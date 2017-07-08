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
 * Hook class.
 */
class Hook_ecommerce_gambling
{
    /**
     * Get the overall categorisation for the products handled by this eCommerce hook.
     *
     * @return ?array A map of product categorisation details (null: disabled).
     */
    public function get_product_category()
    {
        return array(
            'category_name' => do_lang('GAMBLING'),
            'category_description' => do_lang_tempcode('GAMBLING_DESCRIPTION'),
            'category_image_url' => find_theme_image('icons/48x48/menu/_generic_spare/features'),
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
        if (!addon_installed('points')) {
            return array();
        }

        $products = array();

        $min = intval(get_option('minimum_gamble_amount'));
        $max = intval(get_option('maximum_gamble_amount'));
        if ($max > $min) {
            $_min = $min;
            $min = $max;
            $max = $_min;
        }
        $spread = intval(round(floatval($max - $min) / 4.0));
        $amounts = array();
        $amounts[] = $min;
        $amounts[] = $min + $spread * 1;
        $amounts[] = $min + $spread * 2;
        $amounts[] = $min + $spread * 3;
        $amounts[] = $max;
        $amounts = array_unique($amounts);

        foreach ($amounts as $amount) {
            $products['GAMBLING_' . strval($amount)] = array(
                'item_name' => do_lang('GAMBLE_THIS', integer_format($amount)),
                'item_description' => new Tempcode(),
                'item_image_url' => '',

                'type' => PRODUCT_PURCHASE,
                'type_special_details' => array(),

                'price' => null,
                'currency' => get_option('currency'),
                'price_points' => $amount,
                'discount_points__num_points' => null,
                'discount_points__price_reduction' => null,

                'tax_code' => '0.0',
                'shipping_cost' => 0.00,
                'product_weight' => null,
                'product_length' => null,
                'product_width' => null,
                'product_height' => null,
                'needs_shipping_address' => false,
            );
        }

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
        if (!addon_installed('points')) {
            return ECOMMERCE_PRODUCT_DISABLED;
        }

        if (get_option('is_on_gambling_buy') == '0') {
            return ECOMMERCE_PRODUCT_DISABLED;
        }

        if (is_guest($member_id)) {
            return ECOMMERCE_PRODUCT_NO_GUESTS;
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
        return do_lang_tempcode('GAMBLE_WARNING');
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
        return null;
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
        if ($details['STATUS'] != 'Completed') {
            return false;
        }

        $amount = intval(preg_replace('#^GAMBLING_#', '', $type_code));

        $member_id = intval($purchase_id);

        // Calculate
        $average_gamble_multiplier = floatval(get_option('average_gamble_multiplier')) / 100.0;
        $maximum_gamble_multiplier = floatval(get_option('maximum_gamble_multiplier')) / 100.0;
        $above_average = (mt_rand(0, 10) < 5);
        if ($above_average) {
            //$winnings = round($average_gamble_multiplier * $amount + mt_rand(0, round($maximum_gamble_multiplier * $amount - $average_gamble_multiplier * $amount)));   Even distribution is NOT wise
            $peak = $maximum_gamble_multiplier * $amount;
            $under = 0.0;
            $number = intval(round($average_gamble_multiplier * $amount + mt_rand(0, intval(round($maximum_gamble_multiplier * $amount - $average_gamble_multiplier * $amount)))));
            for ($x = 1; $x < intval($peak); $x++) { // Perform some discrete calculus: we need to find when we've reached the proportional probability area equivalent to our number
                $p = $peak * (1.0 / pow(floatval($x) + 0.4, 2.0) - (1.0 / pow($maximum_gamble_multiplier * floatval($amount), 2.0))); // Using a 1/x^2 curve. 0.4 is a bit of a magic number to get the averaging right
                $under += $p;
                if ($under > floatval($number)) {
                    break;
                }
            }
            $winnings = intval(round($average_gamble_multiplier * $amount + $x * 1.1)); // 1.1 is a magic number to make it seem a bit fairer
        } else {
            $winnings = mt_rand(0, intval(round($average_gamble_multiplier * $amount)));
        }

        $GLOBALS['SITE_DB']->query_insert('ecom_sales', array('date_and_time' => time(), 'member_id' => $member_id, 'details' => do_lang('GAMBLING', null, null, null, get_site_default_lang()), 'details2' => strval($amount) . ' --> ' . strval($winnings), 'txn_id' => $details['TXN_ID']));

        // Actuate
        require_code('points2');
        give_points($winnings, $member_id, $GLOBALS['FORUM_DRIVER']->get_guest_id(), do_lang('GAMBLING_WINNINGS'), false, false);

        // Show an instant message so the member knows how it worked out (plus buying via points, so will definitely be seen)
        if ($winnings > $amount) {
            $result = do_lang_tempcode('GAMBLE_CONGRATULATIONS', escape_html(integer_format($winnings - $amount)), escape_html(integer_format($amount)));
        } else {
            $result = do_lang_tempcode('GAMBLE_COMMISERATIONS', escape_html(integer_format($amount - $winnings)), escape_html(integer_format($amount)));
        }
        global $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE;
        $ECOMMERCE_SPECIAL_SUCCESS_MESSAGE = $result;

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
        return intval($purchase_id);
    }
}
