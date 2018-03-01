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
 * @package    core_webstandards
 */

/**
 * Hook class.
 */
class Hook_config_webstandards_javascript
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'WEBSTANDARDS_JAVASCRIPT',
            'type' => 'tick',
            'category' => 'ACCESSIBILITY',
            'group' => 'WEBSTANDARDS',
            'explanation' => 'CONFIG_OPTION_webstandards_javascript',
            'shared_hosting_restricted' => '1',
            'list_options' => '',
            'required' => true,

            'public' => false,

            'addon' => 'core_webstandards',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return null; // Way too slow, and unlikely to be accurate with quickly-evolving JS standards
    }
}
