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

// DON'T include this file directly. Include 'ecommerce' instead.

/**
 * Recalculate shipping cost based on customer context.
 * Does not handle multiple quantities or items, this is complex in itself (due to dimension merging) and handled in derive_cart_amounts.
 *
 * @param  ?array $details Map of product details (null: not for a final sold product, some kind of intermediate stage or peripheral check).
 * @param  ?REAL $shipping_cost The default shipping cost (null: unknown).
 * @param  ?float $product_weight Weight of product (null: unknown).
 * @param  ?float $product_length Length of product (null: unknown).
 * @param  ?float $product_width Width of product (null: unknown).
 * @param  ?float $product_height Height of product (null: unknown).
 * @param  ?MEMBER $member_id The member this is for (null: current member).
 * @return REAL The shipping cost.
 */
function calculate_shipping_cost($details, $shipping_cost, &$product_weight, &$product_length, &$product_width, &$product_height, $member_id = null)
{
    // ADD CUSTOM CODE HERE BY OVERRIDING THIS FUNCTION

    // Normalise things, sometimes things will not be set but we can derive it...

    // Get dimensions from each other
    if (($product_length !== null) || ($product_width !== null) || ($product_height !== null)) {
        if ($product_length === null) {
            $product_length = ($product_width !== null) ? $product_width : $product_height;
        }
        if ($product_width === null) {
            $product_width = ($product_length !== null) ? $product_length : $product_height;
        }
        if ($product_height === null) {
            $product_height = ($product_length !== null) ? $product_length : $product_width;
        }
    }

    // Get weight from dimensions
    if ($product_weight === null) {
        if (($product_length !== null) && ($product_width !== null) && ($product_height !== null)) {
            $product_volume = $product_length * $product_width * $product_height;

            $product_weight = $product_volume / floatval(get_option('shipping_density'));
        }
    }

    // Get dimensions from weight
    if ($product_length === null) {
        if ($product_weight !== null) {
            $product_volume = $product_weight * floatval(get_option('shipping_density'));

            $product_length = pow($product_volume, 1.0 / 3.0);
            $product_width = $product_length;
            $product_height = $product_length;
        }
    }

    // Are we okay to do calculations?
    $got_what_we_need = (($product_weight !== null) && ($product_length !== null) && ($product_width !== null) && ($product_height !== null));
    if ((!$got_what_we_need) && ($shipping_cost !== null)) {
        return $shipping_cost; // This is better than estimating something from zero; a flat shipping cost that carries through
    }

    // Set to zero if we still failed
    if ($product_weight === null) {
        $product_weight = 0.0;
    }
    if ($product_length === null) {
        $product_length = 0.0;
    }
    if ($product_width === null) {
        $product_width = 0.0;
    }
    if ($product_height === null) {
        $product_height = 0.0;
    }

    // Do calculation if we don't have Shippo...

    $shippo_token = get_option(ecommerce_test_mode() ? 'shipping_shippo_api_test' : 'shipping_shippo_api_live');

    $base = get_base_shipping_cost();

    if ($shippo_token == '') {
        $factor = floatval(get_option('shipping_cost_factor'));
        $shipping_cost = $base + $product_weight * $factor;

        return round($shipping_cost, 2);
    }

    // Do Shippo call...

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
    $country = _make_country_for_shippo($country);

    list($company, $street_address_1, $street_address_2) = split_street_address($street_address, 3, true);
    list($business_street_address_1, $business_street_address_2) = split_street_address(get_option('business_street_address'), 2);

    $request = array(
        'object_purpose' => 'QUOTE',
        'address_to' => array(
            'name' => trim($shipping_firstname . ' ' . $shipping_lastname),
            'company' => $company,
            'street1' => $street_address_1,
            'street2' => $street_address_2,
            'city' => $city,
            'state' => $state,
            'zip' => $post_code,
            'country' => $country,
            'phone' => $shipping_phone,
            'email' => $shipping_email,
            'object_purpose' => 'QUOTE',
        ),
        'address_from' => array(
            'name' => get_option('business_name'), // This would be company name, so no need for separate company field
            'street1' => $business_street_address_1,
            'street2' => $business_street_address_2,
            'city' => get_option('business_city'),
            'state' => get_option('business_state'),
            'zip' => get_option('business_post_code'),
            'country' => _make_country_for_shippo(get_option('business_country')),
            'phone' => get_option('pd_number'),
            'email' => get_option('pd_email'),
            'object_purpose' => 'QUOTE',
        ),
        'parcel' => array(
            'length' => float_to_raw_string($product_length),
            'width' => float_to_raw_string($product_width),
            'height' => float_to_raw_string($product_height),
            'distance_unit' => strtolower(get_option('shipping_distance_units')),
            'weight' => float_to_raw_string($product_weight),
            'mass_unit' => strtolower(get_option('shipping_weight_units')),
        ),
        'async' => false,
    );
    $post_params = array(json_encode($request));
    $url = 'https://api.goshippo.com/shipments/';
    $_response = http_get_contents($url, array('post_params' => $post_params, 'timeout' => 10.0, 'raw_post' => true, 'extra_headers' => array('Authorization' => 'ShippoToken ' . $shippo_token), 'raw_content_type' => 'application/json', 'ignore_http_status' => true));
    $response = json_decode($_response, true);

    // Error handling
    $error_message = '';
    foreach ($response['messages'] as $error_struct) {
        if ($error_message != '') {
            $error_message = '';
        }
        $error_message .= $error_struct['text'];
    }
    if ($response['object_status'] == 'ERROR') {
        fatal_exit(do_lang_tempcode('SHIPPING_ERROR', escape_html($error_message)));
    }
    if (!isset($response['rates_list'][0])) {
        if ($error_message != '') {
            fatal_exit(do_lang_tempcode('SHIPPING_ERROR', escape_html($error_message)));
        }

        fatal_exit(do_lang_tempcode('NO_SHIPPING_RESULT'));
    }

    require_code('currency');
    $price = floatval($response['rates_list'][0]['amount']);
    $price = currency_convert($price, $response['rates_list'][0]['currency'], null, CURRENCY_DISPLAY_RAW);

    $shipping_cost = round($base + $price, 2);
    return $shipping_cost;
}

