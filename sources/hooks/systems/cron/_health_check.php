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
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_cron__health_check
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
        return array(
            'label' => 'Health Check',
            'num_queued' => null,
            'minutes_between_runs' => intval(get_option('hc_cron_regularity')),
        );
    }

    /**
     * Run function for system scheduler scripts. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run($last_run)
    {
        // Note that we have a leading "_" on the hook name so that it runs first (we run the system scheduler scripts in sorted order)

        require_code('health_check');

        $cron_notify_regardless = get_option('hc_cron_notify_regardless');

        $sections_to_run = (get_option('hc_cron_sections_to_run') == '') ? array() : explode(',', get_option('hc_cron_sections_to_run'));
        $passes = ($cron_notify_regardless == '1');
        $skips = ($cron_notify_regardless == '1');
        $manual_checks = false;

        $has_fails = false;
        $categories = run_health_check($has_fails, $sections_to_run, $passes, $skips, $manual_checks, false, null);

        if ((count($categories) > 0) || ($cron_notify_regardless == '1')) {
            $results = do_template('HEALTH_CHECK_RESULTS', array('_GUID' => 'b7bbb671bacc1a5eee03a71c3f1a1eac', 'CATEGORIES' => $categories));

            require_code('notifications');
            $subject = do_lang('HEALTH_CHECK_SUBJECT_' . ($has_fails ? 'fail' : 'misc'));
            $message = do_lang('HEALTH_CHECK_BODY', $results->evaluate());
            dispatch_notification('health_check', $has_fails ? '1' : '0', $subject, $message, null, A_FROM_SYSTEM_PRIVILEGED, array('priority' => $has_fails ? 1 : 4));
        }
    }
}
