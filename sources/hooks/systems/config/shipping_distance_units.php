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
class Hook_config_shipping_distance_units
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'SHIPPING_DISTANCE_UNITS',
            'type' => 'list',
            'category' => 'ECOMMERCE',
            'group' => 'SHIPPING',
            'explanation' => 'CONFIG_OPTION_shipping_distance_units',
            'shared_hosting_restricted' => '0',
            'list_options' => 'Cm|In',
            'order_in_category_group' => 5,
            'required' => true,

            'public' => false,

            'addon' => 'ecommerce',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        require_code('locations');
        if (geolocate_ip() == 'US') {
            return 'In';
        }
        return 'Cm';
    }
}
