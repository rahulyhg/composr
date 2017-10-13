<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


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
 * Add a usergroup subscription.
 *
 * @param  SHORT_TEXT $title The title
 * @param  LONG_TEXT $description The description
 * @param  REAL $price The price
 * @param  ID_TEXT $tax_code The tax code
 * @param  integer $length The length
 * @param  SHORT_TEXT $length_units The units for the length
 * @set    y m d w
 * @param  BINARY $auto_recur Auto-recur
 * @param  GROUP $group_id The usergroup that purchasing gains membership to
 * @param  BINARY $uses_primary Whether this is applied to primary usergroup membership
 * @param  BINARY $enabled Whether this is currently enabled
 * @param  ?LONG_TEXT $mail_start The text of the e-mail to send out when a subscription is start (null: default)
 * @param  ?LONG_TEXT $mail_end The text of the e-mail to send out when a subscription is ended (null: default)
 * @param  ?LONG_TEXT $mail_uhoh The text of the e-mail to send out when a subscription cannot be renewed because the subproduct is gone (null: default)
 * @param  ?array $mails Other e-mails to send (null: none)
 * @return AUTO_LINK The ID
 */
function add_usergroup_subscription($title, $description, $price, $tax_code, $length, $length_units, $auto_recur, $group_id, $uses_primary, $enabled, $mail_start, $mail_end, $mail_uhoh, $mails = null)
{
    if (is_null($mails)) {
        $mails = array();
    }

    require_code('global4');
    prevent_double_submit('ADD_USERGROUP_SUBSCRIPTION', null, $title);

    $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;

    $map = array(
        's_price' => $price,
        's_tax_code' => $tax_code,
        's_length' => $length,
        's_length_units' => $length_units,
        's_auto_recur' => $auto_recur,
        's_group_id' => $group_id,
        's_uses_primary' => $uses_primary,
        's_enabled' => $enabled,
    );
    $map += insert_lang('s_title', $title, 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += insert_lang_comcode('s_description', $description, 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += insert_lang('s_mail_start', $mail_start, 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += insert_lang('s_mail_end', $mail_end, 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += insert_lang('s_mail_uhoh', $mail_uhoh, 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $id = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_insert('f_usergroup_subs', $map, true);

    foreach ($mails as $mail) {
        $map = array(
            'm_usergroup_sub_id' => $id,
            'm_ref_point' => $mail['ref_point'],
            'm_ref_point_offset' => $mail['ref_point_offset'],
        );
        $map += insert_lang('m_subject', $mail['subject'], 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
        $map += insert_lang('m_body', $mail['body'], 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
        $GLOBALS['SITE_DB']->query_insert('f_usergroup_sub_mails', $map);
    }

    log_it('ADD_USERGROUP_SUBSCRIPTION', strval($id), $title);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('usergroup_subscription', strval($id), null, null, true);
    }

    $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;

    return $id;
}

/**
 * Edit a usergroup subscription.
 *
 * @param  AUTO_LINK $id The ID
 * @param  SHORT_TEXT $title The title
 * @param  LONG_TEXT $description The description
 * @param  REAL $price The price
 * @param  ID_TEXT $tax_code The tax code
 * @param  integer $length The length
 * @param  SHORT_TEXT $length_units The units for the length
 * @set    y m d w
 * @param  BINARY $auto_recur Auto-recur
 * @param  GROUP $group_id The usergroup that purchasing gains membership to
 * @param  BINARY $uses_primary Whether this is applied to primary usergroup membership
 * @param  BINARY $enabled Whether this is currently enabled
 * @param  ?LONG_TEXT $mail_start The text of the e-mail to send out when a subscription is start (null: default)
 * @param  ?LONG_TEXT $mail_end The text of the e-mail to send out when a subscription is ended (null: default)
 * @param  ?LONG_TEXT $mail_uhoh The text of the e-mail to send out when a subscription cannot be renewed because the subproduct is gone (null: default)
 * @param  ?array $mails Other e-mails to send (null: do not change)
 */
function edit_usergroup_subscription($id, $title, $description, $price, $tax_code, $length, $length_units, $auto_recur, $group_id, $uses_primary, $enabled, $mail_start, $mail_end, $mail_uhoh, $mails = null)
{
    $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;

    $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'usergroup_subscription'));
    }
    $myrow = $rows[0];

    // If usergroup has changed, do a move
    if ($myrow['s_group_id'] != $group_id) {
        require_code('cns_groups_action');
        require_code('cns_groups_action2');
        $type_code = 'USERGROUP' . strval($id);
        $subscriptions = $GLOBALS['SITE_DB']->query_select('ecom_subscriptions', array('*'), array('s_type_code' => $type_code));
        foreach ($subscriptions as $sub) {
            $member_id = $sub['s_member_id'];
            if ((get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
                if ((method_exists($GLOBALS['FORUM_DB'], 'remove_member_from_group')) && (method_exists($GLOBALS['FORUM_DB'], 'add_member_to_group'))) {
                    $GLOBALS['FORUM_DB']->remove_member_from_group($member_id, $group_id);
                    $GLOBALS['FORUM_DB']->add_member_to_group($member_id, $group_id);
                }
            } else {
                $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_delete('f_group_members', array('gm_group_id' => $group_id, 'gm_member_id' => $member_id), '', 1);
                cns_add_member_to_group($member_id, $group_id);
            }
        }
    }

    $_title = $myrow['s_title'];
    $_description = $myrow['s_description'];
    $_mail_start = $myrow['s_mail_start'];
    $_mail_end = $myrow['s_mail_end'];
    $_mail_uhoh = $myrow['s_mail_uhoh'];

    $map = array(
        's_price' => $price,
        's_tax_code' => $tax_code,
        's_length' => $length,
        's_length_units' => $length_units,
        's_auto_recur' => $auto_recur,
        's_group_id' => $group_id,
        's_uses_primary' => $uses_primary,
        's_enabled' => $enabled,
    );
    $map += lang_remap('s_title', $_title, $title, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += lang_remap_comcode('s_description', $_description, $description, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += lang_remap('s_mail_start', $_mail_start, $mail_start, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += lang_remap('s_mail_end', $_mail_end, $mail_end, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $map += lang_remap('s_mail_uhoh', $_mail_uhoh, $mail_uhoh, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_update('f_usergroup_subs', $map, array('id' => $id), '', 1);

    // Handle extra mails. Add/edit/delete as required
    if (!is_null($mails)) {
        $existing_mails = array();
        $_mails = $GLOBALS['FORUM_DB']->query_select('f_usergroup_sub_mails', array('*'), array('m_usergroup_sub_id' => $id), 'ORDER BY id');
        foreach ($_mails as $_mail) {
            $existing_mails[] = array($_mail['id'], $_mail['m_subject'], $_mail['m_body']);
        }
        foreach ($mails as $i => $mail) {
            if (isset($existing_mails[$i])) {
                $map = array(
                    'm_usergroup_sub_id' => $id,
                    'm_ref_point' => $mail['ref_point'],
                    'm_ref_point_offset' => $mail['ref_point_offset'],
                );
                $map += lang_remap('m_subject', $existing_mails[$i][1], $mail['subject'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
                $map += lang_remap('m_body', $existing_mails[$i][2], $mail['body'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
                $GLOBALS['SITE_DB']->query_update('f_usergroup_sub_mails', $map, array('id' => $existing_mails[$i][0]), '', 1);
            } else {
                $map = array(
                    'm_usergroup_sub_id' => $id,
                    'm_ref_point' => $mail['ref_point'],
                    'm_ref_point_offset' => $mail['ref_point_offset'],
                );
                $map += insert_lang('m_subject', $mail['subject'], 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
                $map += insert_lang('m_body', $mail['body'], 2, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
                $GLOBALS['SITE_DB']->query_insert('f_usergroup_sub_mails', $map);
            }
        }
        for ($i = count($mails); $i < count($existing_mails); $i++) {
            $GLOBALS['SITE_DB']->query_delete('f_usergroup_sub_mails', array('id' => $existing_mails[$i][0]), '', 1);
            delete_lang($existing_mails[$i][1], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
            delete_lang($existing_mails[$i][2], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
        }
    }

    log_it('EDIT_USERGROUP_SUBSCRIPTION', strval($id), $title);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        generate_resource_fs_moniker('usergroup_subscription', strval($id));
    }

    $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;
}

/**
 * Delete a usergroup subscription.
 *
 * @param  AUTO_LINK $id The ID
 * @param  LONG_TEXT $uhoh_mail The cancellation mail to send out (blank: none)
 */
function delete_usergroup_subscription($id, $uhoh_mail = '')
{
    $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;

    $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $id), '', 1);
    if (!array_key_exists(0, $rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'usergroup_subscription'));
    }
    $myrow = $rows[0];
    $new_group = $myrow['s_group_id'];

    // Remove benefits
    $type_code = 'USERGROUP' . strval($id);
    $subscriptions = $GLOBALS['SITE_DB']->query_select('ecom_subscriptions', array('*'), array('s_type_code' => $type_code));
    $to_members = array();
    foreach ($subscriptions as $sub) {
        $member_id = $sub['s_member_id'];

        $test = in_array($new_group, $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
        if ($test) {
            if (is_null($GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select_value_if_there('f_group_member_timeouts', 'member_id', array('member_id' => $member_id, 'group_id' => $new_group)))) {
                // Remove them from the group

                if ((method_exists($GLOBALS['FORUM_DB'], 'remove_member_from_group')) && (get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
                    $GLOBALS['FORUM_DB']->remove_member_from_group($member_id, $new_group);
                } else {
                    $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_delete('f_group_members', array('gm_group_id' => $new_group, 'gm_member_id' => $member_id), '', 1);
                }
                $to_members[] = $member_id;
            }
        }
    }
    if ($uhoh_mail != '') {
        require_code('notifications');
        dispatch_notification('paid_subscription_messages', null, do_lang('PAID_SUBSCRIPTION_ENDED', null, null, null, get_site_default_lang()), $uhoh_mail, $to_members);
    }

    $_title = $myrow['s_title'];
    $_description = $myrow['s_description'];
    $title = get_translated_text($_title, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    $_mail_start = $myrow['s_mail_start'];
    $_mail_end = $myrow['s_mail_end'];
    $_mail_uhoh = $myrow['s_mail_uhoh'];

    $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_delete('f_usergroup_subs', array('id' => $id), '', 1);
    delete_lang($_title, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    delete_lang($_description, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    delete_lang($_mail_start, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    delete_lang($_mail_end, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    delete_lang($_mail_uhoh, $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);

    $_mails = $GLOBALS['FORUM_DB']->query_select('f_usergroup_sub_mails', array('*'), array('m_usergroup_sub_id' => $id));
    foreach ($_mails as $_mail) {
        delete_lang($_mail['m_subject'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
        delete_lang($_mail['m_body'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);
    }

    log_it('DELETE_USERGROUP_SUBSCRIPTION', strval($id), $title);

    if ((addon_installed('commandr')) && (!running_script('install'))) {
        require_code('resource_fs');
        expunge_resource_fs_moniker('usergroup_subscription', strval($id));
    }

    $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;
}
