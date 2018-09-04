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
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__currency()
{
    if (!defined('CURRENCY_DISPLAY_RAW')) {
        define('CURRENCY_DISPLAY_RAW', 0); // Float
        define('CURRENCY_DISPLAY_STRING', 1); // Plain string
        define('CURRENCY_DISPLAY_LOCALE', 2); // Plain string
        define('CURRENCY_DISPLAY_WITH_CURRENCY_SIMPLEST', 3); // HTML
        define('CURRENCY_DISPLAY_WITH_CURRENCY_SIMPLIFIED', 4); // HTML
        define('CURRENCY_DISPLAY_WITH_CURRENCY_EXPLICIT', 5); // HTML
        define('CURRENCY_DISPLAY_TEMPLATED', 6); // HTML
    }
}

/**
 * Convert a country code to a currency code.
 *
 * @param  ID_TEXT $country The country code
 * @return ID_TEXT The currency code
 */
function country_to_currency($country)
{
    $map = get_currency_map();
    $currency = null;
    foreach ($map as $tmp_currency => $countries) {
        if (in_array($country, $countries)) {
            $currency = $tmp_currency;
            break;
        }
    }
    return $currency;
}

/**
 * Find the active ISO currency for the current user.
 *
 * @return string The active currency
 */
function get_currency()
{
    // Perform a preferential guessing sequence
    // ========================================

    // keep_currency
    $currency = get_param_string('keep_currency', null);
    if ($currency === null) {
        // a specially named Custom Profile Field for the currency.
        $currency = get_cms_cpf('currency');
        if ($currency === '' || $currency === 'CURRENCY') {
            $currency = null;
        }
        if ($currency === null) {
            require_code('locations');

            $country = get_country();
            if ($country === null) {
                $currency = get_option('currency');
            } else {
                $currency = country_to_currency($country);
                if ($currency === null) {
                    $currency = get_option('currency');
                }
            }
        }
    }

    return $currency;
}

/**
 * Perform a currency conversion to the visitor's currency, if automatic conversions are enabled -- otherwise just display in the site currency.
 * Not cache safe.
 *
 * @param  mixed $amount The starting amount (integer or float)
 * @param  ?ID_TEXT $from_currency The start currency code (null: site currency)
 * @param  integer $display_method A CURRENCY_DISPLAY_* constant
 * @return mixed The new amount with the specified display method (CURRENCY_DISPLAY_RAW is a float, otherwise a string)
 */
function currency_convert_wrap($amount, $from_currency = null, $display_method = 6)
{
    if ($from_currency === null) {
        $from_currency = get_option('currency');
    }
    $to_currency = (get_option('currency_auto') == '1') ? get_currency() : get_option('currency');

    return currency_convert($amount, $from_currency, $to_currency, $display_method);
}

/**
 * Perform a currency conversion.
 * Not cache safe.
 *
 * @param  mixed $amount The starting amount (integer or float)
 * @param  ?ID_TEXT $from_currency The start currency code (null: site currency)
 * @param  ?ID_TEXT $to_currency The end currency code (null: something appropriate for the user)
 * @param  integer $display_method A CURRENCY_DISPLAY_* constant
 * @param  ?ID_TEXT $force_via Force conversion via this API (null: no restriction)
 * @set conv_api
 * @return mixed The new amount with the specified display method (CURRENCY_DISPLAY_RAW is a float, otherwise a string)
 */
