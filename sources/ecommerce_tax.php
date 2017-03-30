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

// DON'T include this file directly. Include 'ecommerce' instead.

/**
 * Multiply tax up or down to reflect a price being multiplied up or down.
 * This does nothing for a semantic tax code or a simple percentage.
 * For a simple flat figure it does though.
 *
 * @param  ID_TEXT $tax_code The tax code.
 * @param  float $multiplier The multipler.
 * @return ID_TEXT The amended tax code.
 */
function tax_multiplier($tax_code, $multiplier)
{
    if (($tax_code != '') && (is_numeric($tax_code[0])) && (substr($tax_code, -1) != '%')) {
        return float_to_raw_string(floatval($tax_code) * $multiplier);
    }

    return $tax_code;
}

/**
 * Calculate tax that is due based on customer context.
 * Wraps get_tax_using_tax_codes which can do bulk-lookups.
 *
 * @param  ?array $details Map of product details (null: there's no product directly associated). Not strictly needed, only passed for customisation potential.
 * @param  ID_TEXT $tax_code The tax code. This may be different to the product default, e.g. if a discount is in place.
 * @param  REAL $amount The amount.
 * @param  REAL $shipping_cost The shipping cost.
 * @param  ?MEMBER $member_id The member this is for (null: current member).
 * @param  integer $quantity The quantity of items.
 * @return array A tuple: The tax derivation, tax due (including shipping tax), tax tracking ID (at time of writing this is just with TaxCloud), shipping tax
 */
function calculate_tax_due($details, $tax_code, $amount, $shipping_cost = 0.00, $member_id = null, $quantity = 1)
{
    // ADD CUSTOM CODE HERE BY OVERRIDING THIS FUNCTION

    // We will lookup via a single item going through our main get_tax_using_tax_codes worker function
    $item_details = array();
    $item = array(
        'quantity' => $quantity,
    );
    $details = array(
        'tax_code' => $tax_code,
        'price' => $amount,
    );
    $item_details[] = array($item, $details);

    // Do main call
    list($shipping_tax_derivation, $shipping_tax, $shipping_tax_tracking) = get_tax_using_tax_codes($item_details, '', $shipping_cost);

    // Extract result for our single item
    $tax_derivation = $item_details[0][2];
    $tax = $item_details[0][3];
    $tax_tracking = $item_details[0][4];

    // Add in shipping to tax derivation and tax
    foreach ($shipping_tax_derivation as $tax_category => $tax_category_amount) {
        if (!array_key_exists($tax_category, $tax_derivation)) {
            $tax_derivation[$tax_category] = 0.00;
        }
        $tax_derivation[$tax_category] += $tax_category_amount;
    }
    $tax += $shipping_tax;

    return array($tax_derivation, $tax, $tax_tracking, $shipping_tax);
}

/**
 * Find the tax for a number of items being sold together.
 *
 * @param  array $item_details A list of pairs: shopping-cart/order style row (with at least 'quantity'), product details (with at least 'tax_code' and 'price'). This is returned by reference as a list of tuples, (tax, tax_derivation, tax_tracking) gets appended.
 * @param  string $field_name_prefix Field name prefix. Pass as blank for cart items or 'p_' for order items.
 * @param  REAL $shipping_cost The shipping cost.
 * @param  ?MEMBER $member_id The member this is for (null: current member).
 * @return array A tuple: The shipping tax derivation, shipping tax due (including shipping tax), shipping tax tracking ID (at time of writing this is just with TaxCloud)
 */
