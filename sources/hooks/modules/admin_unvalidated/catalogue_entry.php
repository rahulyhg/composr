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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_unvalidated_catalogue_entry
{
    /**
     * Find details on the unvalidated hook.
     *
     * @return ?array Map of hook info (null: hook is disabled)
     */
    public function info()
    {
        if (!addon_installed('catalogues')) {
            return null;
        }

        require_lang('catalogues');

        $info = array();
        $info['db_table'] = 'catalogue_entries';
        $info['db_identifier'] = 'id';
        $info['db_validated'] = 'ce_validated';
        $info['db_add_date'] = 'ce_add_date';
        $info['db_edit_date'] = 'ce_edit_date';
        $info['edit_module'] = 'cms_catalogues';
        $info['edit_type'] = '_edit_entry';
        $info['edit_identifier'] = 'id';
        $info['title'] = do_lang_tempcode('CATALOGUE_ENTRIES');

        return $info;
    }
}
