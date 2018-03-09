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
 * @package    calendar
 */

/**
 * Hook class.
 */
class Hook_unvalidated_calendar
{
    /**
     * Find details on the unvalidated hook.
     *
     * @return ?array Map of hook info (null: hook is disabled)
     */
    public function info()
    {
        if (!addon_installed('calendar')) {
            return null;
        }

        require_lang('calendar');

        $info = array();
        $info['db_table'] = 'calendar_events';
        $info['db_identifier'] = 'id';
        $info['db_validated'] = 'validated';
        $info['db_add_date'] = 'e_add_date';
        $info['db_edit_date'] = 'e_edit_date';
        $info['edit_module'] = 'cms_calendar';
        $info['edit_type'] = 'edit';
        $info['edit_identifier'] = 'id';
        $info['title'] = do_lang_tempcode('EVENTS');

        return $info;
    }
}