function currency_convert($amount, $from_currency = null, $to_currency = null, $display_method = 0, $force_via = null)
{
    if (is_integer($amount)) {
        $amount = floatval($amount);
    }

    if ($from_currency === null) {
        $from_currency = get_option('currency');
    }
    if ($to_currency === null) {
        $to_currency = get_currency();
    }

    $from_currency = strtoupper($from_currency);
    $to_currency = strtoupper($to_currency);

    $map = get_currency_map();

    // Check from currency
    $from_currency = strtoupper($from_currency);
    if (!array_key_exists($from_currency, $map)) {
        attach_message(do_lang_tempcode('UNKNOWN_CURRENCY', escape_html($from_currency)), 'warn', false, true);

        $from_currency = array_key_exists($to_currency, $map) ? $to_currency : 'USD';
    }

    $new_amount = null;

    // Case: No conversion needed
    if ($new_amount === null) {
        if ($from_currency == $to_currency) {
            $new_amount = $amount;
        }
    }

    // Prepare for cache usage
    $cache_key = 'currency_' . $from_currency . '_' . $to_currency . '_' . float_to_raw_string($amount);
    $cache_minutes = 60 * 24;
    $cache_cutoff = time() - 60 * $cache_minutes;
    $save_caching = false;

    // Case: Cached
    if ($new_amount === null) {
        $_new_amount = get_value_newer_than($cache_key, $cache_cutoff, true);
        $new_amount = ($_new_amount === null) ? null : floatval($_new_amount);
    }

    // Case: Get from "The Free Currency Converter API"
    if (($new_amount === null) && ($force_via === null) || ($force_via == 'conv_api')) {
        $new_amount = _currency_convert__currency_conv_api($amount, $from_currency, $to_currency);
        if ($new_amount !== null) {
            $save_caching = true;
        }
    }

    // Case: Fallback
    if ($new_amount === null) {
        require_lang('ecommerce');
        attach_message(do_lang_tempcode('CURRENCY_CONVERSION_FAILED', escape_html(float_format($amount)), escape_html($from_currency), escape_html($to_currency)), 'warn', false, true);

        $new_amount = $amount;
        $to_currency = $from_currency;
    }

    // Cache saving
    if ($save_caching) {
        $cleanup_sql = 'DELETE FROM ' . get_table_prefix() . 'values_elective WHERE the_name LIKE \'' . db_encode_like('currency\_%') . '\' AND date_and_time<' . strval($cache_cutoff);
        $GLOBALS['SITE_DB']->query($cleanup_sql);
        set_value($cache_key, float_to_raw_string($new_amount), true);
    }

    // Convert if needed
    switch ($display_method) {
        case CURRENCY_DISPLAY_STRING:
            return float_to_raw_string($new_amount);

        case CURRENCY_DISPLAY_LOCALE:
            return float_format($new_amount);

        case CURRENCY_DISPLAY_WITH_CURRENCY_SIMPLEST:
        case CURRENCY_DISPLAY_WITH_CURRENCY_SIMPLIFIED:
        case CURRENCY_DISPLAY_WITH_CURRENCY_EXPLICIT:
            list($symbol, $has_primacy) = get_currency_symbol($to_currency);
            $ret = $symbol;
            $ret .= escape_html(float_format($new_amount));
            if (($display_method != CURRENCY_DISPLAY_WITH_CURRENCY_SIMPLEST) && ((!$has_primacy) || ($display_method == CURRENCY_DISPLAY_WITH_CURRENCY_EXPLICIT))) {
                $ret .= '&nbsp;';
                $ret .= escape_html($to_currency);
            }
            return $ret;

        case CURRENCY_DISPLAY_TEMPLATED:
            $temp_tpl = do_template('CURRENCY', array(
                '_GUID' => '32f7e64b09569dd81c467ee4a369abed',
                'AMOUNT' => float_format($amount),
                'NEW_AMOUNT' => float_format($new_amount),
                'FROM_CURRENCY' => $from_currency,
                'TO_CURRENCY' => $to_currency,
            ));
            return $temp_tpl->evaluate();
    }

    return $new_amount;
}

/**
 * Perform a currency conversion using "The Free Currency Converter API".
 *
 * @param  mixed $amount The starting amount (integer or float)
 * @param  ID_TEXT $from_currency The start currency code
 * @param  ID_TEXT $to_currency The end currency code
 * @return ?float The new amount (null: could not look up)
 */
