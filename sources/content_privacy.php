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
 * @package    content_privacy
 */

/**
 * Get the SQL extension clauses for implementing privacy.
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $table_alias The table alias in the main query
 * @param  ?MEMBER $viewing_member_id Viewing member to check privacy against (null: current member)
 * @param  string $additional_or Additional OR clause for letting the user through
 * @param  ?MEMBER $submitter Member owning the content (null: do dynamically in query via content hook). Usually pass as null
 * @return array A tuple: extra JOIN clause, extra WHERE clause, table clause (rarely used), direct table WHERE clause (rarely used)
 */
function get_privacy_where_clause($content_type, $table_alias, $viewing_member_id = null, $additional_or = '', $submitter = null)
{
    if ($viewing_member_id === null) {
        $viewing_member_id = get_member();
    }

    if ($content_type[0] != '_') {
        require_code('content');
        $cma_ob = get_content_object($content_type);
        $cma_info = $cma_ob->info();

        if (!$cma_info['support_privacy']) {
            return array('', '', '', '');
        }

        $override_page = $cma_info['cms_page'];
        if (has_privilege($viewing_member_id, 'view_private_content', $override_page)) {
            return array('', '', '', '');
        }

        $join = ' LEFT JOIN ' . get_table_prefix() . 'content_privacy priv ON priv.content_id=' . $table_alias . '.' . $cma_info['id_field'] . ' AND ' . db_string_equal_to('priv.content_type', $content_type);
    } else {
        if (has_privilege($viewing_member_id, 'view_private_content')) {
            return array('', '', '', '');
        }

        $join = '';
    }

    $where = ' AND (';
    $where .= 'priv.content_id IS NULL';
    $where .= ' OR priv.guest_view=1';
    if (!is_guest($viewing_member_id)) {
        $where .= ' OR priv.member_view=1';
        if (addon_installed('chat')) {
            $where .= ' OR priv.friend_view=1 AND EXISTS(SELECT * FROM ' . get_table_prefix() . 'chat_friends f WHERE f.member_liked=' . (($submitter === null) ? ($table_alias . '.' . $cma_info['submitter_field']) : strval($submitter)) . ' AND f.member_likes=' . strval($viewing_member_id) . ')';
        }
        $where .= ' OR ' . (($submitter === null) ? ($table_alias . '.' . $cma_info['submitter_field']) : strval($submitter)) . '=' . strval($viewing_member_id);
        $where .= ' OR EXISTS(SELECT * FROM ' . get_table_prefix() . 'content_privacy__members pm WHERE pm.member_id=' . strval($viewing_member_id) . ' AND pm.content_id=' . (($submitter === null) ? ($table_alias . '.' . $cma_info['id_field']) : strval($submitter)) . ' AND ' . db_string_equal_to('pm.content_type', $content_type) . ')';
        if ($additional_or != '') {
            $where .= ' OR ' . $additional_or;
        }
    }
    $where .= ')';

    $table = get_table_prefix() . 'content_privacy priv';

    $table_where = db_string_equal_to('priv.content_type', $content_type) . $where;

    return array($join, $where, $table, $table_where);
}

/**
 * Check to see if some content may be viewed.
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $content_id The content ID
 * @param  ?MEMBER $viewing_member_id Viewing member to check privacy against (null: current member)
 * @return boolean Whether there is access
 */
function has_privacy_access($content_type, $content_id, $viewing_member_id = null)
{
    if ($viewing_member_id === null) {
        $viewing_member_id = get_member();
    }

    if ($content_type[0] == '_') { // Special case, not tied to a content row
        if (has_privilege($viewing_member_id, 'view_private_content')) {
            return true;
        }

        list(, , $privacy_table, $privacy_where) = get_privacy_where_clause($content_type, 'e', $viewing_member_id, '', intval($content_id));

        $query = 'SELECT * FROM ' . $privacy_table . ' WHERE ' . $privacy_where . ' AND ' . db_string_equal_to('priv.content_id', $content_id);
        $results = $GLOBALS['SITE_DB']->query($query, 1, null, false, true);

        if (array_key_exists(0, $results)) {
            return true;
        }
        if ($GLOBALS['SITE_DB']->query_select_value_if_there('content_privacy', 'content_id', array('content_type' => $content_type, 'content_id' => $content_id)) === null) {
            return true; // Maybe there was no privacy row, default to access on
        }
        return false;
    }

    require_code('content');
    $cma_ob = get_content_object($content_type);
    $cma_info = $cma_ob->info();

    if (!$cma_info['support_privacy']) {
        return true;
    }

    $override_page = $cma_info['cms_page'];
    if (has_privilege($viewing_member_id, 'view_private_content', $override_page)) {
        return true;
    }

    list($privacy_join, $privacy_where) = get_privacy_where_clause($content_type, 'e', $viewing_member_id);

    if ($cma_info['id_field_numeric']) {
        $where = 'e.' . $cma_info['id_field'] . '=' . strval(intval($content_id));
    } else {
        $where = db_string_equal_to('e.' . $cma_info['id_field'], $content_id);
    }
    $query = 'SELECT * FROM ' . get_table_prefix() . $cma_info['table'] . ' e' . $privacy_join . ' WHERE ' . $where . $privacy_where;
    $results = $GLOBALS['SITE_DB']->query($query, 1);

    return array_key_exists(0, $results);
}

/**
 * Check to see if some content may be viewed. Exit with an access denied if not.
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $content_id The content ID
 * @param  ?MEMBER $viewing_member_id Viewing member to check privacy against (null: current member)
 */
function check_privacy($content_type, $content_id, $viewing_member_id = null)
{
    if (!has_privacy_access($content_type, $content_id, $viewing_member_id)) {
        require_lang('content_privacy');
        access_denied('PRIVACY_BREACH');
    }
}

/**
 * Find list of members who may view some content.
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $content_id The content ID
 * @param  boolean $strict_all Whether to get a full list including friends even when there are over a thousand friends
 * @return ?array A list of member IDs that have access (null: no restrictions)
 */
function privacy_limits_for($content_type, $content_id, $strict_all = false)
{
    $rows = $GLOBALS['SITE_DB']->query_select('content_privacy', array('*'), array('content_type' => $content_type, 'content_id' => $content_id), '', 1);
    if (!array_key_exists(0, $rows)) {
        return null;
    }

    $row = $rows[0];

    if ($row['guest_view'] == 1) {
        return null;
    }
    if ($row['member_view'] == 1) {
        return null;
    }

    $members = array();

    require_code('content');
    list(, $content_submitter) = content_get_details($content_type, $content_id);

    $members[] = $content_submitter;

    if ($row['friend_view'] == 1 && addon_installed('chat')) {
        $cnt = $GLOBALS['SITE_DB']->query_select_value('chat_friends', 'COUNT(*)', array('chat_likes' => $content_submitter));
        if (($strict_all) || ($cnt <= 1000/*safety limit*/)) {
            $friends = $GLOBALS['SITE_DB']->query_select('chat_friends', array('chat_liked'), array('chat_likes' => $content_submitter));
            $members = array_merge($members, collapse_1d_complexity('member_liked', $friends));
        }
    }

    $GLOBALS['SITE_DB']->query_select('content_privacy__members', array('member_id'), array('content_type' => $content_type, 'content_id' => $content_id));
    $members = array_merge($members, collapse_1d_complexity('member_id', $friends));

    return $members;
}
