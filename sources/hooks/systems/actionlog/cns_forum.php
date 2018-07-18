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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_actionlog_cns_forum
{
    /**
     * Get details of actionlog entry types handled by this hook.
     *
     * @return array Map of handler data in standard format
     */
    public function get_handlers()
    {
        if (get_forum_type() != 'cns') {
            return array();
        }

        if (!addon_installed('cns_forum')) {
            return array();
        }

        require_lang('cns');

        return array(
            'ADD_FORUM_GROUPING' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum_grouping',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_FORUM_GROUPING' => 'TODO',
                    'ADD_FORUM_GROUPING' => 'TODO',
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'EDIT_FORUM_GROUPING' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum_grouping',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'EDIT_THIS_FORUM_GROUPING' => 'TODO',
                    'ADD_FORUM_GROUPING' => 'TODO',
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'DELETE_FORUM_GROUPING' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum_grouping',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_FORUM_GROUPING' => 'TODO',
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'ADD_FORUM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'FORUM' => 'TODO',
                    'EDIT_THIS_FORUM' => 'TODO',
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'EDIT_FORUM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'FORUM' => 'TODO',
                    'EDIT_THIS_FORUM' => 'TODO',
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'DELETE_FORUM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'ADD_FORUM' => 'TODO',
                ),
            ),
            'EDIT_TOPIC' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'EDIT_TOPIC_POLL' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => null,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'DELETE_TOPIC' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => 0,
                'written_context_index' => 1,
                'followup_page_links' => array(
                    'FORUM' => 'TODO',
                ),
            ),
            'DELETE_TOPIC_POLL' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => null,
                'identifier_index' => null,
                'written_context_index' => 1
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'MOVE_TOPICS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'forum',
                'identifier_index' => 1,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'FORUM' => 'TODO',
                ),
            ),
            'EDIT_POST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'post',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                    'EDIT_POST' => 'TODO',
                ),
            ),
            'DELETE_POST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'DELETE_POSTS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'VALIDATE_POST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'post',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                    'EDIT_POST' => 'TODO',
                ),
            ),
            'UNVALIDATE_POST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'post',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                    'EDIT_POST' => 'TODO',
                ),
            ),
            'MOVE_POSTS' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'topic',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'MAKE_ANONYMOUS_POST' => array(
                'flags' => ACTIONLOG_FLAGS_NONE | ACTIONLOG_FLAG__USER_ACTION,
                'cma_hook' => 'post',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_TOPIC' => 'TODO',
                    'EDIT_POST' => 'TODO',
                ),
            ),
            'SILENCE_FROM_FORUM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'member',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_PROFILE' => 'TODO',
                    'FORUM' => 'TODO',
                ),
            ),
            'UNSILENCE_FORUM' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'member',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_PROFILE' => 'TODO',
                    'FORUM' => 'TODO',
                ),
            ),
            'SILENCE_FROM_TOPIC' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'member',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_PROFILE' => 'TODO',
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
            'UNSILENCE_TOPIC' => array(
                'flags' => ACTIONLOG_FLAGS_NONE,
                'cma_hook' => 'member',
                'identifier_index' => 0,
                'written_context_index' => null,
                'followup_page_links' => array(
                    'VIEW_PROFILE' => 'TODO',
                    'VIEW_TOPIC' => 'TODO',
                ),
            ),
        );
    }
}