/**
 * Make an ISO country code shippo-compatible.
 *
 * @param  string $country ISO country code.
 * @return string Shippo-compatible code.
 */
function _make_country_for_shippo($country)
{
    if ($country == 'UK') {
        $country = 'GB'; // A quirk of ISO. Actually 'UK' should not be in the system
    }
    return $country;
}

/**
 * Get the base shipping cost for the shopping cart.
 *
 * @return float Base shipping cost.
 */
function get_base_shipping_cost()
{
    static $ret = null;

    if ($ret === null) {
        $option = get_option('shipping_cost_base');
        $ret = round(float_unformat($option), 2);
    }

    return $ret;
}

/**
 * Get form fields for a shipping/invoice address.
 *
 * @param  string $shipping_email E-mail address.
 * @param  string $shipping_phone Phone number.
 * @param  string $shipping_firstname First name.
 * @param  string $shipping_lastname Last name.
 * @param  string $shipping_street_address Street address.
 * @param  string $shipping_city Town/City.
 * @param  string $shipping_county County.
 * @param  string $shipping_state State.
 * @param  string $shipping_post_code Postcode/Zip.
 * @param  string $shipping_country Country.
 * @param  boolean $require_all_details Whether to require all details to be input.
 * @return Tempcode Address fields.
 */
function get_shipping_address_fields($shipping_email, $shipping_phone, $shipping_firstname, $shipping_lastname, $shipping_street_address, $shipping_city, $shipping_county, $shipping_state, $shipping_post_code, $shipping_country, $require_all_details = true)
{
    $fields = new Tempcode();

    $fields->attach(get_shipping_name_fields($shipping_firstname, $shipping_lastname, $require_all_details));
    $fields->attach(get_address_fields('shipping_', $shipping_street_address, $shipping_city, $shipping_county, $shipping_state, $shipping_post_code, $shipping_country, $require_all_details));
    $fields->attach(get_shipping_contact_fields($shipping_email, $shipping_phone, $require_all_details));

    return $fields;
}