function get_tax_using_tax_codes(&$item_details, $field_name_prefix = '', $shipping_cost = 0.00, $member_id = null)
{
    // ADD CUSTOM CODE HERE BY OVERRIDING THIS FUNCTION

    $taxcloud_item_details = array();
    $non_taxcloud_item_details = array();
    $free_item_details = array();
    $has_eu_digital_goods = false;
    $tax_tracking = array();
    $shipping_tax_derivation = array();
    $shipping_tax = 0.00;
    foreach ($item_details as $i => $parts) {
        list($item, $details) = $parts;

        $tax_code = $details['tax_code'];
        $amount = $details['price'];

        $usa_tic = (preg_match('#^TIC:#', $tax_code) != 0);
        if ($usa_tic) {
            $taxcloud_item_details[$i] = $parts;
        } elseif ($amount == 0.00) {
            $free_item_details[$i] = $parts;
        } else {
            $non_taxcloud_item_details[$i] = $parts;

            if ($tax_code == 'EU') {
                $has_eu_digital_goods = true;
            }
        }
    }

    if (($has_eu_digital_goods) || (count($taxcloud_item_details) != 0)) {
        $shipping_email = '';
        $shipping_phone = '';
        $shipping_firstname = '';
        $shipping_lastname = '';
        $shipping_street_address = '';
        $shipping_city = '';
        $shipping_county = '';
        $shipping_state = '';
        $shipping_post_code = '';
        $shipping_country = '';
        $shipping_email = '';
        $shipping_phone = '';
        $cardholder_name = '';
        $card_type = '';
        $card_number = null;
        $card_start_date_year = null;
        $card_start_date_month = null;
        $card_expiry_date_year = null;
        $card_expiry_date_month = null;
        $card_issue_number = null;
        $card_cv2 = null;
        $billing_street_address = '';
        $billing_city = '';
        $billing_county = '';
        $billing_state = '';
        $billing_post_code = '';
        $billing_country = '';
        get_default_ecommerce_fields($member_id, $shipping_email, $shipping_phone, $shipping_firstname, $shipping_lastname, $shipping_street_address, $shipping_city, $shipping_county, $shipping_state, $shipping_post_code, $shipping_country, $cardholder_name, $card_type, $card_number, $card_start_date_year, $card_start_date_month, $card_expiry_date_year, $card_expiry_date_month, $card_issue_number, $card_cv2, $billing_street_address, $billing_city, $billing_county, $billing_state, $billing_post_code, $billing_country, true);

        if ($shipping_street_address == '') {
            $street_address = $billing_street_address;
            $city = $billing_city;
            $county = $billing_county;
            $state = $billing_state;
            $post_code = $billing_post_code;
            $country = $billing_country;
        } else {
            $street_address = $shipping_street_address;
            $city = $shipping_city;
            $county = $shipping_county;
            $state = $shipping_state;
            $post_code = $shipping_post_code;
            $country = $shipping_country;
        }
    }

    if (count($taxcloud_item_details) != 0) {
        check_taxcloud_configured_correctly();

        if ($country == 'US') {
            // Cleanup address...

            $url = 'https://api.taxcloud.com/1.0/TaxCloud/VerifyAddress';

            $zip_parts = explode('-', $post_code, 2);
            $request = array(
                'apiLoginID' => get_option('taxcloud_api_id'),
                'apiKey' => get_option('taxcloud_api_key'),
                'Address1' => $street_address,
                'Address2' => '',
                'City' => $city,
                'State' => $state,
                'Zip5' => $zip_parts[0],
                'Zip4' => array_key_exists(1, $zip_parts) ? $zip_parts[1] : '',
            );
            $post_params = array(json_encode($request));

            $_response = http_download_file($url, null, true, false, 'Composr', $post_params, null, null, null, null, null, null, null, 10.0, true, null, null, null, 'application/json'); // TODO: Fix in v11
            $response = json_decode($_response, true);

            if ($response['ErrNumber'] == 0) {
                $street_address = $response['Address1'];
                $city = $response['City'];
                $state = $response['State'];
                $post_code = $response['Zip5'] . (($response['Zip4'] == '') ? '' : ('-' . $response['Zip4']));
            }

            // Work out TaxCloud call...

            $cart_items = array();
            foreach ($taxcloud_item_details as $i => $parts) {
                list($item, $details) = $parts;

                $quantity = $item[$field_name_prefix . 'quantity'];
                $tax_code = $details['tax_code'];
                $amount = $details['price'];
                $sku = empty($details['type_special_details']['sku']) ? strval('item' . strval($i)) : $details['type_special_details']['sku'];

                $cart_items[$i] = array(
                    'Qty' => $quantity,
                    'Price' => $amount,
                    'TIC' => intval(substr($tax_code, strlen('TIC:'))),
                    'ItemID' => $sku,
                    'Index' => $i,
                );
            }

            if ($shipping_cost != 0.00) {
                $cart_items[count($taxcloud_item_details)] = array(
                    'Qty' => 1,
                    'Price' => $shipping_cost,
                    'TIC' => 11010,
                    'ItemID' => 'shipping',
                    'Index' => count($taxcloud_item_details),
                );
            }

            $url = 'https://api.taxcloud.com/1.0/TaxCloud/Lookup';

            $zip_parts = explode('-', $post_code, 2);
            $business_zip_parts = explode('-', get_option('business_post_code'), 2);
            $request = array(
                'apiLoginID' => get_option('taxcloud_api_id'),
                'apiKey' => get_option('taxcloud_api_key'),
                'customerID' => is_guest($member_id) ? ('guest-' . get_session_id()) : ('member-' . strval($member_id)),
                'deliveredBySeller' => false,
                'cartID' => '',
                'destination' => array(
                    'Address1' => $street_address,
                    'City' => $city,
                    'State' => $state,
                    'Zip5' => $zip_parts[0],
                    'Zip4' => array_key_exists(1, $zip_parts) ? $zip_parts[1] : '',
                ),
                'origin' => array(
                    'Address1' => get_option('business_street_address'),
                    'City' => get_option('business_city'),
                    'State' => get_option('business_state'),
                    'Zip5' => $business_zip_parts[0],
                    'Zip4' => array_key_exists(1, $business_zip_parts) ? $business_zip_parts[1] : '',
                ),
                'cartItems' => $cart_items,
            );
            $post_params = array(json_encode($request));

            // Do TaxCloud call...

            $_response = http_download_file($url, null, true, false, 'Composr', $post_params, null, null, null, null, null, null, null, 10.0, true, null, null, null, 'application/json'); // TODO: Fix in v11
            $response = json_decode($_response, true);

            // Error handling...

            if ($response['ResponseType'] != 3) {
                fatal_exit($response['Messages'][0]['Message']);
            }

            // Process TaxCloud results...

            foreach ($response['CartItemsResponse'] as $cart_item) {
                $i = $cart_item['CartItemIndex'];

                if (isset($taxcloud_item_details[$i])) {
                    $tax = $cart_item['TaxAmount'];
                    $tax_derivation = array('TaxCloud' => $tax);

                    $taxcloud_item_details[$i][2] = $tax_derivation;
                    $taxcloud_item_details[$i][3] = $tax;
                    $taxcloud_item_details[$i][4] = $response['CartID'];
                    $item_details[$i] = $taxcloud_item_details[$i];
                } else {
                    // Shipping...

                    $shipping_tax = $cart_item['TaxAmount'];
                    $shipping_tax_derivation = array('TaxCloud' => $shipping_tax);
                }
            }

            $tax_tracking = array('taxcloud' => $response['CartID']);
        } else {
            // EXCEPTION: Not in USA
            foreach ($taxcloud_item_details as $i => $parts) {
                $free_item_details[$i] = $parts;
                unset($non_taxcloud_item_details[$i]);
            }
        }
    }

    foreach ($non_taxcloud_item_details as $i => $parts) {
        list($item, $details) = $parts;

        $quantity = $item[$field_name_prefix . 'quantity'];
        $tax_code = $details['tax_code'];
        $amount = $details['price'];

        // Europe
        if ($tax_code == 'EU') {
            require_code('files2');
            list($__rates) = cache_and_carry('http_download_file', array('http://euvat.ga/rates.json'));
            $_rates = json_decode($__rates, true); // TODO: Fix in v11

            if (isset($_rates['rates'][$country])) {
                $rate = $_rates['rates'][$country]['standard_rate'];
            } else {
                // EXCEPTION: Not in Europe
                $free_item_details[$i] = $parts;
                unset($non_taxcloud_item_details[$i]);
                continue;
            }

            $tax = round(($rate / 100.0) * $amount * $quantity, 2);
            $tax_derivation = array($country => $tax);
        } else {
            // Simple, with some guards...

            $php_errormsg = mixed();
            $tax_country_regexp = get_option('tax_country_regexp');
            if (!empty($tax_country_regexp)) {
                $check = @preg_match('#' . $tax_country_regexp . '#', $country);
                if ($check === false) {
                    warn_exit(do_lang_tempcode('INVALID_REGULAR_EXPRESSION', do_lang('TAX_COUNTRY_REGEXP'), escape_html($tax_country_regexp), $php_errormsg));
                }
                if ($check == 0) {
                    // EXCEPTION: Country not covered
                    $free_item_details[$i] = $parts;
                    unset($non_taxcloud_item_details[$i]);
                    continue;
                }
            }
            $tax_state_regexp = get_option('tax_state_regexp');
            if (!empty($tax_state_regexp)) {
                $check = @preg_match('#' . $tax_state_regexp . '#', $state);
                if ($check === false) {
                    warn_exit(do_lang_tempcode('INVALID_REGULAR_EXPRESSION', do_lang('TAX_STATE_REGEXP'), escape_html($tax_country_regexp), $php_errormsg));
                }
                if ($check == 0) {
                    // EXCEPTION: State not covered
                    $free_item_details[$i] = $parts;
                    unset($non_taxcloud_item_details[$i]);
                    continue;
                }
            }

            // Simple rate
            if (substr($tax_code, -1) == '%') {
                $rate = floatval($tax_code);
                $tax = round(($rate / 100.0) * $amount * $quantity, 2);
            } else {
                // Simple flat
                $tax = round(floatval($tax_code) * $quantity, 2);
            }

            $tax_derivation = array('?' => $tax);
        }

        $non_taxcloud_item_details[$i][2] = $tax_derivation;
        $non_taxcloud_item_details[$i][3] = $tax;
        $non_taxcloud_item_details[$i][4] = array();
        $item_details[$i] = $non_taxcloud_item_details[$i];
    }

    foreach ($free_item_details as $i => $parts) {
        list($item, $details) = $parts;

        $free_item_details[$i][2] = array();
        $free_item_details[$i][3] = 0.00;
        $free_item_details[$i][4] = array();
        $item_details[$i] = $free_item_details[$i];
    }

    if (count($taxcloud_item_details) == 0) {
        if ($shipping_cost != 0.00) {
            list($shipping_tax_derivation, $shipping_tax, , ) = calculate_tax_due(null, $tax_code, $amount, 0.00, $member_id); // This will force a call back into our function, but won't recurse again
        }
    }

    $shipping_tax_tracking = $tax_tracking;

    return array($shipping_tax_derivation, $shipping_tax, $shipping_tax_tracking);
}

