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
 * @package    iotds
 */

/**
 * Hook class.
 */
class Hook_actionlog_iotds extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    protected function get_handlers()
    {
        if (!addon_installed('iotds')) {
            return array();
        }

        require_lang('iotds');

        return array(
            'ADD_IOTD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'iotd',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:iotds:view:{ID}',
                    'EDIT_THIS_IOTD' => '_SEARCH:cms_iotds:_edit:{ID}',
                    'ADD_IOTD' => '_SEARCH:cms_iotds:add',
                ),
            ),
            'EDIT_IOTD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'iotd',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:iotds:view:{ID}',
                    'EDIT_THIS_IOTD' => '_SEARCH:cms_iotds:_edit:{ID}',
                    'ADD_IOTD' => '_SEARCH:cms_iotds:add',
                ),
            ),
            'CHOOSE_IOTD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'iotd',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW' => '_SEARCH:iotds:view:{ID}',
                    'EDIT_THIS_IOTD' => '_SEARCH:cms_iotds:_edit:{ID}',
                    'ADD_IOTD' => '_SEARCH:cms_iotds:add',
                ),
            ),
            'DELETE_IOTD' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'iotd',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_IOTD' => '_SEARCH:cms_iotds:add',
                ),
            ),
        );
    }
}
