<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    banners
 */

/**
 * Hook class.
 */
class Hook_ecommerce_banners
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled)
     */
    public function get_product_category()
    {
        if (!addon_installed('banners')) {
            return null;
        }

        return array(
            'category_name' => do_lang('BANNER_ADS', integer_format(intval(get_option('initial_banner_hits')))),
            'category_description' => do_lang_tempcode('BANNER_DESCRIPTION', escape_html(integer_format(intval(get_option('initial_banner_hits'))))),
            'category_image_url' => find_theme_image('icons/menu/cms/banners'),
        );
    }

    /**
     * Get the products handled by this eCommerce hook.
     *
     * IMPORTANT NOTE TO PROGRAMMERS: This function may depend only on the database, and not on get_member() or any GET/POST values.
     *  Such dependencies will break IPN, which works via a Guest and no dependable environment variables. It would also break manual transactions from the Admin Zone.
     *
     * @param  ?ID_TEXT $search Product being searched for (null: none)
     * @return array A map of product name to list of product details
     */
    public function get_products($search = null)
    {
        if (!addon_installed('banners')) {
            return array();
        }

        require_lang('banners');

        $products = array();

        $products['BANNER_ACTIVATE'] = automatic_discount_calculation(array(
            'item_name' => do_lang('BANNER_ACTIVATE', integer_format(intval(get_option('initial_banner_hits')))),
            'item_description' => do_lang_tempcode('BANNER_ACTIVATE_DESCRIPTION', escape_html(integer_format(intval(get_option('initial_banner_hits'))))),
            'item_image_url' => find_theme_image('icons/admin/add'),

            'type' => PRODUCT_PURCHASE,
            'type_special_details' => array(),

            'price' => (get_option('banner_setup_price') == '') ? null : (floatval(get_option('banner_setup_price'))),
            'currency' => get_option('currency'),
            'price_points' => (get_option('banner_setup_price_points') == '') ? null : (intval(get_option('banner_setup_price_points'))),
            'discount_points__num_points' => null,
            'discount_points__price_reduction' => null,

            'tax_code' => get_option('banner_setup_tax_code'),
            'shipping_cost' => 0.00,
            'product_weight' => null,
            'product_length' => null,
            'product_width' => null,
            'product_height' => null,
            'needs_shipping_address' => false,
        ));

        // It's slightly naughty for us to use get_member(), but it's only for something going into item_description so safe
        $banner_name = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.txn_id', 'details2', array('details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'member_id' => get_member(), 't_type_code' => 'BANNER_ACTIVATE'));
        $sql = 'SELECT SUM(display_likelihood) FROM ' . get_table_prefix() . 'banners WHERE ' . db_string_equal_to('b_type', '');
        if ($banner_name !== null) {
            $sql .= ' AND ' . db_string_not_equal_to('name', $banner_name);
        }
        $total_importance = $GLOBALS['SITE_DB']->query_value_if_there($sql);
        if ($total_importance === null) {
            $total_importance = 0;
        }
        $current_importance = 0;
        $current_hits = 0;
        if ($banner_name !== null) {
            $banner_rows = $GLOBALS['SITE_DB']->query_select('banners', array('*'), array('name' => $banner_name), '', 1);
            if (array_key_exists(0, $banner_rows)) {
                $current_importance = $banner_rows[0]['display_likelihood'];
                $current_hits = $banner_rows[0]['campaign_remaining'];
            }
        }

        $price_points = get_option('banner_hit_price_points');
        foreach (array(10, 20, 50, 100, 1000, 2000, 5000, 10000, 20000, 50000, 100000) as $hits) {
            $products['BANNER_UPGRADE_HITS_' . strval($hits)] = automatic_discount_calculation(array(
                'item_name' => do_lang('BANNER_ADD_HITS', integer_format($hits), integer_format($current_hits)),
                'item_description' => do_lang_tempcode('BANNER_ADD_HITS_DESCRIPTION', escape_html(integer_format($hits)), escape_html(integer_format($current_hits))),
                'item_image_url' => find_theme_image('icons/admin/add_to_category'),

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => (get_option('banner_hit_price') == '') ? null : (floatval(get_option('banner_hit_price')) * $hits),
                'currency' => get_option('currency'),
                'price_points' => empty($price_points) ? null : (intval($price_points) * $hits),
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => tax_multiplier(get_option('banner_hit_tax_code'), $hits),
                'shipping_cost' => 0.00,
                'product_weight' => null,
                'product_length' => null,
                'product_width' => null,
                'product_height' => null,
                'needs_shipping_address' => false,
            ));
        }

        $price_points = get_option('banner_imp_price_points');
        foreach (array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10) as $importance) {
            $percentage = intval(round(100.0 * floatval($current_importance + $importance) / floatval($total_importance)));

            $products['BANNER_UPGRADE_IMPORTANCE_' . strval($importance)] = automatic_discount_calculation(array(
                'item_name' => do_lang('BANNER_ADD_IMPORTANCE', integer_format($importance), strval($percentage)),
                'item_description' => do_lang_tempcode('BANNER_ADD_IMPORTANCE_DESCRIPTION', escape_html(integer_format($importance)), escape_html($percentage)),
                'item_image_url' => find_theme_image('icons/buttons/choose'),

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => (get_option('banner_imp_price') == '') ? null : (floatval(get_option('banner_imp_price')) * $importance),
                'currency' => get_option('currency'),
                'price_points' => empty($price_points) ? null : (intval($price_points) * $importance),
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => tax_multiplier(get_option('banner_imp_tax_code'), $importance),
                'shipping_cost' => 0.00,
                'product_weight' => null,
                'product_length' => null,
                'product_width' => null,
                'product_height' => null,
                'needs_shipping_address' => false,
            ));
        }

        return $products;
    }

    /**
     * Check whether the product codename is available for purchase by the member.
     *
     * @param  ID_TEXT $type_code The product codename
     * @param  MEMBER $member_id The member we are checking against
     * @param  integer $req_quantity The number required
     * @param  boolean $must_be_listed Whether the product must be available for public listing
     * @return integer The availability code (a ECOMMERCE_PRODUCT_* constant)
     */
    public function is_available($type_code, $member_id, $req_quantity = 1, $must_be_listed = false)
    {
        if (get_option('is_on_banner_buy') == '0') {
            return ECOMMERCE_PRODUCT_DISABLED;
        }

        if (is_guest($member_id)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
        }

        require_lang('banners');

        $banner_name = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.txn_id', 'details2', array('details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'member_id' => $member_id, 't_type_code' => 'BANNER_ACTIVATE'));

        if ($banner_name !== null) {
            $test = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'name', array('name' => $banner_name));
            if ($test === null) {
                // Cleanup an inconsistency
                $sales_id = $GLOBALS['SITE_DB']->query_select_value('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.txn_id', 's.id', array('details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'member_id' => $member_id, 't_type_code' => 'BANNER_ACTIVATE'));
                $GLOBALS['SITE_DB']->query_delete('ecom_sales', array('id' => $sales_id), '', 1);

                $banner_name = null;
            }
        }

        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                if ($banner_name !== null) {
                    return ECOMMERCE_PRODUCT_ALREADY_HAS;
                }
                break;

            case 'BANNER_UPGRADE_HITS':
                if ($banner_name === null) {
                    return ECOMMERCE_PRODUCT_PROHIBITED;
                }
                break;

            case 'BANNER_UPGRADE_IMPORTANCE':
                if ($banner_name === null) {
                    return ECOMMERCE_PRODUCT_PROHIBITED;
                }
                break;
        }

        return ECOMMERCE_PRODUCT_AVAILABLE;
    }

    /**
     * Get the message for use in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product in question
     * @return ?Tempcode The message (null: no message)
     */
    public function get_message($type_code)
    {
        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                return do_lang_tempcode('BANNERS_INTRO', escape_html(integer_format(intval(get_option('initial_banner_hits')))));

            case 'BANNER_UPGRADE_HITS':
                return null;

            case 'BANNER_UPGRADE_IMPORTANCE':
                return null;
        }
    }

    /**
     * Get fields that need to be filled in in the purchasing module.
     *
     * @param  ID_TEXT $type_code The product codename
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return ?array A triple: The fields (null: none), The text (null: none), The JavaScript (null: none)
     */
    public function get_needed_fields($type_code, $from_admin = false)
    {
        require_lang('banners');

        require_code('banners');
        require_code('banners2');

        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                list($fields, $js_function_calls) = get_banner_form_fields(true);
                break;

            case 'BANNER_UPGRADE_HITS':
                return array(null, null, null);

            case 'BANNER_UPGRADE_IMPORTANCE':
                return array(null, null, null);
        }

        ecommerce_attach_memo_field_if_needed($fields);

        return array($fields, null, $js_function_calls);
    }

    /**
     * Get the filled in fields and do something with them.
     * May also be called from Admin Zone to get a default purchase ID (i.e. when there's no post context).
     *
     * @param  ID_TEXT $type_code The product codename
     * @param  boolean $from_admin Whether this is being called from the Admin Zone. If so, optionally different fields may be used, including a purchase_id field for direct purchase ID input.
     * @return array A pair: The purchase ID, a confirmation box to show (null for no specific confirmation)
     */
    public function handle_needed_fields($type_code, $from_admin = false)
    {
        require_lang('banners');

        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                $member_id = get_member();

                require_code('uploads');

                $name = post_param_string('name', $from_admin ? '' : false);
                if ($name == '') {
                    return array('', null); // Default is blank
                }

                $urls = get_url('image_url', 'file', 'uploads/banners', 0, CMS_UPLOAD_IMAGE);
                $image_url = $urls[0];
                $site_url = post_param_string('site_url', false, INPUT_FILTER_URL_GENERAL);
                $caption = post_param_string('caption');
                $direct_code = post_param_string('direct_code', '');
                $notes = post_param_string('notes', '');

                $e_details = json_encode(array($member_id, $name, $image_url, $site_url, $caption, $direct_code, $notes));
                $purchase_id = strval($GLOBALS['SITE_DB']->query_insert('ecom_sales_expecting', array('e_details' => $e_details, 'e_time' => time()), true));

                $confirmation_box = show_banner($name, '', comcode_to_tempcode($caption), $direct_code, (url_is_local($image_url) ? (get_custom_base_url() . '/') : '') . $image_url, '', $site_url, '', get_member());
                break;

            case 'BANNER_UPGRADE_HITS':
                return array(strval(get_member()), null);

            case 'BANNER_UPGRADE_IMPORTANCE':
                return array(strval(get_member()), null);
        }

        return array($purchase_id, $confirmation_box);
    }

    /**
     * Handling of a product purchase change state.
     *
     * @param  ID_TEXT $type_code The product codename
     * @param  ID_TEXT $purchase_id The purchase ID
     * @param  array $details Details of the product, with added keys: TXN_ID, STATUS, ORDER_STATUS
     * @return boolean Whether the product was automatically dispatched (if not then hopefully this function sent a staff notification)
     */
    public function actualiser($type_code, $purchase_id, $details)
    {
        if ($details['STATUS'] != 'Completed') {
            return false;
        }

        require_lang('banners');

        require_code('banners');
        require_code('banners2');

        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                $e_details = $GLOBALS['SITE_DB']->query_select_value('ecom_sales_expecting', 'e_details', array('id' => intval($purchase_id)));
                list($member_id, $name, $image_url, $site_url, $caption, $direct_code, $notes) = json_decode($e_details);

                add_banner($name, $image_url, '', $caption, $direct_code, intval(get_option('initial_banner_hits')), $site_url, 3, $notes, BANNER_PERMANENT, null, $member_id, 0);

                $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'details2' => $name, 'txn_id' => $details['TXN_ID']));

                // Send mail to staff
                require_code('submit');
                $edit_url = build_url(array('page' => 'cms_banners', 'type' => '_edit', 'name' => $name), get_module_zone('cms_banners'), array(), false, false, true);
                if (addon_installed('unvalidated')) {
                    send_validation_request('ADD_BANNER', 'banners', true, $name, $edit_url);
                }

                $stats_url = build_url(array('page' => 'banners', 'type' => 'browse'), get_module_zone('banners'));
                $text = do_lang_tempcode('PURCHASED_BANNER');

                $_banner_type_row = $GLOBALS['SITE_DB']->query_select('banner_types', array('t_image_width', 't_image_height'), array('id' => ''), '', 1);
                if (array_key_exists(0, $_banner_type_row)) {
                    $banner_type_row = $_banner_type_row[0];
                } else {
                    $banner_type_row = array('t_image_width' => 728, 't_image_height' => 90);
                }
                $banner_code = do_template('BANNER_SHOW_CODE', array(
                    '_GUID' => 'c96f0ce22de97782b1ab9bee3f43c0ba',
                    'TYPE' => '',
                    'NAME' => $name,
                    'WIDTH' => strval($banner_type_row['t_image_width']),
                    'HEIGHT' => strval($banner_type_row['t_image_height']),
                ));

                // Show a message about banner usage (will only be seen if buying with points)
                $result = do_template('BANNER_ADDED_SCREEN', array('_GUID' => '68725923b19d3df71c72276ada826183', 'TITLE' => '', 'TEXT' => $text, 'BANNER_CODE' => $banner_code, 'STATS_URL' => $stats_url, 'DO_NEXT' => ''));
                global $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE;
                $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE = protect_from_escaping($result); // Note that this won't show for everyone, it depends on the payment method

                break;

            case 'BANNER_UPGRADE_HITS':
                $extrahit = intval(preg_replace('#^BANNER_UPGRADE_HITS_#', '', $type_code));
                $member_id = intval($purchase_id);
                $banner_name = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.txn_id', 'details2', array('details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'member_id' => $member_id, 't_type_code' => 'BANNER_ACTIVATE'));
                $curhit = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'campaign_remaining', array('name' => $banner_name));
                if ($curhit === null) {
                    warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
                }
                $afthit = $curhit + $extrahit;
                $GLOBALS['SITE_DB']->query_update('banners', array('campaign_remaining' => $afthit), array('name' => $banner_name), '', 1);

                $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => $details['item_name'], 'details2' => strval($extrahit), 'txn_id' => $details['TXN_ID']));

                break;

            case 'BANNER_UPGRADE_IMPORTANCE':
                $extraimp = intval(preg_replace('#^BANNER_UPGRADE_IMPORTANCE_#', '', $type_code));
                $member_id = intval($purchase_id);
                $banner_name = $GLOBALS['SITE_DB']->query_select_value_if_there('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.txn_id', 'details2', array('details' => do_lang('BANNER', null, null, null, get_site_default_lang()), 'member_id' => $member_id, 't_type_code' => 'BANNER_ACTIVATE'));
                $curimp = $GLOBALS['SITE_DB']->query_select_value_if_there('banners', 'display_likelihood', array('name' => $banner_name));
                if ($curimp === null) {
                    warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
                }
                $aftimp = $curimp + $extraimp;
                $GLOBALS['SITE_DB']->query_update('banners', array('display_likelihood' => $aftimp), array('name' => $banner_name), '', 1);

                $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => $details['item_name'], 'details2' => strval($extraimp), 'txn_id' => $details['TXN_ID']));

                break;
        }

        return true;
    }

    /**
     * Get the member who made the purchase.
     *
     * @param  ID_TEXT $type_code The product codename
     * @param  ID_TEXT $purchase_id The purchase ID
     * @return ?MEMBER The member ID (null: none)
     */
    public function member_for($type_code, $purchase_id)
    {
        switch (preg_replace('#_\d+$#', '', $type_code)) {
            case 'BANNER_ACTIVATE':
                $e_details = $GLOBALS['SITE_DB']->query_select_value('ecom_sales_expecting', 'e_details', array('id' => intval($purchase_id)));
                list($member_id) = json_decode($e_details);
                return $member_id;

            case 'BANNER_UPGRADE_HITS':
                return intval($purchase_id);

            case 'BANNER_UPGRADE_IMPORTANCE':
                return intval($purchase_id);
        }

        return null;
    }
}