/**
 * Check that TaxCloud is correctly configured.
 */
function check_taxcloud_configured_correctly()
{
    // Check for configuration errors
    if (get_option('business_country') != 'US') {
        warn_exit(do_lang_tempcode('TIC__BUSINESS_COUNTRY_NOT_USA')); // TODO: Error mail to site in v11
    }
    if (get_option('currency') != 'USD') {
        warn_exit(do_lang_tempcode('TIC__CURRENCY_NOT_USD')); // TODO: Error mail to site in v11
    }
    global $USA_STATE_LIST;
    if (!array_key_exists(get_option('business_state'), $USA_STATE_LIST)) {
        warn_exit(do_lang_tempcode('TIC__USA_STATE_INVALID')); // TODO: Error mail to site in v11
    }
    if ((get_option('taxcloud_api_key') == '') || (get_option('taxcloud_api_id') == '')) {
        warn_exit(do_lang_tempcode('TIC__TAXCLOUD_NOT_CONFIGURED')); // TODO: Error mail to site in v11
    }
}

/**
 * Mark an order completed on TaxCloud, i.e. tax has been received for payment to the tax authority.
 *
 * @param  ID_TEXT $tracking_id The TaxCloud tracking ID.
 * @param  ID_TEXT $txn_id The transaction ID.
 * @param  MEMBER $member_id The member ID.
 * @param  ID_TEXT $session_id The session ID of the purchaser.
 */
