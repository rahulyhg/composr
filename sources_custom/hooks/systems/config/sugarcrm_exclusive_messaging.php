<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    sugarcrm
 */

/**
 * Hook class.
 */
class Hook_config_sugarcrm_exclusive_messaging
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'SUGARCRM_EXCLUSIVE_MESSAGING',
            'type' => 'tick',
            'category' => 'COMPOSR_APIS',
            'group' => 'SUGARCRM',
            'explanation' => 'CONFIG_OPTION_sugarcrm_exclusive_messaging',
            'shared_hosting_restricted' => '0',
            'list_options' => '',
            'order_in_category_group' => 4,

            'addon' => 'sugarcrm',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return '0';
    }
}
