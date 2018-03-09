<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    ecommerce
 */

/**
 * Hook class.
 */
class Hook_cron_subscription_mails
{
    /**
     * Get info from this hook.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     * @param  boolean $calculate_num_queued Calculate the number of items queued, if possible
     * @return ?array Return a map of info about the hook (null: disabled)
     */
    public function info($last_run, $calculate_num_queued)
    {
        if (!addon_installed('ecommerce')) {
            return null;
        }

        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'label' => 'Send subscription e-mails',
            'num_queued' => null, // Too time-consuming to calculate
            'minutes_between_runs' => 30,
        );
    }

    /**
     * Run function for system scheduler scripts. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run($last_run)
    {
        require_code('ecommerce_subscriptions');
        $_subscribers_1 = collapse_1d_complexity('s_member_id', $GLOBALS['SITE_DB']->query_select('ecom_subscriptions', array('DISTINCT s_member_id')));
        $_subscribers_2 = collapse_1d_complexity('member_id', $GLOBALS['FORUM_DB']->query_select('f_group_member_timeouts', array('DISTINCT member_id')));
        $_subscribers = array_merge($_subscribers_1, $_subscribers_2);
        $subscribers = array();
        foreach ($_subscribers as $subscriber) {
            $subscribers[$subscriber] = find_member_subscriptions($subscriber, true);
        }

        $mails = $GLOBALS['FORUM_DB']->query_select('f_usergroup_sub_mails m JOIN ' . get_table_prefix() . 'f_usergroup_subs s ON s.id=m.m_usergroup_sub_id', array('m.*'));
        foreach ($mails as $mail) {
            $offset = $mail['m_ref_point_offset'] * 60 * 60; // Convert from hours to seconds
            foreach ($subscribers as $subscriber => $subs) {
                if (isset($subs['USERGROUP' . strval($mail['m_usergroup_sub_id'])])) {
                    $send = false;

                    $sub = $subs['USERGROUP' . strval($mail['m_usergroup_sub_id'])];
                    switch ($mail['m_ref_point']) {
                        case 'start':
                            $send = ((time() - $sub['start_time'] >= $offset) && ($last_run - $sub['start_time'] < $offset));
                            break;
                        case 'term_start':
                            $send = ((time() - $sub['term_start_time'] >= $offset) && ($last_run - $sub['term_start_time'] < $offset));
                            break;
                        case 'term_end':
                            $send = (($sub['term_end_time'] - time() <= $offset) && ($sub['term_end_time'] - $last_run > $offset));
                            break;
                        case 'expiry':
                            if ($sub['expiry_time'] !== null) {
                                $send = (($sub['expiry_time'] - time() <= $offset) && ($sub['expiry_time'] - $last_run > $offset));
                            }
                            break;
                    }

                    // Send notification
                    if ($send) {
                        require_code('notifications');
                        dispatch_notification('paid_subscription_messages', null, get_translated_text($mail['m_subject']), get_translated_text($mail['m_body']), array($subscriber));
                    }
                }
            }
        }
    }
}