function taxcloud_declare_completed($tracking_id, $txn_id, $member_id, $session_id)
{
    $url = 'https://api.taxcloud.com/1.0/TaxCloud/AuthorizedWithCapture';

    //$date = date('Y-m-d', tz_time(time(), get_site_timezone()));
    $date = date('Y-m-d'); // UTC-based according to TaxCloud support
    $request = array(
        'apiLoginID' => get_option('taxcloud_api_id'),
        'apiKey' => get_option('taxcloud_api_key'),
        'customerID' => is_guest($member_id) ? ('guest-' . $session_id) : ('member-' . strval($member_id)),
        'cartID' => $tracking_id,
        'orderID' => $txn_id,
        'dateAuthorized' => $date,
        'dateCaptured' => $date,
    );
    $post_params = array(json_encode($request));

    $_response = http_download_file($url, null, true, false, 'Composr', $post_params, null, null, null, null, null, null, null, 10.0, true, null, null, null, 'application/json'); // TODO: Fix in v11
    $response = json_decode($_response, true);

    if ($response['ResponseType'] != 3) {
        trigger_error(implode('; ', $response['Messages']), E_USER_WARNING);
    }
}

/**
 * Work out the tax rate for a given payment amount and flat tax figure.
 *
 * @param  REAL $amount Amount.
 * @param  REAL $tax Tax in money.
 * @return REAL The tax rate (as a percentage).
 */
