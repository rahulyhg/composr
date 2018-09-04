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
 * @package    shopping
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */

/**
 * Find products in cart.
 *
 * @param  ID_TEXT $catalogue_name Catalogue name
 * @return array Shopping catalogue fields
 */
function find_shopping_catalogue_fields($catalogue_name = 'products')
{
    static $fields_maps = array();
    if (isset($fields_maps[$catalogue_name])) {
        return $fields_maps[$catalogue_name];
    }

    $fields_maps[$catalogue_name] = array( // Defaults
        'product_title' => null,
        'sku' => null,
        'price' => null,
        'stock_level' => null,
        'stock_level_warn_at' => null,
        'stock_level_maintain' => null,
        'tax_code' => null,
        'image' => null,
        'weight' => null,
        'length' => null,
        'width' => null,
        'height' => null,
        'description' => null,
    );

    $fields_map = &$fields_maps[$catalogue_name];

    require_code('fields');

    // By tagging
    $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('id', 'cf_type', 'cf_options'), array('c_name' => $catalogue_name), 'ORDER BY cf_order');
    foreach ($fields as $i => $field) {
        $options = parse_field_options($field['cf_options']);

        foreach (array_keys($fields_map) as $key) {
            if ((isset($options['ecommerce_tag'])) && ($options['ecommerce_tag'] == $key)) {
                $fields_map[$key] = $i;
            }
        }
    }

    require_lang('catalogues');

    // Fallback: By field type
    foreach (array_keys($fields_map) as $j => $key) {
        if ($fields_map[$key] === null) {
            foreach ($fields as $i => $field) {
                switch ($key) {
                    case 'product_title':
                        if (($field['cf_type'] == 'short_trans') || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'sku':
                        if (($field['cf_type'] == 'codename') || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'price':
                        if ((($field['cf_type'] == 'float') && (strpos($field['cf_options'], 'price') !== false)) || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'stock_level':
                    case 'stock_level_warn_at':
                    case 'stock_level_maintain':
                    case 'weight':
                    case 'length':
                    case 'width':
                    case 'height':
                        if (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key)) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'tax_code':
                        if (($field['cf_type'] == 'tax_code') || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'image':
                        if (($field['cf_type'] == 'picture') || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;

                    case 'description':
                        if ((in_array($field['cf_type'], array('long_trans', 'long_text', 'posting_field'))) || (get_translated_text($field['cf_name']) == do_lang('ECOM_CAT_' . $key))) {
                            $fields_map[$key] = $i;
                            continue 2;
                        }
                        break;
                }
            }
        }
    }

    // Fallback: Absolute defaults
    foreach (array_keys($fields_map) as $j => $key) {
        if ($fields_map[$key] === null) {
            $fields_map[$key] = $j;
        }
    }

    return $fields_map;
}

/*
FOR CART MANAGEMENT
*/

/**
 * Find products in cart.
 *
 * @return array Product details in cart
 */
function find_products_in_cart()
{
    $where = array();
    if (is_guest()) {
        $where['session_id'] = get_session_id();
    } else {
        $where['ordered_by'] = get_member();
    }
    return $GLOBALS['SITE_DB']->query_select('shopping_cart', array('*'), $where, 'ORDER BY id');
}

/**
 * Add new item to the cart.
 *
 * @param  ID_TEXT $type_code Product codename
 * @param  ID_TEXT $purchase_id Purchase ID
 * @param  integer $quantity Quantity
 */
function add_to_cart($type_code, $purchase_id = '', $quantity = 1)
{
    list($details, $product_object) = find_product_details($type_code);

    if ($product_object->is_available($type_code, get_member(), 1) != ECOMMERCE_PRODUCT_AVAILABLE) {
        require_lang('shopping');
        warn_exit(do_lang_tempcode('PRODUCT_UNAVAILABLE_WARNING', escape_html($details['item_name'])));
    }

    $where = array('type_code' => $type_code);
    if (is_guest()) {
        $where['session_id'] = get_session_id();
    } else {
        $where['ordered_by'] = get_member();
    }
    $existing_rows = $GLOBALS['SITE_DB']->query_select('shopping_cart', array('id', 'quantity'), $where, '', 1);

    if (!array_key_exists(0, $existing_rows)) {
        $cart_map = array(
            'session_id' => get_session_id(),
            'ordered_by' => get_member(),
            'type_code' => $type_code,
            'purchase_id' => $purchase_id,
            'quantity' => $quantity,
        );
        $id = $GLOBALS['SITE_DB']->query_insert('shopping_cart', $cart_map, true);
    } else {
        $GLOBALS['SITE_DB']->query_update('shopping_cart', array('quantity' => ($existing_rows[0]['quantity'] + $quantity)), $where, '', 1);
    }
}

/**
 * Update cart quantities etc.
 *
 * @param  array $products_in_cart List of product specifiers
 */
function update_cart($products_in_cart)
{
    foreach ($products_in_cart as $_product) {
        list($type_code, $quantity) = $_product;

        $where = array('type_code' => $type_code);
        if (is_guest()) {
            $where['session_id'] = get_session_id();
        } else {
            $where['ordered_by'] = get_member();
        }

        if ($quantity > 0) {
            $GLOBALS['SITE_DB']->query_update('shopping_cart', array('quantity' => $quantity), $where, '', 1);
        } else {
            $GLOBALS['SITE_DB']->query_delete('shopping_cart', $where, '', 1);
        }
    }
}

/**
 * Remove particular items from the cart.
 *
 * @param  array $products_to_remove Products to remove
 */
function remove_from_cart($products_to_remove)
{
    foreach ($products_to_remove as $type_code) {
        $where = array('type_code' => $type_code);
        if (is_guest()) {
            $where['session_id'] = get_session_id();
        } else {
            $where['ordered_by'] = get_member();
        }

        $GLOBALS['SITE_DB']->query_delete('shopping_cart', $where);
    }
}

/**
 * Delete cart contents for the current user.
 */
function empty_cart()
{
    $where = array();
    if (is_guest()) {
        $where['session_id'] = get_session_id();
    } else {
        $where['ordered_by'] = get_member();
    }

    $GLOBALS['SITE_DB']->query_delete('shopping_cart', $where);
}

/**
 * Log cart actions.
 *
 * @param  ID_TEXT $action The data
 */
function log_cart_actions($action)
{
    $GLOBALS['SITE_DB']->query_insert('shopping_logging', array(
        'l_member_id' => get_member(),
        'l_session_id' => get_session_id(),
        'l_ip' => get_ip_address(),
        'l_last_action' => $action,
        'l_date_and_time' => time(),
    ));
}

/*
FOR MAKING PURCHASE
*/

/**
 * Find costings for items in the cart / an order.
 *
 * @param  array $shopping_cart_rows List of cart/order items
 * @param  string $field_name_prefix Field name prefix. Pass as blank for cart items or 'p_' for order items.
 * @return array A tuple: total price, total tax derivation, total tax, total tax tracking ID, total shipping cost, total shipping tax, total shipping weight, total shipping length, total shipping width, total shipping height
 */
function derive_cart_amounts($shopping_cart_rows, $field_name_prefix = '')
{
    // Filter out non-saleable items
    foreach ($shopping_cart_rows as $i => $item) {
        $type_code = $item[$field_name_prefix . 'type_code'];
        list($details) = find_product_details($type_code);

        if ($details === null) {
            unset($shopping_cart_rows[$i]);
            continue;
        }

        if ($details['type'] == PRODUCT_SUBSCRIPTION) {
            unset($shopping_cart_rows[$i]);
            continue; // Subscription type skipped, can't handle within an order
        }
    }

    // Work out shipping cost etc
    $shipped_products = array();
    $total_product_weight = 0.0;
    $total_product_length = 0.0;
    $total_product_width = 0.0;
    $total_product_height = 0.0;
    $total_product_volume = 0.0;
    $total_quantity = 0;
    foreach ($shopping_cart_rows as $i => $item) {
        $quantity = $item[$field_name_prefix . 'quantity'];
        $total_quantity += $quantity;

        $type_code = $item[$field_name_prefix . 'type_code'];
        list($details) = find_product_details($type_code);

        $total_product_weight += $details['product_weight'] * $quantity;
        $total_product_length += $details['product_length'] * $quantity;
        $total_product_width += $details['product_width'] * $quantity;
        $total_product_height += $details['product_height'] * $quantity;
        $total_product_volume += $details['product_length'] * $details['product_width'] * $details['product_height'] * $quantity;

        $shipped_products[] = array($item, $quantity);
    }
    if ($total_quantity > 1) {
        // We obviously can't actually gallery length/width/height, so we'll have to work out using volume
        $total_product_length = pow($total_product_weight, 1.0 / 3.0);
        $total_product_width = $total_product_length;
        $total_product_height = $total_product_length;
    }
    $total_shipping_cost = calculate_shipping_cost(null, null, $total_product_weight, $total_product_length, $total_product_width, $total_product_height);

    // Work out total price
    $total_price = 0.00;
    foreach ($shopping_cart_rows as $i => $item) {
        $type_code = $item[$field_name_prefix . 'type_code'];
        list($details) = find_product_details($type_code);

        $quantity = $item[$field_name_prefix . 'quantity'];
        $total_price += $details['price'] * $quantity;
    }

    // Split into TaxCloud and non-TaxCloud
    $taxcloud_items = array();
    $non_taxcloud_items = array();
    foreach ($shopping_cart_rows as $i => $item) {
        $type_code = $item[$field_name_prefix . 'type_code'];
        list($details) = find_product_details($type_code);

        $tax_code = $details['tax_code'];

        $usa_tic = (preg_match('#^TIC:#', $tax_code) != 0);
        if ($usa_tic) {
            $taxcloud_items[$i] = array($item, $details);
        } else {
            $non_taxcloud_items[$i] = array($item, $details);
        }
    }

    // Taxes will be put in here
    $total_tax_derivation = array();
    $total_tax = 0.00;
    $total_tax_tracking = '';
    $shopping_cart_rows_taxes = array();
    $shipping_tax_derivation = array();
    $shipping_tax = 0.00;
    $shipping_tax_tracking = array();
    $total_shipping_tax_derivation = array();
    $total_shipping_tax = 0.0;

    // Do TaxCloud call
    if (count($taxcloud_items) > 0) {
        $do_shipping_in_tax_cloud = (count($non_taxcloud_items) == 0);

        $shipping_tax_details = get_tax_using_tax_codes($taxcloud_items, $field_name_prefix, $do_shipping_in_tax_cloud ? $total_shipping_cost : 0.00/*don't incorporate as we have our own calculation anyway*/);

        foreach ($taxcloud_items as $i => $parts) {
            list($item, $details, $tax_derivation, $tax, $tax_tracking) = $parts;

            $shopping_cart_rows_taxes[$i] = array($tax_derivation, $tax, $tax_tracking);

            $total_tax += $tax;

            foreach ($tax_derivation as $tax_category => $tax_category_amount) {
                if (!array_key_exists($tax_category, $total_tax_derivation)) {
                    $total_tax_derivation[$tax_category] = 0.00;
                }
                $total_tax_derivation[$tax_category] += $tax_category_amount;
            }
        }

        $total_tax_tracking = $tax_tracking; // Any one of them, they'll all be the same

        if ($do_shipping_in_tax_cloud) {
            list($total_shipping_tax_derivation, $total_shipping_tax, $total_shipping_tax_tracking) = $shipping_tax_details;
        }
    }

    // Work out taxes for non-TaxCloud
    if (count($non_taxcloud_items) > 0) {
        foreach ($non_taxcloud_items as $i => $parts) {
            list($item, $details) = $parts;

            $price = $details['price'];
            $tax_code = $details['tax_code'];

            $quantity = $item[$field_name_prefix . 'quantity'];

            list($tax_derivation, $tax, $tax_tracking, $shipping_tax) = calculate_tax_due($item, $tax_code, $price, 0.0, null, $quantity);
            unset($shipping_tax); // Meaningless

            $shopping_cart_rows_taxes[$i] = array($tax_derivation, $tax, $tax_tracking);

            $total_tax += $tax;

            foreach ($tax_derivation as $tax_category => $tax_category_amount) {
                if (!array_key_exists($tax_category, $total_tax_derivation)) {
                    $total_tax_derivation[$tax_category] = 0.00;
                }
                $total_tax_derivation[$tax_category] += $tax_category_amount;
            }
        }

        // We always calculate shipping manually when doing any non-TaxCloud products, as it's more tuned
        list($total_shipping_tax_derivation, $total_shipping_tax, $total_shipping_tax_tracking, $total_shipping_tax) = calculate_tax_due(null, '0.00', 0.00, $total_shipping_cost);
        $total_tax += $total_shipping_tax;
    }

    // Integrate shipping tax derivation into main derivation
    foreach ($total_shipping_tax_derivation as $tax_category => $tax_category_amount) {
        if (!array_key_exists($tax_category, $total_tax_derivation)) {
            $total_tax_derivation[$tax_category] = 0.00;
        }
        $total_tax_derivation[$tax_category] += $tax_category_amount;
    }

    // Return
    return array($total_price, $total_tax_derivation, $total_tax, $total_tax_tracking, $shopping_cart_rows_taxes, $total_shipping_cost, $total_shipping_tax, $total_product_weight, $total_product_length, $total_product_width, $total_product_height);
}

/**
 * Convert a shopping cart into an order.
 *
 * @return AUTO_LINK Order ID
 */
function copy_shopping_cart_to_order()
{
    // Prepare order...

    $shopping_cart_rows = find_products_in_cart();

    if (count($shopping_cart_rows) == 0) {
        warn_exit(do_lang_tempcode('CART_EMPTY'));
    }

    list($total_price, $total_tax_derivation, $total_tax, $total_tax_tracking, $shopping_cart_rows_taxes, $total_shipping_cost, $total_shipping_tax, $total_product_weight, $total_product_length, $total_product_width, $total_product_height) = derive_cart_amounts($shopping_cart_rows);

    $shopping_order = array(
        'member_id' => get_member(),
        'session_id' => get_session_id(),
        'total_price' => $total_price,
        'total_tax_derivation' => json_encode($total_tax_derivation, defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0),
        'total_tax' => $total_tax,
        'total_tax_tracking' => $total_tax_tracking,
        'total_shipping_cost' => $total_shipping_cost,
        'total_shipping_tax' => $total_shipping_tax,
        'total_product_weight' => $total_product_weight,
        'total_product_length' => $total_product_length,
        'total_product_width' => $total_product_width,
        'total_product_height' => $total_product_height,
        'order_currency' => get_option('currency'),
        'order_status' => 'ORDER_STATUS_awaiting_payment',
        'notes' => '',
        'purchase_through' => 'cart',
        'txn_id' => '',
    );

    $shopping_order_details = array();
    foreach ($shopping_cart_rows as $i => $item) {
        $type_code = $item['type_code'];

        list($details, $product_object) = find_product_details($type_code);

        if ($details === null) {
            continue;
        }

        if ($details['type'] == PRODUCT_SUBSCRIPTION) {
            continue; // Subscription type skipped, can't handle within an order
        }

        $call_actualiser_from_cart = !isset($details['type_special_details']['call_actualiser_from_cart']) || $details['type_special_details']['call_actualiser_from_cart'];
        if ((method_exists($product_object, 'handle_needed_fields')) && ($call_actualiser_from_cart)) {
            list($purchase_id) = $product_object->handle_needed_fields($type_code);
        } else {
            $purchase_id = strval(get_member());
        }

        if (isset($shopping_cart_rows_taxes[$i])) {
            list($tax_derivation, $tax, $tax_tracking) = $shopping_cart_rows_taxes[$i];
        } else {
            list($tax_derivation, $tax, $tax_tracking, $shipping_tax) = calculate_tax_due($item, $details['tax_code'], $details['price'], 0.0, null, $item['quantity']);
            unset($shipping_tax); // Meaningless
        }

        $shopping_order_details[] = array(
            'p_type_code' => $type_code,
            'p_purchase_id' => $purchase_id,
            'p_name' => $details['item_name'],
            'p_sku' => isset($details['type_special_details']['sku']) ? $details['type_special_details']['sku'] : '',
            'p_quantity' => $item['quantity'],
            'p_price' => $details['price'],
            'p_tax_code' => $details['tax_code'],
            'p_tax' => $tax,
            'p_dispatch_status' => 'ORDER_STATUS_awaiting_payment',
        );
    }

    // See if it matches an existing unpaid order...

    $orders = $GLOBALS['SITE_DB']->query_select('shopping_orders', array('id'), $shopping_order);
    foreach ($orders as $order) {
        $_shopping_order_details = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('p_order_id' => $order['id']), 'ORDER BY p_name');
        foreach ($_shopping_order_details as &$_map) {
            unset($_map['id']);
            unset($_map['p_order_id']);
        }
        if ($shopping_order_details == $_shopping_order_details) {
            return $order['id'];
        }
    }

    // Insert order...

    $order_id = $GLOBALS['SITE_DB']->query_insert('shopping_orders', $shopping_order + array('add_date' => time()), true);
    foreach ($shopping_order_details as $map) {
        $GLOBALS['SITE_DB']->query_insert('shopping_order_details', $map + array('p_order_id' => $order_id));
    }

    // Clear out any previous unpaid & empty cart orders...

    delete_pending_orders_for_current_user($order_id, 'cart');

    return $order_id;
}

/**
 * Make a shopping cart payment button.
 *
 * @param  AUTO_LINK $order_id Order ID
 * @param  ID_TEXT $currency The currency to use
 * @param  integer $price_points Transaction price in points
 * @return Tempcode The button
 */
function make_cart_payment_button($order_id, $currency, $price_points = 0)
{
    require_css('shopping');

    $order_rows = $GLOBALS['SITE_DB']->query_select('shopping_orders', array('*'), array('id' => $order_id), '', 1);
    if (!array_key_exists(0, $order_rows)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
    }
    $order_row = $order_rows[0];

    $price = $order_row['total_price'];
    $tax_derivation = ($order_row['total_tax_derivation'] == '') ? array() : json_decode($order_row['total_tax_derivation'], true);
    $tax = $order_row['total_tax'];
    $tax_tracking = ($order_row['total_tax_tracking'] == '') ? array() : json_decode($order_row['total_tax_tracking'], true);
    $shipping_cost = $order_row['total_shipping_cost'];
    $shipping_tax = $order_row['total_shipping_tax'];

    $type_code = 'CART_ORDER_' . strval($order_id);
    $item_name = do_lang('CART_ORDER', strval($order_id));

    $_items = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('p_order_id' => $order_id));
    $items = array();
    foreach ($_items as $_item) {
        $items[] = array(
            'PRODUCT_NAME' => $_item['p_name'],
            'TYPE_CODE' => $_item['p_type_code'],
            'PRICE' => float_to_raw_string($_item['p_price']),
            'TAX' => float_to_raw_string($_item['p_tax']),
            'AMOUNT' => float_to_raw_string($_item['p_price'] + $_item['p_tax']),
            'QUANTITY' => strval($_item['p_quantity']),
        );
    }

    $invoicing_breakdown = generate_invoicing_breakdown($type_code, $item_name, strval($order_id), $price, $tax, $shipping_cost, $shipping_tax);

    $payment_gateway = get_option('payment_gateway');
    require_code('hooks/systems/payment_gateway/' . filter_naughty_harsh($payment_gateway));
    $payment_gateway_object = object_factory('Hook_payment_gateway_' . filter_naughty_harsh($payment_gateway));

    if (!method_exists($payment_gateway_object, 'make_cart_transaction_button')) {
        return $payment_gateway_object->make_transaction_button($type_code, $item_name, strval($order_id), $price, $tax, $shipping_cost, $currency, $price_points);
    }

    $trans_expecting_id = $payment_gateway_object->generate_trans_id();
    $GLOBALS['SITE_DB']->query_insert('ecom_trans_expecting', array(
        'id' => $trans_expecting_id,
        'e_type_code' => $type_code,
        'e_purchase_id' => strval($order_id),
        'e_item_name' => $item_name,
        'e_member_id' => get_member(),
        'e_session_id' => get_session_id(),
        'e_price' => $price + $shipping_cost,
        'e_tax_derivation' => json_encode($tax_derivation, defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0),
        'e_tax' => $tax,
        'e_tax_tracking' => json_encode($tax_tracking, defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0),
        'e_currency' => $currency,
        'e_price_points' => $price_points,
        'e_ip_address' => get_ip_address(),
        'e_time' => time(),
        'e_length' => null,
        'e_length_units' => '',
        'e_memo' => post_param_string('memo', ''),
        'e_invoicing_breakdown' => json_encode($invoicing_breakdown, defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0),
    ));

    return $payment_gateway_object->make_cart_transaction_button($trans_expecting_id, $items, $shipping_cost, $currency, $order_id);
}

/**
 * Tell the staff the shopping order was placed.
 *
 * @param  AUTO_LINK $order_id Order ID
 */
function send_shopping_order_purchased_staff_mail($order_id)
{
    $member_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'member_id', array('id' => $order_id));
    $displayname = $GLOBALS['FORUM_DRIVER']->get_username($member_id, true);
    $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);

    $order_details_url = build_url(array('page' => 'admin_shopping', 'type' => 'order_details', 'id' => $order_id), get_module_zone('admin_shopping'), array(), false, false, true);

    require_code('notifications');

    $subject = do_lang('ORDER_PLACED_MAIL_SUBJECT', get_site_name(), strval($order_id), array($displayname, $username), get_site_default_lang());
    $message = do_notification_lang('ORDER_PLACED_MAIL_MESSAGE', comcode_escape(get_site_name()), comcode_escape($displayname), array(strval($order_id), strval($member_id), comcode_escape($username), $order_details_url->evaluate()), get_site_default_lang());

    dispatch_notification('new_order', null, $subject, $message);
}

