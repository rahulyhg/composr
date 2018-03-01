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
 * @package    downloads
 */

/**
 * Hook class.
 */
class Hook_page_groupings_downloads
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
        if (!addon_installed('downloads')) {
            return array();
        }

        return array(
            array('cms', 'menu/rich_content/downloads', array('cms_downloads', array('type' => 'browse'), get_module_zone('cms_downloads')), do_lang_tempcode('ITEMS_HERE', do_lang_tempcode('downloads:SECTION_DOWNLOADS'), make_string_tempcode(escape_html(integer_format(@intval($GLOBALS['SITE_DB']->query_select_value('download_downloads', 'COUNT(*)', array(), '', true)))))), 'downloads:DOC_DOWNLOADS'),
            array('rich_content', 'menu/rich_content/downloads', array('downloads', array(), get_module_zone('downloads')), do_lang_tempcode('downloads:SECTION_DOWNLOADS')),
        );
    }
}