function backcalculate_tax_rate($amount, $tax)
{
    if ($amount == 0.00) {
        return 0.0;
    }
    return round(100.0 * ($tax / $amount), 1);
}

/**
 * Generate an invoicing breakdown.
 *
 * @param  ID_TEXT $type_code The product codename.
 * @param  SHORT_TEXT $item_name The human-readable product title.
 * @param  ID_TEXT $purchase_id The purchase ID.
 * @param  REAL $price Transaction price in money.
 * @param  REAL $tax Transaction tax in money (including shipping tax).
 * @param  REAL $shipping_cost Transaction shipping cost in money.
 * @param  REAL $shipping_tax Transaction shipping tax in money.
 * @return array Invoicing breakdown.
 */
function generate_invoicing_breakdown($type_code, $item_name, $purchase_id, $price, $tax, $shipping_cost = 0.00, $shipping_tax = 0.00)
{
    $invoicing_breakdown = array();

    if (preg_match('#^CART\_ORDER\_\d+$#', $type_code) == 0) {
        // Not a cart order...

        $invoicing_breakdown[] = array(
            'type_code' => $type_code,
            'item_name' => $item_name,
            'quantity' => 1,
            'unit_price' => $price,
            'unit_tax' => $tax - $shipping_tax,
        );
    } else {
        // A cart order...

        $order_id = intval(substr($type_code, strlen('CART_ORDER_')));

        $total_price = 0.00;
        $total_tax = 0.00;

        $_items = $GLOBALS['SITE_DB']->query_select('shopping_order_details', array('*'), array('p_order_id' => $order_id));
        foreach ($_items as $_item) {
            $invoicing_breakdown[] = array(
                'type_code' => $_item['p_type_code'],
                'item_name' => $_item['p_name'],
                'quantity' => $_item['p_quantity'],
                'unit_price' => $_item['p_price'],
                'unit_tax' => $_item['p_tax'],
            );

            $total_price += $_item['p_price'];
            $total_tax += $_item['p_tax'];
        }

        if ($shipping_cost !== null) {
            $total_price += $shipping_cost;
        }

        if (($total_price != $price) || ($total_tax != $tax - $shipping_tax)) {
            $invoicing_breakdown[] = array(
                'type_code' => '',
                'item_name' => do_lang('PRICING_ADJUSTMENT'),
                'quantity' => 1,
                'unit_price' => $price - $total_price,
                'unit_tax' => $tax - $total_tax,
            );
        }
    }

    if (($shipping_cost !== 0.00) || ($shipping_tax !== 0.00)) {
        $invoicing_breakdown[] = array(
            'type_code' => '',
            'item_name' => do_lang('SHIPPING'),
            'quantity' => 1,
            'unit_price' => $shipping_cost,
            'unit_tax' => $shipping_tax,
        );
    }

    return $invoicing_breakdown;
}

/**
 * Send an invoice notification to a member.
 *
 * @param  MEMBER $member_id The member to send to.
 * @param  AUTO_LINK $id The invoice ID.
 */
function send_invoice_notification($member_id, $id)
{
    // Send out notification
    require_code('notifications');
    $_url = build_url(array('page' => 'invoices', 'type' => 'browse'), get_module_zone('invoices'), null, false, false, true);
    $url = $_url->evaluate();
    $subject = do_lang('INVOICE_SUBJECT', strval($id), null, null, get_lang($member_id));
    $body = do_notification_lang('INVOICE_MESSAGE', $url, get_site_name(), null, get_lang($member_id));
    dispatch_notification('invoice', null, $subject, $body, array($member_id));
}

/**
 * Generate tax invoice.
 *
 * @param  ID_TEXT $txn_id Transaction ID.
 * @return Tempcode Tax invoice.
 */
