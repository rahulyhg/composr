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
 * @package    welcome_emails
 */

/**
 * Hook class.
 */
class Hook_actionlog_welcome_emails extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    protected function get_handlers()
    {
        if (!addon_installed('welcome_emails')) {
            return array();
        }

        require_lang('cns_welcome_emails');

        return array(
            'ADD_WELCOME_EMAIL' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'welcome_email',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_WELCOME_EMAIL' => '_SEARCH:admin_cns_welcome_emails:_edit:{ID}',
                    'ADD_WELCOME_EMAIL' => '_SEARCH:admin_cns_welcome_emails:add',
                ),
            ),
            'EDIT_WELCOME_EMAIL' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'welcome_email',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_WELCOME_EMAIL' => '_SEARCH:admin_cns_welcome_emails:_edit:{ID}',
                    'ADD_WELCOME_EMAIL' => '_SEARCH:admin_cns_welcome_emails:add',
                ),
            ),
            'DELETE_WELCOME_EMAIL' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'welcome_email',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_WELCOME_EMAIL' => '_SEARCH:admin_cns_welcome_emails:add',
                ),
            ),
        );
    }
}