/*
FOR ORDER MANAGEMENT
*/

/**
 * Delete incomplete orders from ages ago.
 */
function delete_incomplete_orders()
{
    $where = db_string_equal_to('order_status', 'ORDER_STATUS_awaiting_payment') . ' AND add_date<' . strval(time() - 60 * 60 * 24 * 14/*2 weeks*/);
    $sql = 'SELECT id FROM ' . get_table_prefix() . 'shopping_orders WHERE ' . $where;
    $order_rows = $GLOBALS['SITE_DB']->query($sql);
    foreach ($order_rows as $order_row) {
        $GLOBALS['SITE_DB']->query_delete('shopping_order_details', array('p_order_id' => $order_row['id']));
        $GLOBALS['SITE_DB']->query_delete('shopping_orders', array('id' => $order_row['id']), '', 1);
    }
}

/**
 * Delete any pending orders for the current user. E.g. if cart purchase was cancelled, or cart was changed.
 *
 * @param  ?AUTO_LINK $keep_order_id ID of order to not keep (null: none)
 * @param  ?string $purchase_through Only delete orders of this origin (null: no filter)
 */
function delete_pending_orders_for_current_user($keep_order_id = null, $purchase_through = null)
{
    $where = array(
        'order_status' => 'ORDER_STATUS_awaiting_payment',
    );
    if ($purchase_through !== null) {
        $where['purchase_through'] = $purchase_through;
    }
    if (is_guest()) {
        $where['session_id'] = get_session_id();
    } else {
        $where['member_id'] = get_member();
    }

    $extra = ' AND add_date<' . strval(time() - 60 * 60 * 24 * 7); // If a week old, as otherwise a transaction may still come through
    $order_rows = $GLOBALS['SITE_DB']->query_select('shopping_orders', array('id', 'notes'), $where, $extra);

    foreach ($order_rows as $order_row) {
        if ($order_row['id'] !== $keep_order_id) {
            if ($order_row['notes'] == '') {
                $GLOBALS['SITE_DB']->query_delete('shopping_order_details', array('order_id' => $order_row['id']));
                $GLOBALS['SITE_DB']->query_delete('shopping_orders', array('id' => $order_row['id']), '', 1);
            } else {
                // Set to cancelled, as there are some notes on this order to be preserved
                $GLOBALS['SITE_DB']->query_update('shopping_order_details', array('order_status' => 'ORDER_STATUS_cancelled'), array('order_id' => $order_row['id']));
                $GLOBALS['SITE_DB']->query_update('shopping_orders', array('order_status' => 'ORDER_STATUS_cancelled'), array('id' => $order_row['id']), '', 1);
            }
        }
    }
}

