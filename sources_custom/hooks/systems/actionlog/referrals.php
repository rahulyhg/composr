<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    referrals
 */

/**
 * Hook class.
 */
class Hook_actionlog_referrals extends Hook_actionlog
{
    /**
     * Get details of action log entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (!addon_installed('referrals')) {
            return array();
        }

        require_lang('referrals');

        return array(
            '_MANUALLY_ADJUST_SCHEME_SETTINGS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => 0,
                'written_context_index' => 0,
                'followup_page_links' => array(
                    'REFERRALS' => '_SEARCH:admin_referrals',
                ),
            ),
        );
    }
}