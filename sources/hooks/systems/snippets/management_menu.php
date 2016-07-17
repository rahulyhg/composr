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
 * @package    core_menus
 */

/**
 * Hook class.
 */
class Hook_snippet_management_menu
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return Tempcode The snippet
     */
    public function run()
    {
        if (has_zone_access(get_member(), 'adminzone')) {
            require_code('menus');
            return build_menu('popup', 'adminzone:' . DEFAULT_ZONE_PAGE_NAME . ',include=node,title=' . do_lang('menus:DASHBOARD') . ',icon=menu/adminzone/start + adminzone:,include=children,max_recurse_depth=4,use_page_groupings=1 + cms:,include=node,max_recurse_depth=3,use_page_groupings=1');
        }
        return new Tempcode();
    }
}