/**
 * Get form fields for a shipping/invoice address.
 *
 * @param  string $shipping_firstname First name.
 * @param  string $shipping_lastname Last name.
 * @param  boolean $require_all_details Whether to require all details to be input.
 * @return Tempcode Name fields.
 */
function get_shipping_name_fields($shipping_firstname, $shipping_lastname, $require_all_details = true)
{
    $fields = new Tempcode();

    $fields->attach(form_input_line(do_lang_cpf('firstname'), '', 'shipping_firstname', $shipping_firstname, $require_all_details));
    $fields->attach(form_input_line(do_lang_cpf('lastname'), '', 'shipping_lastname', $shipping_lastname, $require_all_details));

    return $fields;
}

/**
 * Get form fields for a shipping/invoice address.
 *
 * @param  string $shipping_email E-mail address.
 * @param  string $shipping_phone Phone number.
 * @param  boolean $require_all_details Whether to require all details to be input.
 * @return Tempcode Contact fields.
 */
function get_shipping_contact_fields($shipping_email, $shipping_phone, $require_all_details = true)
{
    $fields = new Tempcode();

    $fields->attach(form_input_email(do_lang_tempcode('EMAIL_ADDRESS'), '', 'shipping_email', $shipping_email, $require_all_details));
    $fields->attach(form_input_line(do_lang_tempcode('PHONE_NUMBER'), '', 'shipping_phone', $shipping_phone, $require_all_details));

    return $fields;
}

/**
 * Store shipping address for a transaction.
 * We try and merge it with one we already have on record in a sensible way.
 *
 * @param  ID_TEXT $trans_expecting_id Expected transaction ID.
 * @param  ID_TEXT $txn_id Transaction ID (blank: not set yet).
 * @param  ?array $shipping_address Shipping address (null: get from POST parameters).
 * @return ?AUTO_LINK Address ID (null: none saved).
 */
function store_shipping_address($trans_expecting_id, $txn_id = '', $shipping_address = null)
{
    $field_groups = array(
        array('a_firstname', 'a_lastname'),
        array('a_street_address', 'a_city', 'a_county', 'a_state', 'a_post_code', 'a_country'),
        array('a_email'),
        array('a_phone'),
    );

    if ($shipping_address === null) {
        $shipping_address = array();
        foreach ($field_groups as $field_group) {
            foreach ($field_group as $field) {
                $_field = substr($field, 2);
                $shipping_address[$field] = post_param_string('shipping_' . $_field, '');
            }
        }
        if (implode('', $shipping_address) == '') {
            return null;
        }
    }

    $existing = $GLOBALS['SITE_DB']->query_select('ecom_trans_addresses', array('*'), array('a_trans_expecting_id' => $trans_expecting_id), '', 1);
    if (array_key_exists(0, $existing)) {
        $e = $existing[0];

        foreach ($field_groups as $field_group) {
            $is_empty_new = true;
            foreach ($field_group as $field) {
                if ($shipping_address[$field] != '') {
                    $is_empty_new = false;
                    break;
                }
            }

            if ($is_empty_new) {
                $is_empty_existing = true;
                foreach ($field_group as $field) {
                    if ($e[$field] != '') {
                        $is_empty_existing = false;
                        break;
                    }
                }

                if (!$is_empty_existing) {
                    foreach ($field_group as $field) {
                        $shipping_address[$field] = $e[$field];
                    }
                }
            }
        }

        $GLOBALS['SITE_DB']->query_delete('ecom_trans_addresses', array('a_trans_expecting_id' => $trans_expecting_id), '', 1);
    }

    $more = array(
        'a_trans_expecting_id' => $trans_expecting_id,
        'a_txn_id' => $txn_id,
    );
    return $GLOBALS['SITE_DB']->query_insert('ecom_trans_addresses', $shipping_address + $more, true);
}
