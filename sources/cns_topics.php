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
 * @package    core_cns
 */

/**
 * Render a topic box.
 *
 * @param  array $row Topic row
 * @param  ID_TEXT $zone Zone to link through to
 * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
 * @param  boolean $include_breadcrumbs Whether to include breadcrumbs (if there are any)
 * @param  ?AUTO_LINK $root Virtual root to use (null: none)
 * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
 * @return Tempcode The topic box
 */
function render_topic_box($row, $zone = '_SEARCH', $give_context = true, $include_breadcrumbs = true, $root = null, $guid = '')
{
    if ($row === null) { // Should never happen, but we need to be defensive
        return new Tempcode();
    }

    require_lang('cns');

    $map = array('page' => 'topicview', 'id' => $row['id']);
    if ($root !== null) {
        $map['keep_forum_root'] = $root;
    }
    $url = build_url($map, get_module_zone('topicview'));

    require_lang('cns');

    $_title = $row['t_cache_first_title'];
    $title = $give_context ? do_lang('CONTENT_IS_OF_TYPE', do_lang('FORUM_TOPIC'), $_title) : $_title;

    $breadcrumbs = null;
    if ($include_breadcrumbs) {
        require_code('cns_forums');
        $breadcrumbs = breadcrumb_segments_to_tempcode(cns_forum_breadcrumbs($row['t_forum_id'], null, null, false, ($root === null) ? get_param_integer('keep_forum_root', null) : $root));
    }

    $num_posts = $row['t_cache_num_posts'];
    $entry_details = do_lang_tempcode('FORUM_NUM_POSTS', escape_html(integer_format($num_posts)));

    return do_template('SIMPLE_PREVIEW_BOX', array(
        '_GUID' => ($guid != '') ? $guid : '85727b71bebcab45977363c8cb0a3ee6',
        'ID' => strval($row['id']),
        'TITLE' => $title,
        'TITLE_PLAIN' => $_title,
        'SUMMARY' => $row['t_description'],
        'URL' => $url,
        'ENTRY_DETAILS' => $entry_details,
        'BREADCRUMBS' => $breadcrumbs,
        'FRACTIONAL_EDIT_FIELD_NAME' => $give_context ? null : 'title',
        'FRACTIONAL_EDIT_FIELD_URL' => $give_context ? null : '_SEARCH:topics:_edit_topic:' . strval($row['id']),
        'RESOURCE_TYPE' => 'topic',
    ));
}

/**
 * Get an SQL 'WHERE' clause for the posts in a topic.
 *
 * @param  AUTO_LINK $topic_id The ID of the topic we are getting details of
 * @param  ?MEMBER $member_id The member doing the lookup (null: current member)
 * @return string The WHERE clause
 */
function cns_get_topic_where($topic_id, $member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    $where = 'p_topic_id=' . strval($topic_id);
    if (is_guest()) {
        $where .= ' AND p_intended_solely_for IS NULL';
    } elseif (!has_privilege($member_id, 'view_other_pt')) {
        $where .= ' AND (p_intended_solely_for=' . strval($member_id) . ' OR p_poster=' . strval($member_id) . ' OR p_intended_solely_for IS NULL)';
    }
    if ((!has_privilege($member_id, 'see_unvalidated')) && (addon_installed('unvalidated'))) {
        if (is_guest($member_id)) {
            $where .= ' AND (p_validated=1 OR (' . db_string_equal_to('p_ip_address', get_ip_address()) . ' AND p_poster=' . strval($member_id) . '))';
        } else {
            $where .= ' AND (p_validated=1 OR p_poster=' . strval($member_id) . ')';
        }
    }
    return $where;
}

/**
 * Find whether a member can access a particular post.
 *
 * @param  AUTO_LINK $topic_id Topic ID
 * @param  ?MEMBER $member_id Member involved (null: current member)
 * @param  ?array $topic_details Topic row (null: lookup)
 * @return boolean Whether they can
 */
