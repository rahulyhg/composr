<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_page_groupings_catalogues
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
        if (!addon_installed('catalogues')) {
            return array();
        }

        $exhaustive = true;

        if (is_null($member_id)) {
            $member_id = get_member();
        }

        $ret = array();
        if (has_privilege($member_id, 'submit_cat_highrange_content', 'cms_catalogues')) {
            $ret[] = array('cms', 'menu/rich_content/catalogues/catalogues', array('cms_catalogues', array('type' => 'browse'), get_module_zone('cms_catalogues')), do_lang_tempcode('ITEMS_HERE', do_lang_tempcode('catalogues:CATALOGUES'), make_string_tempcode(escape_html(integer_format($GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'COUNT(*)', null, '', true))))), 'catalogues:DOC_CATALOGUES');
        }
        if ($exhaustive) {
            $catalogues = $GLOBALS['SITE_DB']->query_select('catalogues', array('c_name', 'c_title', 'c_description', 'c_ecommerce', 'c_is_tree'), null, 'ORDER BY c_add_date', 50, null, true);
            if (!is_null($catalogues)) {
                $ret2 = array();
                $count = 0;

                foreach ($catalogues as $row) {
                    if (substr($row['c_name'], 0, 1) == '_') {
                        continue;
                    }

                    if (($row['c_ecommerce'] == 0) || (addon_installed('shopping'))) {
                        $menu_icon = 'menu/rich_content/catalogues/' . $row['c_name'];
                        if (find_theme_image('icons/24x24/' . $menu_icon, true) == '') {
                            $menu_icon = 'menu/rich_content/catalogues/catalogues';
                        }

                        if (has_submit_permission('mid', $member_id, get_ip_address(), 'cms_catalogues', array('catalogues_catalogue', $row['c_name']))) {
                            if ($count < 10) {
                                $ret2[] = array('cms', $menu_icon, array('cms_catalogues', array('type' => 'browse', 'catalogue_name' => $row['c_name']), get_module_zone('cms_catalogues')), do_lang_tempcode('ITEMS_HERE', escape_html(get_translated_text($row['c_title'])), escape_html(integer_format($GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'COUNT(*)', array('c_name' => $row['c_name']), '', true)))), get_translated_tempcode('catalogues', $row, 'c_description'));
                            }
                            $count++;
                        }

                        $page_grouping = 'rich_content';
                        if ($row['c_name'] == 'projects') {
                            $page_grouping = ((addon_installed('collaboration_zone') && has_zone_access($member_id, 'collaboration')) ? 'collaboration' : 'rich_content');
                        }
                        if ($row['c_name'] == 'classifieds') {
                            $page_grouping = 'social';
                        }

                        if ($row['c_is_tree'] == 0) {
                            $num_categories = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'COUNT(*)', array('c_name' => $row['c_name']));
                            /*if ($num_categories==0) { // Actually we should show an empty index - catalogue exists, show it does
                                continue;
                            }
                            else*/
                            if ($num_categories == 1) {
                                $only_category = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'id', array('c_name' => $row['c_name']));
                                $ret2[] = array($page_grouping, $menu_icon, array('catalogues', array('type' => 'browse', 'id' => strval($only_category)), get_module_zone('catalogues')), make_string_tempcode(escape_html(get_translated_text($row['c_title']))), get_translated_tempcode('catalogues', $row, 'c_description'));
                                continue;
                            }
                        }

                        $ret2[] = array($page_grouping, $menu_icon, array('catalogues', array('type' => 'index', 'id' => $row['c_name']), get_module_zone('catalogues')), make_string_tempcode(escape_html(get_translated_text($row['c_title']))), get_translated_tempcode('catalogues', $row, 'c_description'));
                    }
                }

                $ret = array_merge($ret, $ret2);
            }
        }

        //$ret[]=array('rich_content','menu/rich_content/catalogues/catalogues',array('catalogues',array(),get_module_zone('catalogues')),do_lang_tempcode('catalogues:CATALOGUES'));  Lame

        return $ret;
    }
}
