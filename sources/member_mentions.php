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
 * @package    core_rich_media
 */

/**
 * Dispatch any pending member mention notifications ("mentions").
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $content_id The content ID
 * @param  ?MEMBER $submitter The content submitter (null: current user)
 */
function dispatch_member_mention_notifications($content_type, $content_id, $submitter = null)
{
    global $MEMBER_MENTIONS_IN_COMCODE;
    if ((!isset($MEMBER_MENTIONS_IN_COMCODE)) || (count($MEMBER_MENTIONS_IN_COMCODE) == 0)) {
        return;
    }

    if ($submitter === null) {
        $submitter = get_member();
    }
    $poster_username = $GLOBALS['FORUM_DRIVER']->get_username($submitter);
    $poster_displayname = $GLOBALS['FORUM_DRIVER']->get_username($submitter, true);

    require_code('notifications');
    require_code('content');
    require_code('feedback');
    require_lang('comcode');

    $mentions = array_unique($MEMBER_MENTIONS_IN_COMCODE);
    $MEMBER_MENTIONS_IN_COMCODE = array(); // Reset
    foreach ($mentions as $member_id) {
        if (!may_view_content_behind($member_id, $content_type, $content_id)) {
            continue;
        }

        $cma_ob = get_content_object($content_type);
        $info = $cma_ob->info();
        list($content_title, $submitter_id, $cma_info, , , $content_url_email_safe) = content_get_details($content_type, $content_id);

        if ($content_title === null) {
            continue;
        }

        $content_type_title = do_lang($cma_info['content_type_label']);

        // Special case. Would prefer not to hard-code, but important for usability
        if (($content_type == 'post') && ($content_title == '') && (get_forum_type() == 'cns')) {
            $content_title = do_lang('POST_IN', $GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_cache_first_title', array('id' => $GLOBALS['FORUM_DB']->query_select_value('f_posts', 'p_topic_id', array('id' => intval($content_id))))));
        }

        $rendered = '';
        if ($content_type != '') {
            $cma_content_row = content_get_row($content_id, $info);
            if ($cma_content_row !== null) {
                push_no_keep_context();
                $rendered = static_evaluate_tempcode($cma_ob->run($cma_content_row, '_SEARCH', true, true));
                pop_no_keep_context();
            }
        }

        $subject = do_lang('NOTIFICATION_MEMBER_MENTION_SUBJECT', $poster_username, strtolower($content_type_title), array($content_title, $content_url_email_safe->evaluate(), $content_type_title, $poster_displayname));
        $message = do_notification_lang('NOTIFICATION_MEMBER_MENTION_BODY', comcode_escape($poster_username), comcode_escape(strtolower($content_type_title)), array(comcode_escape($content_title), $content_url_email_safe->evaluate(), comcode_escape($content_type_title), comcode_escape($poster_displayname), $rendered));

        dispatch_notification('member_mention', '', $subject, $message, array($member_id), get_member());
    }
}
