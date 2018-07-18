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
 * @package    downloads
 */

/**
 * Hook class.
 */
class Hook_actionlog_downloads
{
    /**
     * Get details of actionlog entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('downloads')) {
            return array();
        }

        require_lang('downloads');

        return array(
            'ADD_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_DOWNLOAD_CATEGORY' => 'TODO',
                    'ADD_DOWNLOAD_CATEGORY' => 'TODO',
                    'ADD_DOWNLOAD' => 'TODO',
                ),
            ),
            'EDIT_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_DOWNLOAD_CATEGORY' => 'TODO',
                    'ADD_DOWNLOAD_CATEGORY' => 'TODO',
                    'ADD_DOWNLOAD' => 'TODO',
                ),
            ),
            'DELETE_DOWNLOAD_CATEGORY' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_category',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD_CATEGORY' => 'TODO',
                ),
            ),
            'ADD_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_DOWNLOAD_LICENCE' => 'TODO',
                    'ADD_DOWNLOAD_LICENCE' => 'TODO',
                ),
            ),
            'EDIT_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_DOWNLOAD_LICENCE' => 'TODO',
                    'ADD_DOWNLOAD_LICENCE' => 'TODO',
                ),
            ),
            'DELETE_DOWNLOAD_LICENCE' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download_licence',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD_LICENCE' => 'TODO',
                ),
            ),
            'ADD_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_DOWNLOAD' => 'TODO',
                    'ADD_DOWNLOAD' => 'TODO',
                ),
            ),
            'EDIT_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => 'TODO',
                    'EDIT_THIS_DOWNLOAD' => 'TODO',
                    'ADD_DOWNLOAD' => 'TODO',
                ),
            ),
            'DELETE_DOWNLOAD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'download',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_DOWNLOAD' => 'TODO',
                ),
            ),
            'FILESYSTEM_DOWNLOADS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DOWNLOADS_HOME' => 'TODO',
                ),
            ),
            'FTP_DOWNLOADS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'DOWNLOADS_HOME' => 'TODO',
                ),
            ),
        );
    }
}