function has_topic_access($topic_id, $member_id = null, $topic_details = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    if (!has_actual_page_access($member_id, 'topicview')) {
        return false;
    }

    if ($topic_details === null) {
        $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();
        $_topic_details = $GLOBALS['FORUM_DB']->query_select('f_topics t JOIN ' . $table_prefix . 'f_posts p ON t.t_cache_first_post_id=p.id', array('*', 't.id AS topic_id', 'p.id AS post_id'), array('t.id' => $topic_id), '', 1);
        if (!isset($_topic_details[0])) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'topic'));
        }
        $topic_details = $_topic_details[0];
    }

    if ($topic_details['t_forum_id'] === null) {
        require_code('cns_topics');
        if ((!has_privilege($member_id, 'view_other_pt')) && (!cns_has_special_pt_access($topic_id, $member_id))) {
            if (($topic_details['t_pt_from'] != $member_id) && ($topic_details['t_pt_to'] != $member_id)) {
                return false;
            }
        }
    } else {
        if (!has_category_access($member_id, 'forums', strval($topic_details['t_forum_id']))) {
            return false;
        }
    }

    if (addon_installed('unvalidated')) {
        if (($topic_details['t_validated'] == 0) && (!has_privilege($member_id, 'jump_to_unvalidated'))) {
            return false;
        }
    }

    return true;
}

/**
 * Find whether a member may make a Private Topic.
 *
 * @param  ?MEMBER $member_id The member (null: current member)
 * @return boolean The answer
 */
function cns_may_make_private_topic($member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    if (!has_privilege($member_id, 'use_pt')) {
        return false;
    }

    return !is_guest($member_id);
}

/**
 * Check that a member may make a Private Topic.
 */
function cns_check_make_private_topic()
{
    check_privilege('use_pt');

    if (is_guest()) {
        access_denied('NOT_AS_GUEST');
    }
}

/**
 * Find whether a member may post a topic in a certain forum.
 *
 * @param  AUTO_LINK $forum_id The forum the topic would be in
 * @param  ?MEMBER $member_id The member (null: current member)
 * @return boolean The answer
 */
function cns_may_post_topic($forum_id, $member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    if (!has_privilege($member_id, 'submit_midrange_content', 'topics', array('forums', $forum_id))) {
        return false;
    }
    if ($forum_id === null) {
        return true;
    }

    $test = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_warnings', 'id', array('p_silence_from_forum' => $forum_id, 'w_member_id' => $member_id));
    if ($test !== null) {
        return false;
    }

    return true;
}

/**
 * Find whether a member has replied to a certain topic.
 *
 * @param  AUTO_LINK $topic_id The topic
 * @param  ?MEMBER $member_id The member (null: current member)
 * @return boolean The answer
 */
function cns_has_replied_topic($topic_id, $member_id = null)
{
    $test = $GLOBALS['FORUM_DB']->query_select_value('f_posts', 'id', array('p_topic_id' => $topic_id, 'p_poster' => $member_id));
    return $test !== null;
}

/**
 * Find whether a member may edit topics in a certain forum.
 *
 * @param  AUTO_LINK $forum_id The forum the topic would be in
 * @param  MEMBER $member_id The member checking access for
 * @param  MEMBER $resource_owner The member that owns this resource
 * @return boolean The answer
 */
function cns_may_edit_topics_by($forum_id, $member_id, $resource_owner)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    if ($forum_id === null) {
        return has_privilege($member_id, 'moderate_private_topic');
    }

    return has_edit_permission('mid', $member_id, $resource_owner, 'topics', array('forums', $forum_id));
}

/**
 * Find whether a member may delete topics in a certain forum.
 *
 * @param  AUTO_LINK $forum_id The forum the topic would be in
 * @param  MEMBER $member_id The member checking access for
 * @param  MEMBER $resource_owner The member that owns this resource
 * @return boolean The answer
 */
