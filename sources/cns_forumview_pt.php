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
 * Get a map of details relating to the Private Topics of a certain member.
 *
 * @param  integer $start The start row for getting details of topics in the Private Topic forum (i.e. 0 is newest, higher is starting further back in time).
 * @param  integer $true_start True offset when disconsidering keyset pagination
 * @param  ?integer $max The maximum number of topics to get detail of (null: default).
 * @param  string $sql_sup Extra SQL to append.
 * @param  string $sql_sup_order_by Extra SQL to append as order clause.
 * @param  ?MEMBER $member_id The member to get Private Topics of (null: current member).
 * @return array The details.
 */
function cns_get_private_topics($start = 0, $true_start = 0, $max = null, $sql_sup = '', $sql_sup_order_by = '', $member_id = null)
{
    if (is_null($max)) {
        $max = intval(get_option('forum_topics_per_page'));
    }

    if (is_null($member_id)) {
        $member_id = get_member();
    } else {
        if ((!has_privilege(get_member(), 'view_other_pt')) && ($member_id != get_member())) {
            access_denied('PRIVILEGE', 'view_other_pt');
        }
    }

    // Find topics
    $where = '(t_pt_from=' . strval($member_id) . ' OR t_pt_to=' . strval($member_id) . ') AND t_forum_id IS NULL';
    $filter = get_param_string('category', '');
    $where .= ' AND (' . db_string_equal_to('t_pt_from_category', $filter) . ' AND t_pt_from=' . strval($member_id) . ' OR ' . db_string_equal_to('t_pt_to_category', $filter) . ' AND t_pt_to=' . strval($member_id) . ')';
    $query = 'FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_topics t';
    if (!multi_lang_content()) {
        $query .= ' LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_posts p ON p.id=t.t_cache_first_post_id';
    }
    $query .= ' WHERE ' . $where;
    $max_rows = 0;
    $union = '';
    $select = 'SELECT t.*';
    if (multi_lang_content()) {
        $select .= ',t_cache_first_post AS p_post';
    } else {
        $select .= ',p_post,p_post__text_parsed,p_post__source_user';
    }
    if ($filter == do_lang('INVITED_TO_PTS')) {
        $or_list = '';
        $s_rows = $GLOBALS['FORUM_DB']->query_select('f_special_pt_access', array('s_topic_id'), array('s_member_id' => get_member()));
        foreach ($s_rows as $s_row) {
            if ($or_list != '') {
                $or_list .= ' OR ';
            }
            $or_list .= 't.id=' . strval($s_row['s_topic_id']);
        }
        if ($or_list != '') {
            $query2 = 'FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_topics t';
            if (!multi_lang_content()) {
                $query2 .= ' LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_posts p ON p.id=t.t_cache_first_post_id';
            }
            $query2 .= ' WHERE ' . $or_list;
            $union = ' UNION ' . $select . ' ' . $query2;
            $max_rows += $GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) ' . $query2, false, true);
        }
    }
    $query_full = $select;
    $query_full .= ' ' . $query . $union . $sql_sup . $sql_sup_order_by;
    $topic_rows = $GLOBALS['FORUM_DB']->query($query_full, $max, $start, false, true);
    $max_rows += $GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) ' . $query);
    $topics = array();
    $hot_topic_definition = intval(get_option('hot_topic_definition'));
    foreach ($topic_rows as $topic_row) {
        $topic = array();
        $topic['id'] = $topic_row['id'];
        $topic['num_views'] = $topic_row['t_num_views'];
        $topic['num_posts'] = $topic_row['t_cache_num_posts'];
        $topic['first_time'] = $topic_row['t_cache_first_time'];
        $topic['first_title'] = $topic_row['t_cache_first_title'];
        if (is_null($topic_row['p_post'])) {
            $topic['first_post'] = new Tempcode();
        } else {
            $post_row = db_map_restrict($topic_row, array('id', 'p_post'), array('id' => 't_cache_first_post_id'));
            $topic['first_post'] = get_translated_tempcode('f_posts', $post_row, 'p_post', $GLOBALS['FORUM_DB']);
        }
        $topic['first_post']->singular_bind('ATTACHMENT_DOWNLOADS', make_string_tempcode('?'));
        $topic['first_username'] = $topic_row['t_cache_first_username'];
        $topic['first_member_id'] = $topic_row['t_cache_first_member_id'];
        $topic['last_post_id'] = $topic_row['t_cache_last_post_id'];
        $topic['last_time'] = $topic_row['t_cache_last_time'];
        $topic['last_time_string'] = is_null($topic_row['t_cache_last_time']) ? '' : get_timezoned_date($topic_row['t_cache_last_time']);
        $topic['last_title'] = $topic_row['t_cache_last_title'];
        $topic['last_username'] = $topic_row['t_cache_last_username'];
        $topic['last_member_id'] = $topic_row['t_cache_last_member_id'];
        $topic['emoticon'] = $topic_row['t_emoticon'];
        $topic['description'] = $topic_row['t_description'];
        $topic['pt_from'] = $topic_row['t_pt_from'];
        $topic['pt_to'] = $topic_row['t_pt_to'];

        // Modifiers
        $topic['modifiers'] = array();
        $has_read = cns_has_read_topic($topic['id'], $topic_row['t_cache_last_time'], $member_id);
        if (!$has_read) {
            $topic['modifiers'][] = 'unread';
        }
        if ($topic_row['t_pinned'] == 1) {
            $topic['modifiers'][] = 'pinned';
        }
        if ($topic_row['t_sunk'] == 1) {
            $topic['modifiers'][] = 'sunk';
        }
        if ($topic_row['t_is_open'] == 0) {
            $topic['modifiers'][] = 'closed';
        }
        if (!is_null($topic_row['t_poll_id'])) {
            $topic['modifiers'][] = 'poll';
        }
        $num_posts = $topic_row['t_cache_num_posts'];
        $start_time = $topic_row['t_cache_first_time'];
        $end_time = $topic_row['t_cache_last_time'];
        $days = floatval($end_time - $start_time) / 60.0 / 60.0 / 24.0;
        if ($days == 0.0) {
            $days = 1.0;
        }
        if (intval(round($num_posts / $days)) >= $hot_topic_definition) {
            $topic['modifiers'][] = 'hot';
        }

        $topics[] = $topic;
    }

    $out = array('topics' => $topics, 'max_rows' => $max_rows);

    if ((has_privilege($member_id, 'moderate_private_topic')) && (($member_id == get_member()) || (has_privilege($member_id, 'multi_delete_topics')))) {
        $out['may_move_topics'] = 1;
        $out['may_delete_topics'] = 1;
        $out['may_change_max'] = 1;
    }
    if (cns_may_make_private_topic()) {
        $out['may_post_topic'] = 1;
    }

    return $out;
}
