<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

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
 * eCommerce product hook.
 */
class Hook_ecommerce_usergroup
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled).
     */
    public function get_product_category()
    {
        return array(
            'category_name' => do_lang('USERGROUP_SUBSCRIPTION'),
            'category_description' => do_lang_tempcode('USERGROUP_SUBSCRIPTION_DESCRIPTION'),
            'category_image_url' => find_theme_image('icons/48x48/menu/adminzone/audit/ecommerce/subscriptions'),
        );
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  ?ID_TEXT $search Product being searched for (null: none).
     * @return array A map of product name to list of product details.
     */
    public function get_products($search = null)
    {
        if ((get_forum_type() != 'cns') && (get_value('unofficial_ecommerce') !== '1')) {
            return array();
        }

        push_db_scope_check(false);

        $images = array('bronze', 'silver', 'gold', 'platinum');

        $db = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'];
        $usergroup_subs = $db->query_select('f_usergroup_subs', array('*'), array('s_enabled' => 1), 'ORDER BY s_length_units, s_price');
        $products = array();
        foreach ($usergroup_subs as $i => $sub) {
            $item_name = get_translated_text($sub['s_title'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']);

            $image_url = '';
            if (get_forum_type() == 'cns') {
                $image_url = $db->query_select_value_if_there('f_groups', 'g_rank_image', array('id' => $sub['id']));
                if ($image_url === null) {
                    continue; // Missing
                }
                if ($image_url != '') {
                    $image_url = find_theme_image($image_url);
                }
            }

            if ($image_url == '') {
                $image_url = find_theme_image('icons/48x48/tiers/' . $images[$i % 4]);
            }

            $products['USERGROUP' . strval($sub['id'])] = array(
                'item_name' => do_lang('_SUBSCRIPTION', $item_name),
                'item_description' => get_translated_tempcode('f_usergroup_subs', $sub, 's_description', $db),
                'item_image_url' => $image_url,

                'type' => ($sub['s_auto_recur'] == 1) ? PRODUCT_SUBSCRIPTION : PRODUCT_PURCHASE, // Technically a non-recurring usergroup subscription is NOT a subscription (i.e. conflicting semantics here...)
                'type_special_details' => array('length' => $sub['s_length'], 'length_units' => $sub['s_length_units']),

                'price' => $sub['s_price'],
                'currency' => get_option('currency'),
                'price_points' => null,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => $sub['s_tax_code'],
                'shipping_cost' => 0.00,
                'product_weight' => null,
                'product_length' => null,
                'product_width' => null,
                'product_height' => null,
                'needs_shipping_address' => false,
            );
        }

        pop_db_scope_check();

        return $products;
    }

    /**
     * Check whether the product codename is available for purchase by the member.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  MEMBER $member_id The member we are checking against.
     * @param  integer $req_quantity The number required.
     * @param  boolean $must_be_listed Whether the product must be available for public listing.
     * @return integer The availability code (a ECOMMERCE_PRODUCT_* constant).
     */
    public function is_available($type_code, $member_id, $req_quantity = 1, $must_be_listed = false)
    {
        if (is_guest($member_id)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
        }
        if ($GLOBALS['FORUM_DRIVER']->is_super_admin($member_id)) {
            return ECOMMERCE_PRODUCT_AVAILABLE;
        }

        $usergroup_subscription_id = intval(substr($type_code, 9));
        push_db_scope_check(false);
        $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id));
        pop_db_scope_check();
        if (!isset($rows[0])) {
            return ECOMMERCE_PRODUCT_MISSING;
        }
        $sub = $rows[0];
        $group_id = $sub['s_group_id'];

        $groups = $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id);

        if ($sub['s_auto_recur'] == 1) { // Non-auto-recur can be topped up at will
            if (in_array($group_id, $groups)) {
                return ECOMMERCE_PRODUCT_ALREADY_HAS;
            }
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }

    /**
     * Get the message for use in the purchasing module
     *
     * @param  ID_TEXT $type_code The product in question.
     * @return ?Tempcode The message (null: no message).
     */
    public function get_message($type_code)
    {
        push_db_scope_check(false);

        $usergroup_subscription_id = intval(preg_replace('#^USERGROUP#', '', $type_code));

        $db = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'];
        $sub = $db->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id), '', 1);
        if (!array_key_exists(0, $sub)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', do_lang_tempcode('CUSTOM_PRODUCT_USERGROUP')));
        }

        $ret = get_translated_tempcode('f_usergroup_subs', $sub[0], 's_description', $db);

        pop_db_scope_check();

        return $ret;
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return ?array A triple: The fields (null: none), The text (null: none), The JavaScript (null: none).
     */
    public function get_needed_fields($type_code, $from_admin = false)
    {
        $fields = mixed();

        if ($from_admin) {
            $fields = new Tempcode();

            $list = new Tempcode();
            $rows = $GLOBALS['SITE_DB']->query_select('ecom_subscriptions', array('*'), array('s_type_code' => $type_code, 's_state' => 'new'), 'ORDER BY id DESC');
            foreach ($rows as $row) {
                $username = $GLOBALS['FORUM_DRIVER']->get_username($row['s_member_id']);
                if ($username === null) {
                    $username = do_lang('UNKNOWN');
                }
                $list->attach(form_input_list_entry(strval($row['id']), false, do_lang('SUBSCRIPTION_OF', strval($row['id']), $username, get_timezoned_date_time($row['s_time']))));
            }

            $fields = alternate_fields_set__start('options');

            $fields_inner = new Tempcode();

            if (!$list->is_empty()) {
                $fields_inner->attach(form_input_list(do_lang_tempcode('FINISH_STARTED_ALREADY'), do_lang_tempcode('DESCRIPTION_FINISH_STARTED_ALREADY'), 'purchase_id', $list, null, false, true));
            }

            $pretty_name = do_lang_tempcode('NEW_UGROUP_SUB_FOR');
            $description = do_lang_tempcode('DESCRIPTION_NEW_UGROUP_SUB_FOR');
            $fields_inner->attach(form_input_username($pretty_name, $description, 'username', '', true, true)); // This is handled as a special case in admin_ecommerce_logs.php

            $fields->attach(alternate_fields_set__end('options', do_lang_tempcode('SUBSCRIPTION'), '', $fields_inner, true));
        }

        ecommerce_attach_memo_field_if_needed($fields);

        return array(null, null, null);
    }

    /**
     * Get the filled in fields and do something with them.
     * May also be called from Admin Zone to get a default purchase ID (i.e. when there's no post context).
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return array A pair: The purchase ID, a confirmation box to show (null for no specific confirmation).
     */
    public function handle_needed_fields($type_code, $from_admin = false)
    {
        if (($from_admin) && (post_param_string('purchase_id', null) !== null)) {
            return array(post_param_string('purchase_id'), null);
        }

        return array('', null);
    }

    /**
     * Handling of a product purchase change state.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @param  array $details Details of the product, with added keys: TXN_ID, STATUS, ORDER_STATUS.
     * @return boolean Whether the product was automatically dispatched (if not then hopefully this function sent a staff notification).
     */
    public function actualiser($type_code, $purchase_id, $details)
    {
        require_code('cns_groups_action');
        require_code('cns_groups_action2');
        require_code('cns_members');
        require_code('notifications');

        $usergroup_subscription_id = intval(preg_replace('#^USERGROUP#', '', $type_code));

        push_db_scope_check(false);
        $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id), '', 1);
        pop_db_scope_check();
        if (!array_key_exists(0, $rows)) {
            return false; // The usergroup subscription has been deleted, and this was to remove the payment for it
        }

        $item_name = $details['item_name'];

        $myrow = $rows[0];
        $new_group = $myrow['s_group_id'];

        if ($myrow['s_auto_recur'] == 1) {
            $member_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_subscriptions', 's_member_id', array('id' => intval($purchase_id)));
            if ($member_id === null) {
                return false;
            }
        } else {
            $member_id = intval($purchase_id);
        }

        $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
        if ($username === null) {
            $username = do_lang('GUEST');
        }

        if ($details['STATUS'] == 'SCancelled') { // Cancelled
            $test = in_array($new_group, $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
            if ($test) {
                // Remove them from the group

                if ($GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select_value_if_there('f_group_member_timeouts', 'member_id', array('member_id' => $member_id, 'group_id' => $new_group)) === null) {
                    if ((method_exists($GLOBALS['FORUM_DRIVER'], 'remove_member_from_group')) && (get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
                        $GLOBALS['FORUM_DRIVER']->remove_member_from_group($member_id, $new_group);
                    } else {
                        if ($myrow['s_uses_primary'] == 1) {
                            $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_update('f_members', array('m_primary_group' => get_first_default_group()), array('id' => $member_id), '', 1);

                            $GLOBALS['FORUM_DB']->query_insert('f_group_join_log', array(
                                'member_id' => $member_id,
                                'usergroup_id' => get_first_default_group(),
                                'join_time' => time()
                            ));
                        } else {
                            $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_delete('f_group_members', array('gm_group_id' => $new_group, 'gm_member_id' => $member_id));// ,'',1
                        }
                    }

                    // Notification to user
                    $subject = do_lang('PAID_SUBSCRIPTION_ENDED', null, null, null, get_lang($member_id));
                    $body = get_translated_text($myrow['s_mail_end'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'], get_lang($member_id));
                    dispatch_notification('paid_subscription_messages', null/*Not currently per-sub settable strval($usergroup_subscription_id)*/, $subject, $body, array($member_id), A_FROM_SYSTEM_PRIVILEGED);

                    // Notification to staff
                    $subject = do_lang('SERVICE_CANCELLED', $item_name, $username, get_site_name(), get_site_default_lang());
                    $body = do_notification_lang('_SERVICE_CANCELLED', $item_name, $username, get_site_name(), get_site_default_lang());
                    dispatch_notification('service_cancelled_staff', null, $subject, $body);
                }
            }
        } else { // Completed
            $test = in_array($new_group, $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
            if (!$test) {
                // Add them to the group

                if ((method_exists($GLOBALS['FORUM_DRIVER'], 'add_member_to_group')) && (get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
                    $GLOBALS['FORUM_DRIVER']->add_member_to_group($member_id, $new_group);
                } else {
                    if ($myrow['s_uses_primary'] == 1) {
                        $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_update('f_members', array('m_primary_group' => $new_group), array('id' => $member_id), '', 1);

                        $GLOBALS['FORUM_DB']->query_insert('f_group_join_log', array(
                            'member_id' => $member_id,
                            'usergroup_id' => $new_group,
                            'join_time' => time()
                        ));
                    } else {
                        cns_add_member_to_group($member_id, $new_group);
                    }
                }

                $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_delete('f_group_member_timeouts', array('member_id' => $member_id, 'group_id' => $new_group));
            }

            if ($myrow['s_auto_recur'] == 0) { // Purchasing module, so need to maintain group-member-timeout
                $start_time = $GLOBALS['SITE_DB']->query_select_value_if_there('f_group_member_timeouts', 'MAX(timeout)', array(
                    'member_id' => $member_id,
                    'group_id' => $new_group,
                ));
                if (($start_time === null) || ($start_time < time())) {
                    $start_time = time();
                }
                $GLOBALS['SITE_DB']->query_delete('f_group_member_timeouts', array(
                    'member_id' => $member_id,
                    'group_id' => $new_group,
                ));

                $time_period_units = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day');
                $term_end_time = strtotime('+' . strval($myrow['s_length']) . ' ' . $time_period_units[$myrow['s_length_units']], $start_time);

                $GLOBALS['SITE_DB']->query_insert('f_group_member_timeouts', array(
                    'member_id' => $member_id,
                    'group_id' => $new_group,
                    'timeout' => $term_end_time,
                ));
            }

            // Notification to user
            $subject = do_lang('PAID_SUBSCRIPTION_STARTED', null, null, null, get_lang($member_id));
            $body = get_translated_text($myrow['s_mail_start'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'], get_lang($member_id));
            dispatch_notification('paid_subscription_messages', null/*Not currently per-sub settable strval($usergroup_subscription_id)*/, $subject, $body, array($member_id), A_FROM_SYSTEM_PRIVILEGED);

            // Notification to staff
            $subject = do_lang('SERVICE_PAID_FOR', $item_name, $username, get_site_name(), get_site_default_lang());
            $body = do_notification_lang('_SERVICE_PAID_FOR', $item_name, $username, get_site_name(), get_site_default_lang());
            dispatch_notification('service_paid_for_staff', null, $subject, $body);

            $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => $details['item_name'], 'details2' => strval($usergroup_subscription_id), 'txn_id' => $details['TXN_ID']));
        }

        return true;
    }

    /**
     * Get the member who made the purchase.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return ?MEMBER The member ID (null: none).
     */
    public function member_for($type_code, $purchase_id)
    {
        $usergroup_subscription_id = intval(substr($type_code, 9));
        push_db_scope_check(false);
        $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id), '', 1);
        pop_db_scope_check();
        if (array_key_exists(0, $rows)) {
            $myrow = $rows[0];
        } else {
            return null;
        }

        if ($myrow['s_auto_recur'] == 1) {
            $member_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_subscriptions', 's_member_id', array('id' => intval($purchase_id)));
        } else {
            $member_id = intval($purchase_id);
        }
        return $member_id;
    }
}
