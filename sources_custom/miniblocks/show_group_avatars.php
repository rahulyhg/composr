<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    show_group_avatars
 */

/*
    Parameters:

    order=date|random|username
    group_id
    limit
*/

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

$order = 'm_join_time DESC';
if (isset($map['order'])) {
    if ($map['order'] == 'random') {
        $order = db_function('RAND');
    }
    if ($map['order'] == 'username') {
        $order = 'm_username';
    }
}

$where = 'm_avatar_url<>\'\' AND ' . db_string_equal_to('m_validated_email_confirm_code', '');
if (addon_installed('unvalidated')) {
    $where .= ' AND m_validated=1';
}
if (isset($map['param'])) {
    if (is_numeric($map['param'])) {
        $group_id = intval($map['param']);
    } else {
        $group_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_groups', 'id', array($GLOBALS['FORUM_DB']->translate_field_ref('g_name') => $map['param']));
        if ($group_id === null) {
            $ret = paragraph(do_lang_tempcode('MISSING_RESOURCE'), '', 'nothing_here');
            $ret->evaluate_echo();
            return;
        }
    }
    $where .= ' AND (m_primary_group=' . strval($group_id) . ' OR EXISTS(SELECT gm_member_id FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_group_members x WHERE x.gm_member_id=m.id AND gm_validated=1 AND gm_group_id=' . strval($group_id) . '))';
}

$limit = isset($map['limit']) ? intval($map['limit']) : 200;

require_code('cns_members2');

echo '<div class="float-surrounder">';

$query = 'SELECT m.* FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m WHERE ' . $where . ' ORDER BY ' . $order;
$rows = $GLOBALS['FORUM_DB']->query($query, $limit);
foreach ($rows as $row) {
    $url = $GLOBALS['FORUM_DRIVER']->member_profile_url($row['id'], true);

    $avatar_url = $row['m_avatar_url'];
    if (url_is_local($avatar_url)) {
        $avatar_url = get_custom_base_url() . '/' . $avatar_url;
    }

    $username = $GLOBALS['FORUM_DRIVER']->get_username($row['id']);

    $tooltip = static_evaluate_tempcode(render_member_box($row['id'], true, false));

    echo '
        <div class="box left float_separation"><div class="box_inner">
            <a href="' . escape_html($url->evaluate()) . '"><img src="' . escape_html($avatar_url) . '" /></a><br />

            <a href="' . escape_html($url->evaluate()) . '" data-focus-activate-tooltip="[\'' . escape_html(str_replace("\n", '\n', addslashes($tooltip))) . '\',\'auto\']" data-mouseover-activate-tooltip="[\'' . escape_html(str_replace("\n", '\n', addslashes($tooltip))) . '\',\'auto\']" data-blur-deactivate-tooltip>' . escape_html($username) . '</a><br />
        </div></div>
    ';
}

echo '</div>';
