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
 * @package    points
 */

/**
 * Hook class.
 */
class Hook_checklist_points
{
    /**
     * Find items to include on the staff checklist.
     *
     * @return array An array of tuples: The task row to show, the number of seconds until it is due (or null if not on a timer), the number of things to sort out (or null if not on a queue), The name of the config option that controls the schedule (or null if no option).
     */
    public function run()
    {
        // Monitor gift points
        if (addon_installed('points')) {
            require_lang('points');

            $url = build_url(array('page' => 'admin_points', 'type' => 'browse'), 'adminzone');
            $status = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM_STATUS_NA');
            $tpl = do_template('BLOCK_MAIN_STAFF_CHECKLIST_ITEM', array('_GUID' => 'f421d75a70956d3beddf16c3f8138f26', 'URL' => '', 'STATUS' => $status, 'TASK' => do_lang_tempcode('NAG_MONITOR_GIFTS', escape_html_tempcode($url)), 'INFO' => ''));
            return array(array($tpl, null, null, null));
        }
        return array();
    }
}