/**
 * Recalculate the saved cost details related to an order. May be used after that order is changed.
 *
 * @param  AUTO_LINK $order_id The order ID
 */
function recalculate_order_costs($order_id)
{
    $product_rows = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('p_order_id' => $order_id));

    list($total_price, $total_tax_derivation, $total_tax, $total_tax_tracking, $shopping_cart_rows_taxes, $total_shipping_cost, $total_shipping_tax, $total_product_weight, $total_product_length, $total_product_width, $total_product_height) = derive_cart_amounts($product_rows, 'p_');

    $GLOBALS['SITE_DB']->query_update('shopping_orders', array(
        'total_price' => $total_price,
        'total_tax_derivation' => json_encode($total_tax_derivation, defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0),
        'total_tax' => $total_tax,
        'total_tax_tracking' => $total_tax_tracking,
        'total_shipping_cost' => $total_shipping_cost,
        'total_shipping_tax' => $total_shipping_tax,
        'total_product_weight' => $total_product_weight,
        'total_product_length' => $total_product_length,
        'total_product_width' => $total_product_width,
        'total_product_height' => $total_product_height,
    ), array('id' => $order_id, 'order_status' => 'ORDER_STATUS_awaiting_payment'), '', 1);
}

/**
 * Return list entry of common order statuses of orders.
 *
 * @return Tempcode Order status list entries
 */
