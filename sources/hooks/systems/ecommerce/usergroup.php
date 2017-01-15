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
 * Handling of a usergroup subscription.
 *
 * @param  ID_TEXT $purchase_id The purchase ID.
 * @param  array $details Details of the product.
 * @param  ID_TEXT $type_code The product codename.
 * @param  ID_TEXT $payment_status The status this transaction is telling of
 * @set    Pending Completed SModified SCancelled
 * @param  SHORT_TEXT $txn_id The transaction ID
 */
function handle_usergroup_subscription($purchase_id, $details, $type_code, $payment_status, $txn_id)
{
    require_code('cns_groups_action');
    require_code('cns_groups_action2');
    require_code('cns_members');
    require_code('notifications');

    $usergroup_subscription_id = intval(substr($type_code, 9));
    $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
    $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id), '', 1);
    $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;
    if (array_key_exists(0, $rows)) {
        $myrow = $rows[0];
        $new_group = $myrow['s_group_id'];
        $object = find_product($type_code);
    } else {
        return; // The usergroup subscription has been deleted, and this was to remove the payment for it
    }

    if ($myrow['s_auto_recur'] == 1) {
        $member_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_subscriptions', 's_member_id', array('id' => intval($purchase_id)));
        if ($member_id === null) {
            return;
        }
    } else {
        $member_id = intval($purchase_id);
    }

    if ($payment_status == 'SCancelled') { // Cancelled
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

                dispatch_notification('paid_subscription_messages', null/*Not currently per-sub settable strval($usergroup_subscription_id)*/, do_lang('PAID_SUBSCRIPTION_ENDED', null, null, null, get_lang($member_id)), get_translated_text($myrow['s_mail_end'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'], get_lang($member_id)), array($member_id), A_FROM_SYSTEM_PRIVILEGED);
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

        dispatch_notification('paid_subscription_messages', null/*Not currently per-sub settable strval($usergroup_subscription_id)*/, do_lang('PAID_SUBSCRIPTION_STARTED'), get_translated_text($myrow['s_mail_start'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'], get_lang($member_id)), array($member_id), A_FROM_SYSTEM_PRIVILEGED);
    }
}

/**
 * Get the member who made the purchase.
 *
 * @param  ID_TEXT $purchase_id The purchase ID.
 * @param  array $details Details of the product.
 * @param  ID_TEXT $type_code The product codename.
 * @return ?MEMBER The member ID (null: none).
 */
function member_for_usergroup_subscription($purchase_id, $details, $type_code)
{
    $usergroup_subscription_id = intval(substr($type_code, 9));
    $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
    $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $usergroup_subscription_id), '', 1);
    $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;
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

/**
 * eCommerce product hook.
 */
class Hook_ecommerce_usergroup
{
    /**
     * Find whether a shipping address is needed.
     *
     * @return boolean Whether a shipping address is needed.
     */
    public function needs_shipping_address()
    {
        return false;
    }

    /**
     * Function for administrators to pick an identifier (only used by admins, usually the identifier would be picked via some other means in the wider Composr codebase).
     *
     * @param  ID_TEXT $type_code Product codename.
     * @return ?Tempcode Input field in standard Tempcode format for fields (null: no identifier).
     */
    public function get_identifier_manual_field_inputter($type_code)
    {
        $list = new Tempcode();
        $rows = $GLOBALS['SITE_DB']->query_select('ecom_subscriptions', array('*'), array('s_type_code' => $type_code, 's_state' => 'new'), 'ORDER BY id DESC');
        foreach ($rows as $row) {
            $username = $GLOBALS['FORUM_DRIVER']->get_username($row['s_member_id']);
            if ($username === null) {
                $username = do_lang('UNKNOWN');
            }
            $list->attach(form_input_list_entry(strval($row['id']), false, do_lang('SUBSCRIPTION_OF', strval($row['id']), $username, get_timezoned_date($row['s_time']))));
        }

        $fields = alternate_fields_set__start('options');

        $fields_inner = new Tempcode();

        if (!$list->is_empty()) {
            $fields_inner->attach(form_input_list(do_lang_tempcode('FINISH_STARTED_ALREADY'), do_lang_tempcode('DESCRIPTION_FINISH_STARTED_ALREADY'), 'purchase_id', $list, null, false, true));
        }

        $pretty_name = do_lang_tempcode('NEW_UGROUP_SUB_FOR');
        $description = do_lang_tempcode('DESCRIPTION_NEW_UGROUP_SUB_FOR');
        $fields_inner->attach(form_input_username($pretty_name, $description, 'username', '', true, true));

        $fields->attach(alternate_fields_set__end('options', do_lang_tempcode('SUBSCRIPTION'), '', $fields_inner, true));

        return $fields;
    }

    /**
     * Find the corresponding member to a given purchase ID.
     *
     * @param  ID_TEXT $purchase_id The purchase ID.
     * @return ?MEMBER The member (null: unknown / can't perform operation).
     */
    public function member_for($purchase_id)
    {
        return $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_subscriptions', 's_member_id', array('id' => intval($purchase_id)));
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  boolean $site_lang Whether to make sure the language for item_name is the site default language (crucial for when we read/go to third-party sales systems and use the item_name as a key).
     * @return array A map of product name to list of product details.
     */
    public function get_products($site_lang = false)
    {
        if ((get_forum_type() != 'cns') && (get_value('unofficial_ecommerce') !== '1')) {
            return array();
        }

        $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
        $GLOBALS['NO_DB_SCOPE_CHECK'] = true;

        $usergroup_subs = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('s_enabled' => 1));
        $products = array();
        foreach ($usergroup_subs as $sub) {
            $item_name = get_translated_text($sub['s_title'], $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'], $site_lang ? get_site_default_lang() : null);

            $products['USERGROUP' . strval($sub['id'])] = array(
                'item_name' => $item_name,

                'type' => ($sub['s_auto_recur'] == 1) ? PRODUCT_SUBSCRIPTION : PRODUCT_PURCHASE, // Technically a non-recurring usergroup subscription is NOT a subscription (i.e. conflicting semantics here...)
                'type_special_details' => array('length' => $sub['s_length'], 'length_units' => $sub['s_length_units']),

                'price' => $sub['s_cost'],
                'currency' => get_option('currency'),
                'price_points' => null,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'actualiser' => 'handle_usergroup_subscription',
                'member_finder' => 'member_for_usergroup_subscription',
            );
        }

        $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;

        return $products;
    }

    /**
     * Get the message for use in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product in question.
     * @return Tempcode The message.
     */
    public function get_message($type_code)
    {
        $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
        $GLOBALS['NO_DB_SCOPE_CHECK'] = true;

        $id = intval(substr($type_code, 9));

        $db = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB'];
        $sub = $db->query_select('f_usergroup_subs', array('*'), array('id' => $id), '', 1);
        if (!array_key_exists(0, $sub)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', do_lang_tempcode('CUSTOM_PRODUCT_USERGROUP')));
        }

        $ret = get_translated_tempcode('f_usergroup_subs', $sub[0], 's_description', $db);

        $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;

        return $ret;
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @return ?array The fields and message text (null: none).
     */
    public function get_needed_fields()
    {
        return null;
    }

    /**
     * Check whether the product codename is available for purchase by the member.
     *
     * @param  ID_TEXT $type_code The product codename.
     * @param  MEMBER $member The member.
     * @return integer The availability code (a ECOMMERCE_PRODUCT_* constant).
     */
    public function is_available($type_code, $member)
    {
        if (is_guest($member)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
        }
        if ($GLOBALS['FORUM_DRIVER']->is_super_admin($member)) {
            return ECOMMERCE_PRODUCT_AVAILABLE;
        }

        $id = intval(substr($type_code, 9));
        $dbs_bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
        $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
        $rows = $GLOBALS[(get_forum_type() == 'cns') ? 'FORUM_DB' : 'SITE_DB']->query_select('f_usergroup_subs', array('*'), array('id' => $id));
        $GLOBALS['NO_DB_SCOPE_CHECK'] = $dbs_bak;
        if (!isset($rows[0])) {
            return ECOMMERCE_PRODUCT_MISSING;
        }
        $sub = $rows[0];
        $group_id = $sub['s_group_id'];

        $groups = $GLOBALS['FORUM_DRIVER']->get_members_groups($member);

        if ($sub['s_auto_recur'] == 1) { // Non-auto-recur can be topped up at will
            if (in_array($group_id, $groups)) {
                return ECOMMERCE_PRODUCT_ALREADY_HAS;
            }
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }
}