function generate_tax_invoice($txn_id)
{
    require_css('ecommerce');
    require_code('locations');

    $transaction_row = get_transaction_row($txn_id);

    $address_rows = $GLOBALS['SITE_DB']->query_select('ecom_trans_addresses', array('*'), array('a_trans_expecting_id' => $txn_id), '', 1);
    $trans_address = '';
    if (array_key_exists(0, $address_rows)) {
        $address_row = $address_rows[0];

        $lines = array(
            $address_row['a_firstname'] . ' ' . $address_row['a_lastname'],
            $address_row['a_street_address'],
            $address_row['a_city'],
            $address_row['a_county'],
            $address_row['a_state'],
            $address_row['a_post_code'],
            find_country_name_from_iso($address_row['a_country']),
        );
        foreach ($lines as $line) {
            if (trim($line) != '') {
                $trans_address .= trim($line) . "\n";
            }
        }
        $trans_address = rtrim($trans_address);
    }

    $items = ($transaction_row['t_invoicing_breakdown'] == '') ? array() : json_decode($transaction_row['t_invoicing_breakdown'], true);
    $invoicing_breakdown = array();
    foreach ($items as $item) {
        $invoicing_breakdown[] = array(
            'TYPE_CODE' => $item['type_code'],
            'ITEM_NAME' => $item['item_name'],
            'QUANTITY' => $item['quantity'],
            'UNIT_PRICE' => float_format($item['unit_price']),
            'PRICE' => float_format($item['unit_price'] * $item['quantity']),
            'UNIT_TAX' => float_format($item['unit_tax']),
            'TAX' => float_format($item['unit_tax'] * $item['quantity']),
            'TAX_RATE' => float_format(backcalculate_tax_rate($item['unit_price'], $item['unit_tax']), 1, true),
        );
    }
    if (count($invoicing_breakdown) == 0) { 
        // We don't have a break-down so at least find a single line-item

        list($details) = find_product_details($transaction_row['t_type_code']);
        if ($details !== null) {
            $item_name = $details['item_name'];
        } else {
            $item_name = $transaction_row['t_type_code'];
        }

        $invoicing_breakdown[] = array(
            'TYPE_CODE' => $transaction_row['t_type_code'],
            'ITEM_NAME' => $item_name,
            'QUANTITY' => 1,
            'UNIT_PRICE' => float_format($transaction_row['t_amount']),
            'PRICE' => float_format($transaction_row['t_amount']),
            'UNIT_TAX' => float_format($transaction_row['t_tax']),
            'TAX' => float_format($transaction_row['t_tax']),
            'TAX_RATE' => float_format(backcalculate_tax_rate($transaction_row['t_amount'], $transaction_row['t_tax']), 1, true),
        );
    }

    $status = get_transaction_status_string($transaction_row['t_status']);

    return do_template('ECOM_TAX_INVOICE', array(
        'TXN_ID' => $txn_id,
        '_DATE' => strval($transaction_row['t_time']),
        'DATE' => get_timezoned_date($transaction_row['t_time'], false, false, false, true),
        'TRANS_ADDRESS' => $trans_address,
        'ITEMS' => $invoicing_breakdown,
        'CURRENCY' => $transaction_row['t_currency'],
        'TOTAL_PRICE' => float_format($transaction_row['t_amount']),
        'TOTAL_TAX' => float_format($transaction_row['t_tax']),
        'TOTAL_AMOUNT' => float_format($transaction_row['t_amount'] + $transaction_row['t_tax']),
        'PURCHASE_ID' => $transaction_row['t_purchase_id'],
        'STATUS' => $status,
    ));
}

/**
 * Get the Tempcode for a tax input widget.
 *
 * @param  mixed $set_title A human intelligible name for this input field
 * @param  mixed $description A description for this input field
 * @param  ID_TEXT $set_name The name which this input field is for
 * @param  string $default The default value for this input field
 * @param  boolean $required Whether this is a required input field
 * @param  ?integer $tabindex The tab index of the field (null: not specified)
 * @return Tempcode The input field
 */