function _currency_convert__currency_conv_api($amount, $from_currency, $to_currency)
{
    $rate_key = urlencode($from_currency) . '_' . urlencode($to_currency);
    $cache_key = 'currency_' . $rate_key;

    $test = get_value($cache_key, null, true);
    if ($test !== null) {
        return floatval($test) * $amount;
    }

    $conv_api_url = 'https://free.currencyconverterapi.com/api/v5/convert?q=' . $rate_key . '&compact=y';
    $result = http_get_contents($conv_api_url, array('trigger_error' => false));
    if (is_string($result)) {
        $data = json_decode($result, true);
        if (isset($data[$rate_key]['val'])) {
            $rate = $data[$rate_key]['val'];

            set_value($cache_key, float_to_raw_string($rate, 10, false), true); // Will be de-cached in currency_convert

            return round((float)$rate * $amount, 2);
        }
    }
    return null;
}

/**
 * Get the symbol for a currency.
 *
 * @param  ID_TEXT $currency The currency
 * @return array A pair: The symbol, and whether the symbol is okay to use on its own (as it is the accepted default for the symbol)
 */
function get_currency_symbol($currency)
{
    static $cache = array();
    if (isset($cache[$currency])) {
        return $cache[$currency];
    }

    $symbols = array(
        'AFN' => '&#1547;',
        'AED' => '&#1583;.&#1573;',
        'ALL' => 'L',
        'AMD' => '&#1332;&#1408;&#1377;&#1396;',
        'ANG' => '&fnof;',
        'AOA' => 'Kz',
        'ARS' => '$',
        'AUD' => 'A$',
        'AWG' => '&fnof;',
        'AZN' => '&#1084;&#1072;&#1085;',
        'BAM' => 'KM',
        'BBD' => 'Bds$',
        'BDT' => '&#2547;',
        'BGN' => '&#1083;&#1074;',
        'BHD' => '.&#1583;.&#1576;',
        'BIF' => 'FBu',
        'BMD' => 'BD$',
        'BND' => '$',
        'BOB' => 'Bs.',
        'BRL' => 'R$',
        'BSD' => '$',
        'BTN' => 'Nu.',
        'BWP' => 'P',
        'BYR' => 'Br',
        'BZD' => 'BZ$',
        'CAD' => 'C$',
        'CDF' => 'FC',
        'CHF' => 'Fr',
        'CLP' => '$',
        'CNY' => '&yen;',
        'COP' => '$',
        'CRC' => '&#8353;',
        'CUC' => '&#8369;',
        'CVE' => 'Esc',
        'CZK' => 'K&#269;',
        'DJF' => 'Fdj',
        'DKK' => 'kr',
        'DOP' => 'RD$',
        'DZD' => '&#1583;.&#1580;',
        'EGP' => 'E&pound;',
        'ERN' => 'Nfa',
        'ETB' => 'Br',
        'EUR' => '&euro;',
        'FJD' => 'FJ$',
        'FKP' => 'FK&pound;',
        'GBP' => '&pound;',
        'GEL' => '&#4314;',
        'GHS' => '&#8373;',
        'GIP' => '&pound;',
        'GMD' => 'D',
        'GNF' => 'FG',
        'GTQ' => 'Q',
        'GYD' => 'GY$',
        'HKD' => 'HK$',
        'HNL' => 'L',
        'HRK' => 'kn',
        'HTG' => 'G',
        'HUF' => 'Ft',
        'IDR' => 'Rp',
        'ILS' => '&#8362;',
        'INR' => '&#8377;',
        'IQD' => '&#1593;.&#1583;',
        'IRR' => '&#65020;',
        'ISK' => 'kr',
        'JMD' => 'J$',
        'JOD' => '&#1583;.&#1575;',
        'JPY' => '&yen;',
        'KES' => 'KSh',
        'KGS' => '&#1083;&#1074;',
        'KHR' => '&#6107;',
        'KMF' => 'CF',
        'KPW' => '&#8361;',
        'KWD' => '&#1583;.&#1603;',
        'KYD' => 'KY$',
        'KZT' => '&#8376;',
        'LAK' => '&#8365;',
        'LBP' => 'L&pound;',
        'LKR' => '&#588;s',
        'LRD' => 'L$',
        'LSL' => 'L',
        'LTL' => 'Lt',
        'LVL' => 'Ls',
        'LYD' => '&#1604;.&#1583;',
        'MAD' => '&#1583;.&#1605;.',
        'MDL' => 'L',
        'MGA' => 'Ar',
        'MKD' => '&#1076;&#1077;&#1085;',
        'MMK' => 'K',
        'MNT' => '&#8366;',
        'MOP' => 'P',
        'MRO' => 'UM',
        'MUR' => '&#588;s',
        'MVR' => 'Rf',
        'MWK' => 'MK',
        'MXN' => '$',
        'MYR' => 'RM',
        'MZN' => 'MT',
        'NAD' => 'N$',
        'NGN' => '&#8358;',
        'NIO' => '$',
        'NOK' => 'kr',
        'NPR' => 'N&#588;s',
        'NZD' => 'NZ$',
        'OMR' => '&#1585;.&#1593;.',
        'PAB' => 'B/.',
        'PEN' => 'S/.',
        'PGK' => 'K',
        'PHP' => '&#8369;',
        'PKR' => '&#588;s',
        'PLN' => 'z&#322;',
        'PYG' => '&#8370;',
        'QAR' => '&#1585;.&#1602;',
        'RON' => 'L',
        'RSD' => '&#1044;&#1080;&#1085;.',
        'RUB' => '&#1088;&#1091;&#1073;',
        'RWF' => 'RF',
        'SAR' => '&#1585;.&#1587;',
        'SBD' => 'SI$',
        'SCR' => '&#588;s',
        'SDG' => '&pound;Sd',
        'SEK' => 'kr',
        'SGD' => 'S$',
        'SHP' => '&pound;',
        'SLL' => 'Le',
        'SOS' => 'So. Sh.',
        'SRD' => '$',
        'STD' => 'Db',
        'SVC' => '&#8353;',
        'SYP' => 'S&pound;',
        'SZL' => 'L',
        'THB' => '&#3647;',
        'TJS' => 'SM',
        'TMT' => 'm',
        'TND' => '&#1583;.&#1578;',
        'TOP' => 'T$',
        'TRY' => 'TL',
        'TTD' => '&#x0024;',
        'TWD' => 'NT$',
        'TZS' => 'TSh',
        'UAH' => '&#8372;',
        'UGX' => 'USh',
        'USD' => '$',
        'UYU' => '$U',
        'UZS' => '&#1083;&#1074;',
        'VEF' => 'Bs',
        'VND' => '&#8363;',
        'VUV' => 'VT',
        'WST' => 'T',
        'XAF' => 'CFA',
        'XCD' => 'EC$',
        'XOF' => 'CFA',
        'XPF' => 'F',
        'YER' => '&#65020;',
        'ZAR' => 'R',
        'ZMK' => 'ZK',
        'ZWL' => 'Z$',
    );

    $ret = '';
    if (isset($symbols[$currency])) {
        $ret .= $symbols[$currency];
    }

    // Maybe it has primacy as it owns (devised) the currency symbol even though other's now use it to
    $has_primacy = ($currency == 'USD' || $currency == 'GBP' || $currency == 'JPY');

    if (!$has_primacy) {
        // Maybe it has primacy as no other currency using the symbol?
        $counting = array_count_values($symbols);
        if (isset($counting[$symbols[$currency]])) {
            if ($counting[$symbols[$currency]] == 1) {
                $has_primacy = true;
            }
        }
    }

    $_ret = array($ret, $has_primacy);
    $cache[$currency] = $_ret;
    return $_ret;
}