function cns_may_delete_topics_by($forum_id, $member_id, $resource_owner)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    if ($forum_id === null) {
        return has_privilege($member_id, 'moderate_private_topic');
    }

    return has_delete_permission('mid', $member_id, $resource_owner, 'topics', array('forums', $forum_id));
}

/**
 * Mark a topic as read by the current member.
 *
 * @param  AUTO_LINK $topic_id The ID of the topic to mark as read
 * @param  ?MEMBER $member_id The member to do this for (null: current member)
 * @param  ?TIME $timestamp Mark read timestamp (null: now)
 */
function cns_ping_topic_read($topic_id, $member_id = null, $timestamp = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }
    if ($timestamp === null) {
        $timestamp = time();
    }
    if (!$GLOBALS['FORUM_DB']->table_is_locked('f_read_logs')) {
        $GLOBALS['FORUM_DB']->query_delete('f_read_logs', array('l_member_id' => $member_id, 'l_topic_id' => $topic_id), '', 1);
        $GLOBALS['FORUM_DB']->query_insert('f_read_logs', array('l_member_id' => $member_id, 'l_topic_id' => $topic_id, 'l_time' => $timestamp), false, true); // race condition
    }
}

/**
 * Find whether a member has read a certain topic, such that they have possibly read all posts within it already.
 *
 * @param  AUTO_LINK $topic_id The ID of the topic
 * @param  ?TIME $topic_last_time The time of the last post in the topic (null: get it from the DB)
 * @param  ?MEMBER $member_id The member (null: current member)
 * @param  ?TIME $member_last_time The time the member last viewed the topic (null: get it from the DB)
 * @return boolean They have read it as such, yes
 */
function cns_has_read_topic($topic_id, $topic_last_time = null, $member_id = null, $member_last_time = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }
    if ($member_id == $GLOBALS['CNS_DRIVER']->get_guest_id()) {
        return true;
    }

    if ($topic_last_time === null) {
        $topic_last_time = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_topics', 't_cache_last_time', array('id' => $topic_id));
        if ($topic_last_time === null) {
            return true; // Should not happen
        }
    }

    $post_read_history_days_ago = time() - 60 * 60 * 24 * intval(get_option('post_read_history_days'));

    if ((get_option('post_read_history_days') != '0') && (get_value('disable_normal_topic_read_history') !== '1')) {
        // Occasionally we need to delete old entries
        if (mt_rand(0, 100) == 1) {
            if (!$GLOBALS['FORUM_DB']->table_is_locked('f_read_logs')) {
                $GLOBALS['FORUM_DB']->query('DELETE FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_read_logs WHERE l_time<' . strval($post_read_history_days_ago) . ' AND l_time<>0', 500/*to reduce lock times*/);
            }
        }
    }

    if ($topic_last_time < $post_read_history_days_ago) {
        return true; // We don't store that old
    }
    if ($member_last_time === null) {
        $member_last_time = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_read_logs', 'l_time', array('l_member_id' => $member_id, 'l_topic_id' => $topic_id));
    }
    if ($member_last_time === null) {
        return false;
    }
    if ($member_last_time < $topic_last_time) {
        return false;
    }
    return true;
}

/**
 * Find whether a member has special access to a certain PT.
 *
 * @param  AUTO_LINK $topic_id The ID of the topic
 * @param  ?MEMBER $member_id The member (null: current member)
 * @return boolean Whether they have special access
 */
function cns_has_special_pt_access($topic_id, $member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    static $special_pt_access_cache = array();

    if (!array_key_exists($topic_id, $special_pt_access_cache)) {
        $special_pt_access_cache[$topic_id] = $GLOBALS['FORUM_DB']->query_select('f_special_pt_access', array('s_member_id'), array('s_topic_id' => $topic_id));
    }
    foreach ($special_pt_access_cache[$topic_id] as $t) {
        if ($t['s_member_id'] == $member_id) {
            return true;
        }
    }
    return false;
}
