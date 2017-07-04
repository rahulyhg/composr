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
 * @package    themewizard
 */

/**
 * Hook class.
 */
class Hook_page_groupings_themewizard
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
        return array(
            //array('style', 'menu/adminzone/style/themes/themewizard', array('admin_themewizard', array('type' => 'browse'), get_module_zone('admin_themewizard')), do_lang_tempcode('themes:THEMEWIZARD'), 'themes:DOC_THEMEWIZARD'),
            array('style', 'menu/adminzone/style/themes/logowizard', array('admin_themewizard', array('type' => 'make_logo'), get_module_zone('admin_themewizard')), do_lang_tempcode('themes:LOGOWIZARD'), 'themes:DOC_LOGOWIZARD'),
        );
    }
}