function form_input_tax_code($set_title, $description, $set_name, $default, $required, $tabindex = null)
{
    $tabindex = get_form_field_tabindex($tabindex);

    $default = filter_form_field_default($set_name, $default);

    $required = filter_form_field_required($set_name, $required);
    $_required = ($required) ? '_required' : '';

    $fields = new Tempcode();
    $field_set = alternate_fields_set__start($set_name);

    $default_set = 'rate';

    // Simple rate input ...

    $has_rate = (substr($default, -1) == '%');
    if ($has_rate) {
        $default_set = 'rate';
    }
    $input = do_template('FORM_SCREEN_INPUT_FLOAT', array(
        'TABINDEX' => strval($tabindex),
        'REQUIRED' => $_required,
        'NAME' => $set_name . '_rate',
        'DEFAULT' => $has_rate ? float_format(floatval($default), 2, false) : '',
    ));
    $field_set->attach(_form_input($set_name . '_rate', do_lang_tempcode('TAX_RATE'), do_lang_tempcode('DESCRIPTION_TAX_RATE'), $input, $required, false, $tabindex));

    // TaxCloud input...

    require_code('files2');
    $has_tic = (preg_match('#^TIC:#', $default) != 0);
    if ($has_tic) {
        $default_set = 'tic';
    }
    list($__tics) = cache_and_carry('http_download_file', array('https://taxcloud.net/tic/?format=json', null, false));
    $_tics = @json_decode($__tics, true); // TODO: Fix in v11
    if (($_tics !== false) && ($_tics !== null)) {
        $tics = new Tempcode();
        $tics->attach(_prepare_tics_list($_tics['tic_list'], $has_tic ? substr($default, 4) : '', 'root'));
        $tics->attach(_prepare_tics_list($_tics['tic_list'], $has_tic ? substr($default, 4) : '', ''));
        require_css('widget_select2');
        require_javascript('jquery');
        require_javascript('select2');
        $input = do_template('FORM_SCREEN_INPUT_LIST', array(
            'TABINDEX' => strval($tabindex),
            'REQUIRED' => $_required,
            'NAME' => $set_name . '_tic',
            'CONTENT' => $tics,
            'INLINE_LIST' => false,
            'SIZE' => strval(5),
        ));
        $field_set->attach(_form_input($set_name . '_tic', do_lang_tempcode('TAX_TIC'), do_lang_tempcode('DESCRIPTION_TAX_TIC'), $input, $required, false, $tabindex));
    }

    // EU rate input...

    $has_eu = ($default == 'EU');
    if ($has_eu) {
        $default_set = 'eu';
    }
    $input = form_input_hidden($set_name . '_eu', '1');
    $field_set->attach(_form_input($set_name . '_eu', do_lang_tempcode('TAX_EU'), do_lang_tempcode('DESCRIPTION_TAX_EU'), $input, $required, false, $tabindex));

    // --

    $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required, null, false, $default_set));
    return $fields;
}

/**
 * Get a hierarchical TIC selection list.
 *
 * @param  array $all_tics The list of TICs
 * @param  string $default Default value
 * @param  string $parent Only get child nodes of
 * @param  string $pre Prefix for parent chain
 * @return Tempcode The list
 */
function _prepare_tics_list($all_tics, $default, $parent, $pre = '')
{
    $child_tics = array();
    foreach ($all_tics as $i => $_tic) {
        $tic = $_tic['tic'];
        if ($tic['parent'] == $parent) {
            $child_tics[] = $tic;
        }

        if (isset($tic['children'])) {
            foreach ($tic['children'] as $__tic) {
                $all_tics[] = $__tic;
            }
            unset($all_tics[$i]['tic']['children']);
        }
    }
    sort_maps_by($child_tics, 'label');

    $tics_list = new Tempcode();
    foreach ($child_tics as $tic) {
        $text = $pre . html_entity_decode($tic['label'], ENT_QUOTES, get_charset());
        $title = html_entity_decode($tic['title'], ENT_QUOTES, get_charset());
        $tics_list->attach(form_input_list_entry($tic['id'], $tic['id'] == $default, $text, false, false, $title));

        $under = _prepare_tics_list($all_tics, $default, $tic['id'], $text . ' > ');
        $tics_list->attach($under);
    }

    return $tics_list;
}

/**
 * Read a tax value from the POST environment.
 *
 * @param  string $name Variable name
 * @param  string $default Default value
 * @return string The value
 */
function post_param_tax_code($name, $default = '0%')
{
    $value = post_param_string($name . '_flat', ''); // Simple flat figure
    if ($value == '') {
        $value = post_param_string($name . '_rate', ''); // Simple rate
        if ($value == '') {
            $value = post_param_string($name . '_tic', ''); // Semantic: TaxCloud
            if ($value == '') {
                if (substr(post_param_string($name, ''), -3) == '_eu') { // Semantic: EU rate
                    $value = 'EU';
                } else {
                    $value = $default; // Default
                }
            } else {
                $value = 'TIC:' . $value;
            }
        } else {
            $value = float_to_raw_string(float_unformat($value)) . '%';
        }
    } else {
        $value = float_to_raw_string(float_unformat($value)); // There's actually no UI option for simple flat figure, but it may be used internally
    }
    return $value;
}
