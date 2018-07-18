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
 * @package    authors
 */

/**
 * Hook class.
 */
class Hook_actionlog_authors
{
    /**
     * Get details of actionlog entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('authors')) {
            return array();
        }

        require_lang('authors');

        return array(
            'DEFINE_AUTHOR' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'author',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'VIEW_AUTHOR' => 'TODO',
                    'DEFINE_AUTHOR' => 'TODO',
                    'ADD_AUTHOR' => 'TODO',
                ),
            ),
            'DELETE_AUTHOR' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'author',
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'ADD_AUTHOR' => 'TODO',
                ),
            ),
            'MERGE_AUTHORS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'author',
                'identifier_index' => 1,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW_AUTHOR' => 'TODO',
                    'MERGE_AUTHORS' => 'TODO',
                ),
            ),
        );
    }
}
