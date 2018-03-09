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
 * Hook class.
 */
class Hook_symbol_STOCK_CHECK
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        if (!addon_installed('shopping')) {
            return '';
        }

        $value = '';

        if (array_key_exists(0, $param)) {
            $type_code = $param[0];

            require_code('ecommerce');

            list(, $product_object) = find_product_details($type_code);
            if (method_exists($product_object, 'get_available_quantity')) {
                $available_quantity = $product_object->get_available_quantity($type_code);
            } else {
                $available_quantity = 1;
            }
            $value = ($available_quantity === null) ? '' : strval($available_quantity);
        }

        return $value;
    }
}