/**
 * Get the currency map.
 *
 * @return array The currency map, currency code, to an array of country codes
 */
function get_currency_map()
{
    return array
    (
        'AED' => array
        (
            'AE',
        ),

        'AFA' => array
        (
            'AF',
        ),

        'ALL' => array
        (
            'AL',
        ),

        'AMD' => array
        (
            'AM',
        ),

        'ANG' => array
        (
            'AN',
        ),

        'AOK' => array
        (
            'AO',
        ),

        'AON' => array
        (
            'AO',
        ),

        'ARA' => array
        (
            'AR',
        ),

        'ARP' => array
        (
            'AR',
        ),

        'ARS' => array
        (
            'AR',
        ),

        'AUD' => array
        (
            'AU',
            'CX',
            'CC',
            'HM',
            'KI',
            'NR',
            'NF',
            'TV',
        ),

        'AWG' => array
        (
            'AW',
        ),

        'AZM' => array
        (
            'AZ',
        ),

        'BAM' => array
        (
            'BA',
        ),

        'BBD' => array
        (
            'BB',
        ),

        'BDT' => array
        (
            'BD',
        ),

        'BGL' => array
        (
            'BG',
        ),

        'BHD' => array
        (
            'BH',
        ),

        'BIF' => array
        (
            'BI',
        ),

        'BMD' => array
        (
            'BM',
        ),

        'BND' => array
        (
            'BN',
        ),

        'BOB' => array
        (
            'BO',
        ),

        'BOP' => array
        (
            'BO',
        ),

        'BRC' => array
        (
            'BR',
        ),

        'BRL' => array
        (
            'BR',
        ),

        'BRR' => array
        (
            'BR',
        ),

        'BSD' => array
        (
            'BS',
        ),

        'BTN' => array
        (
            'BT',
        ),

        'BWP' => array
        (
            'BW',
        ),

        'BYR' => array
        (
            'BY',
        ),

        'BZD' => array
        (
            'BZ',
        ),

        'CAD' => array
        (
            'CA',
        ),

        'CDZ' => array
        (
            'CD',
            'ZR',
        ),

        'CHF' => array
        (
            'LI',
            'CH',
        ),

        'CLF' => array
        (
            'CL',
        ),

        'CLP' => array
        (
            'CL',
        ),

        'CNY' => array
        (
            'CN',
        ),

        'COP' => array
        (
            'CO',
        ),

        'CRC' => array
        (
            'CR',
        ),

        'CSD' => array
        (
            'CS',
        ),

        'CUP' => array
        (
            'CU',
        ),

        'CVE' => array
        (
            'CV',
        ),

        'CYP' => array
        (
            'CY',
        ),

        'CZK' => array
        (
            'CZ',
        ),

        'DJF' => array
        (
            'DJ',
        ),

        'DKK' => array
        (
            'DK',
            'FO',
            'GL',
        ),

        'DOP' => array
        (
            'DO',
        ),

        'DZD' => array
        (
            'DZ',
        ),

        'EEK' => array
        (
            'EE',
        ),

        'EGP' => array
        (
            'EG',
        ),

        'ERN' => array
        (
            'ER',
        ),

        'ETB' => array
        (
            'ER',
            'ET',
        ),

        'EUR' => array
        (
            'AT',
            'BE',
            'FI',
            'FR',
            'DE',
            'GR',
            'IE',
            'IT',
            'LU',
            'NL',
            'PT',
            'ES',
            'AD',
            'MC',
            'CS',
            'VA',
            'SM',
        ),

        'FJD' => array
        (
            'FJ',
        ),

        'FKP' => array
        (
            'FK',
        ),

        'GBP' => array
        (
            'IO',
            'VG',
            'GS',
            'GB',
        ),

        'GEL' => array
        (
            'GE',
        ),

        'GHC' => array
        (
            'GH',
        ),

        'GIP' => array
        (
            'GI',
        ),

        'GMD' => array
        (
            'GM',
        ),

        'GNS' => array
        (
            'GN',
        ),

        'GQE' => array
        (
            'GQ',
        ),

        'GTQ' => array
        (
            'GT',
        ),

        'GWP' => array
        (
            'GW',
        ),

        'GYD' => array
        (
            'GY',
        ),

        'HKD' => array
        (
            'HK',
        ),

        'HNL' => array
        (
            'HN',
        ),

        'HRD' => array
        (
            'HR',
        ),

        'HRK' => array
        (
            'HR',
        ),

        'HTG' => array
        (
            'HT',
        ),

        'HUF' => array
        (
            'HU',
        ),

        'IDR' => array
        (
            'ID',
        ),

        'ILS' => array
        (
            'IL',
        ),

        'INR' => array
        (
            'BT',
            'IN',
        ),

        'IQD' => array
        (
            'IQ',
        ),

        'IRR' => array
        (
            'IR',
        ),

        'ISK' => array
        (
            'IS',
        ),

        'JMD' => array
        (
            'JM',
        ),

        'JOD' => array
        (
            'JO',
        ),

        'JPY' => array
        (
            'JP',
        ),

        'KES' => array
        (
            'KE',
        ),

        'KGS' => array
        (
            'KG',
        ),

        'KHR' => array
        (
            'KH',
        ),

        'KMF' => array
        (
            'KM',
        ),

        'KPW' => array
        (
            'KP',
        ),

        'KRW' => array
        (
            'KR',
        ),

        'KWD' => array
        (
            'KW',
        ),

        'KYD' => array
        (
            'KY',
        ),

        'KZT' => array
        (
            'KZ',
        ),

        'LAK' => array
        (
            'LA',
        ),

        'LBP' => array
        (
            'LB',
        ),

        'LKR' => array
        (
            'LK',
        ),

        'LRD' => array
        (
            'LR',
        ),

        'LSL' => array
        (
            'LS',
        ),

        'LSM' => array
        (
            'LS',
        ),

        'LTL' => array
        (
            'LT',
        ),

        'LVL' => array
        (
            'LA',
        ),

        'LYD' => array
        (
            'LY',
        ),

        'MAD' => array
        (
            'MA',
            'EH',
        ),

        'MDL' => array
        (
            'MD',
        ),

        'MGF' => array
        (
            'MG',
        ),

        'MKD' => array
        (
            'MK',
        ),

        'MLF' => array
        (
            'ML',
        ),

        'MMK' => array
        (
            'MM',
            'BU',
        ),

        'MNT' => array
        (
            'MN',
        ),

        'MOP' => array
        (
            'MO',
        ),

        'MRO' => array
        (
            'MR',
            'EH',
        ),

        'MTL' => array
        (
            'MT',
        ),

        'MUR' => array
        (
            'MU',
        ),

        'MVR' => array
        (
            'MV',
        ),

        'MWK' => array
        (
            'MW',
        ),

        'MXN' => array
        (
            'MX',
        ),

        'MYR' => array
        (
            'MY',
        ),

        'MZM' => array
        (
            'MZ',
        ),

        'NAD' => array
        (
            'NA',
        ),

        'NGN' => array
        (
            'NG',
        ),

        'NIC' => array
        (
            'NI',
        ),

        'NOK' => array
        (
            'AQ',
            'BV',
            'NO',
            'SJ',
        ),

        'NPR' => array
        (
            'NP',
        ),

        'NZD' => array
        (
            'CK',
            'NZ',
            'NU',
            'PN',
            'TK',
        ),

        'OMR' => array
        (
            'OM',
        ),

        'PAB' => array
        (
            'PA',
        ),

        'PEI' => array
        (
            'PE',
        ),

        'PEN' => array
        (
            'PE',
        ),

        'PGK' => array
        (
            'PG',
        ),

        'PHP' => array
        (
            'PH',
        ),

        'PKR' => array
        (
            'PK',
        ),

        'PLN' => array
        (
            'PL',
        ),

        'PYG' => array
        (
            'PY',
        ),

        'QAR' => array
        (
            'QA',
        ),

        'ROL' => array
        (
            'RO',
        ),

        'RUB' => array
        (
            'RU',
        ),

        'RWF' => array
        (
            'RW',
        ),

        'SAR' => array
        (
            'SA',
        ),

        'SBD' => array
        (
            'SB',
        ),

        'SCR' => array
        (
            'IO',
            'SC',
        ),

        'SDD' => array
        (
            'SD',
        ),

        'SDP' => array
        (
            'SD',
        ),

        'SEK' => array
        (
            'SE',
        ),

        'SGD' => array
        (
            'SG',
        ),

        'SHP' => array
        (
            'SH',
        ),

        'SIT' => array
        (
            'SI',
        ),

        'SKK' => array
        (
            'SK',
        ),

        'SLL' => array
        (
            'SL',
        ),

        'SOS' => array
        (
            'SO',
        ),

        'SRG' => array
        (
            'SR',
        ),

        'STD' => array
        (
            'ST',
        ),

        'SUR' => array
        (
            'SU',
        ),

        'SVC' => array
        (
            'SV',
        ),

        'SYP' => array
        (
            'SY',
        ),

        'SZL' => array
        (
            'SZ',
        ),

        'THB' => array
        (
            'TH',
        ),

        'TJR' => array
        (
            'TJ',
        ),

        'TMM' => array
        (
            'TM',
        ),

        'TND' => array
        (
            'TN',
        ),

        'TOP' => array
        (
            'TO',
        ),

        'TPE' => array
        (
            'TP',
        ),

        'TRL' => array
        (
            'TR',
        ),

        'TTD' => array
        (
            'TT',
        ),

        'TWD' => array
        (
            'TW',
        ),

        'TZS' => array
        (
            'TZ',
        ),

        'UAH' => array
        (
            'UA',
        ),

        'UAK' => array
        (
            'UA',
        ),

        'UGS' => array
        (
            'UG',
        ),

        'USD' => array
        (
            'AS',
            'VG',
            'EC',
            'FM',
            'GU',
            'MH',
            'MP',
            'PW',
            'PA',
            'PR',
            'TC',
            'US',
            'UM',
            'VI',
        ),

        'UYU' => array
        (
            'UY',
        ),

        'UZS' => array
        (
            'UZ',
        ),

        'VEB' => array
        (
            'VE',
        ),

        'VND' => array
        (
            'VN',
        ),

        'VUV' => array
        (
            'VU',
        ),

        'WST' => array
        (
            'WS',
        ),

        'XAF' => array
        (
            'BJ',
            'BF',
            'CM',
            'CF',
            'TD',
            'CG',
            'CI',
            'GQ',
            'GA',
            'GW',
            'ML',
            'NE',
            'SN',
            'TG',
        ),

        'XCD' => array
        (
            'AI',
            'AG',
            'VG',
            'DM',
            'GD',
            'MS',
            'KN',
            'LC',
            'VC',
        ),

        'XOF' => array
        (
            'NE',
            'SN',
        ),

        'XPF' => array
        (
            'PF',
            'NC',
            'WF',
        ),

        'YDD' => array
        (
            'YD',
        ),

        'YER' => array
        (
            'YE',
        ),

        'ZAL' => array
        (
            'ZA',
        ),

        'ZAR' => array
        (
            'LS',
            'NA',
            'ZA',
        ),

        'ZMK' => array
        (
            'ZM',
        ),

        'ZWD' => array
        (
            'ZW',
        ),
    );
}
