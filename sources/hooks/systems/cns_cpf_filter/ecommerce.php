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
 * Hook class.
 */
class Hook_cns_cpf_filter_ecommerce
{
    /**
     * Find which special CPFs to enable.
     *
     * @return array A list of CPFs to enable
     */
    public function to_enable()
    {
        if (!addon_installed('ecommerce')) {
            return array();
        }

        $cpf = array();

        // General payment details
        // Not configurable per-member yet
        //    $cpf = array_merge($cpf, array('currency' => true,));

        // Local payment (we only store these locally if doing local payment, if we've gone through PCI checks)
        if ((get_option('use_local_payment') == '1') && (get_option('store_credit_card_numbers') == '1')) {
            // Payment details
            $cpf = array_merge($cpf, array('payment_cardholder_name' => true, 'payment_card_type' => true, /*'payment_card_number' => true PCI stops us storing this without a lot of extra work, */'payment_card_start_date' => true, 'payment_card_expiry_date' => true, 'payment_card_issue_number' => true));
            $cpf = array_merge($cpf, array('billing_street_address' => true, 'billing_city' => true, 'billing_post_code' => true, 'billing_country' => true, 'billing_mobile_phone_number' => true));
            if (get_option('cpf_enable_county') == '1') {
                $cpf = array_merge($cpf, array('billing_county' => true));
            }
            if (get_option('cpf_enable_state') == '1') {
                $cpf = array_merge($cpf, array('billing_state' => true));
            }
        }

        return $cpf;
    }
}