function get_order_status_list()
{
    $status = array(
        'ORDER_STATUS_awaiting_payment' => do_lang_tempcode('ORDER_STATUS_awaiting_payment'),
        'ORDER_STATUS_payment_received' => do_lang_tempcode('ORDER_STATUS_payment_received'),
        'ORDER_STATUS_dispatched' => do_lang_tempcode('ORDER_STATUS_dispatched'),
        'ORDER_STATUS_onhold' => do_lang_tempcode('ORDER_STATUS_onhold'),
        'ORDER_STATUS_cancelled' => do_lang_tempcode('ORDER_STATUS_cancelled'),
        'ORDER_STATUS_returned' => do_lang_tempcode('ORDER_STATUS_returned'),
    );

    $status_list = new Tempcode();

    $status_list->attach(form_input_list_entry('', false, do_lang_tempcode('NA_EM')));

    foreach ($status as $key => $string) {
        $status_list->attach(form_input_list_entry($key, false, $string));
    }
    return $status_list;
}

/**
 * Get a string of ordered products for display.
 *
 * @param  AUTO_LINK $order_id Order ID
 * @return LONG_TEXT Products names and quantity
 */
function get_ordered_product_list_string($order_id)
{
    $product_list = array();

    $ordered_items = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('p_order_id' => $order_id), 'ORDER BY p_name');
    foreach ($ordered_items as $ordered_item) {
        $product_list[] = $ordered_item['p_name'] . ' x ' . integer_format($ordered_item['p_quantity']) . ' @ ' . do_lang('PRICE') . '=' . float_format($ordered_item['p_price']);
    }

    return implode("\n", $product_list);
}
