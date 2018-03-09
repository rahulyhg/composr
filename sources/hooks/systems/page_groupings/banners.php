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
 * @package    banners
 */

/**
 * Hook class.
 */
class Hook_page_groupings_banners
{
    /**
     * Run function for do_next_menu hooks. They find links to put on standard navigation menus of the system.
     *
     * @param  ?MEMBER $member_id Member ID to run as (null: current member)
     * @param  boolean $extensive_docs Whether to use extensive documentation tooltips, rather than short summaries
     * @return array List of tuple of links (page grouping, icon, do-next-style linking data), label, help (optional) and/or nulls
     */
    public function run($member_id = null, $extensive_docs = false)
    {
        if (!addon_installed('banners')) {
            return array();
        }

        require_lang('banners');

        return array(
            array('cms', 'menu/cms/banners', array('cms_banners', array('type' => 'browse'), get_module_zone('cms_banners')), do_lang_tempcode('ITEMS_HERE', do_lang_tempcode('banners:BANNERS'), make_string_tempcode(escape_html(integer_format(@intval($GLOBALS['SITE_DB']->query_select_value('banners', 'COUNT(*)', array(), '', true)))))), 'banners:DOC_BANNERS'),
            array('audit', 'menu/cms/banners', array('admin_banners', array('type' => 'browse'), get_module_zone('admin_banners')), do_lang_tempcode('banners:BANNER_STATISTICS'), 'banners:DOC_BANNERS'),
            array('site_meta', 'menu/cms/banners', array('banners', array('type' => 'browse'), get_module_zone('banners')), do_lang_tempcode('banners:BANNERS')),
        );
    }
}
