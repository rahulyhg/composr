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
 * @package    core_cleanup_tools
 */

/**
 * Hook class.
 */
class Hook_cleanup_temp
{
    /**
     * Find details about this cleanup hook.
     *
     * @return ?array Map of cleanup hook info (null: hook is disabled)
     */
    public function info()
    {
        $info = array();
        $info['title'] = do_lang_tempcode('TEMP');
        $info['description'] = do_lang_tempcode('DESCRIPTION_TEMP');
        $info['type'] = 'cache';

        return $info;
    }

    /**
     * Run the cleanup hook action.
     *
     * @return Tempcode Results
     */
    public function run()
    {
        $tables = array(
            'incoming_uploads',
            'captchas',
            'cron_caching_requests',
            'post_tokens',
            'edit_pings',
            'autosave',
            'messages_to_render',
            'temp_block_permissions',
            'webstandards_checked_once',
        );
        foreach ($tables as $table) {
            $GLOBALS['SITE_DB']->query_delete($table);
        }

        $subdirs = array(
            'uploads/incoming',
            'uploads/captcha',
            'temp',
        );
        foreach ($subdirs as $subdir) {
            $full = get_custom_file_base() . '/' . $subdir;
            $dh = @opendir($full);
            if ($dh !== false) {
                while (($file = readdir($dh)) !== false) {
                    if (!in_array($file, array('index.html', '.htaccess'))) {
                        @unlink($full . '/' . $file);
                    }
                }
                closedir($dh);
            }
        }

        return new Tempcode();
    }
}