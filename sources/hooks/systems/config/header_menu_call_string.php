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
 * @package    core_configuration
 */

/**
 * Hook class.
 */
class Hook_config_header_menu_call_string
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'HEADER_MENU_CALL_STRING',
            'type' => 'line',
            'category' => 'THEME',
            'group' => 'BLOCKS_AT_TOP',
            'explanation' => 'CONFIG_OPTION_header_menu_call_string',
            'shared_hosting_restricted' => '0',
            'list_options' => '',

            'addon' => 'core_configuration',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return 'site:' . DEFAULT_ZONE_PAGE_NAME . ',include=node,title=' . do_lang('HOME') . ',icon=menu/home + site:,use_page_groupings=1,max_recurse_depth=4,child_cutoff=15,collapse_zones=1';
    }
}
