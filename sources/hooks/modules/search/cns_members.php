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
 * @package    core_cns
 */

/**
 * Hook class.
 */
class Hook_search_cns_members extends FieldsSearchHook
{
    /**
     * Find details for this search hook.
     *
     * @param  boolean $check_permissions Whether to check permissions.
     * @return ?array Map of search hook details (null: hook is disabled).
     */
    public function info($check_permissions = true)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        if (($GLOBALS['FORUM_DRIVER']->get_members() <= 3) && (get_param_string('id', '') != 'cns_members') && (get_param_integer('search_cns_members', 0) != 1)) {
            return null;
        }

        require_lang('cns');

        $info = array();
        $info['lang'] = do_lang_tempcode('MEMBERS');
        $info['default'] = false;
        $info['special_on'] = array();
        $info['special_off'] = array();
        $info['user_label'] = do_lang_tempcode('USERNAME');
        $info['days_label'] = do_lang_tempcode('JOINED_AGO');
        $info['days_label'] = do_lang_tempcode('JOINED_DATE_RANGE');

        $extra_sort_fields = array();
        if (has_privilege(get_member(), 'view_profiles')) {
            require_code('cns_members');
            $rows = cns_get_all_custom_fields_match(null, has_privilege(get_member(), 'view_any_profile_field') ? null : 1, has_privilege(get_member(), 'view_any_profile_field') ? null : 1);
            foreach ($rows as $row) {
                $extra_sort_fields['field_' . strval($row['id'])] = $row['trans_name'];
            }
        }
        $info['extra_sort_fields'] = $extra_sort_fields;

        $info['permissions'] = array();

        return $info;
    }

    /**
     * Get a list of extra fields to ask for.
     *
     * @return ?array A list of maps specifying extra fields (null: no tree)
     */
    public function get_fields()
    {
        require_code('cns_members');

        $fields = array();
        if (has_privilege(get_member(), 'view_profiles')) {
            $rows = cns_get_all_custom_fields_match(null, has_privilege(get_member(), 'view_any_profile_field') ? null : 1, has_privilege(get_member(), 'view_any_profile_field') ? null : 1);
            require_code('fields');
            foreach ($rows as $row) {
                $ob = get_fields_hook($row['cf_type']);
                $temp = $ob->get_search_inputter($row);
                if (is_null($temp)) {
                    $type = '_TEXT';
                    $special = make_string_tempcode(get_param_string('option_' . strval($row['id']), ''));
                    $display = $row['trans_name'];
                    $fields[] = array('NAME' => strval($row['id']), 'DISPLAY' => $display, 'TYPE' => $type, 'SPECIAL' => $special);
                } else {
                    $fields[] = $temp;
                }
            }

            $age_range = get_param_string('option__age_range', get_param_string('option__age_range_from', '') . '-' . get_param_string('option__age_range_to', ''));
            $fields[] = array('NAME' => '_age_range', 'DISPLAY' => do_lang_tempcode('AGE_RANGE'), 'TYPE' => '_TEXT', 'SPECIAL' => $age_range);
        }

        $where = '1=1';
        if (!has_privilege(get_member(), 'see_hidden_groups')) {
            $members_groups = $GLOBALS['CNS_DRIVER']->get_members_groups(get_member());
            $where .= ' AND (g_hidden=0 OR g.id IN (' . implode(',', array_map('strval', $members_groups)) . '))';
        }
        $group_count = $GLOBALS['FORUM_DB']->query_select_value('f_groups g', 'COUNT(*)');
        if ($group_count > 300) {
            $where .= ' AND g_is_private_club=0';
        }
        $rows = $GLOBALS['FORUM_DB']->query('SELECT g.id,g_name FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups g WHERE ' . $where . ' ORDER BY g_order,' . $GLOBALS['FORUM_DB']->translate_field_ref('g_name'), null, null, false, false, array('g_name' => 'SHORT_TRANS'));
        $groups = form_input_list_entry('', false, '---');
        $default_group = get_param_string('option__user_group', '');
        $group_titles = array();
        $bits = explode(',', $default_group);
        foreach ($rows as $row) {
            $name = get_translated_text($row['g_name'], $GLOBALS['FORUM_DB']);

            if ($row['id'] == db_get_first_id()) {
                continue;
            }
            $groups->attach(form_input_list_entry(strval($row['id']), in_array(strval($row['id']), $bits), $name));
            $group_titles[$row['id']] = $name;
        }
        if (strpos($default_group, ',') !== false) {
            $bits = explode(',', $default_group);
            $combination = new Tempcode();
            foreach ($bits as $bit) {
                if (!$combination->is_empty()) {
                    $combination->attach(do_lang_tempcode('LIST_SEP'));
                }
                $combination->attach(escape_html(@$group_titles[intval($bit)]));
            }
            $groups->attach(form_input_list_entry(strval($default_group), true, do_lang_tempcode('USERGROUP_SEARCH_COMBO', escape_html($combination))));
        }
        $fields[] = array('NAME' => '_user_group', 'DISPLAY' => do_lang_tempcode('USERGROUP'), 'TYPE' => '_MULTI_LIST', 'SPECIAL' => $groups);
        return $fields;
    }

    /**
     * Run function for search results.
     *
     * @param  string $content Search string
     * @param  boolean $only_search_meta Whether to only do a META (tags) search
     * @param  ID_TEXT $direction Order direction
     * @param  integer $max Start position in total results
     * @param  integer $start Maximum results to return in total
     * @param  boolean $only_titles Whether only to search titles (as opposed to both titles and content)
     * @param  string $content_where Where clause that selects the content according to the main search string (SQL query fragment) (blank: full-text search)
     * @param  SHORT_TEXT $author Username/Author to match for
     * @param  ?MEMBER $author_id Member-ID to match for (null: unknown)
     * @param  mixed $cutoff Cutoff date (TIME or a pair representing the range)
     * @param  string $sort The sort type (gets remapped to a field in this function)
     * @set    title add_date
     * @param  integer $limit_to Limit to this number of results
     * @param  string $boolean_operator What kind of boolean search to do
     * @set    or and
     * @param  string $where_clause Where constraints known by the main search code (SQL query fragment)
     * @param  string $search_under Comma-separated list of categories to search under
     * @param  boolean $boolean_search Whether it is a boolean search
     * @return array List of maps (template, orderer)
     */
    public function run($content, $only_search_meta, $direction, $max, $start, $only_titles, $content_where, $author, $author_id, $cutoff, $sort, $limit_to, $boolean_operator, $where_clause, $search_under, $boolean_search)
    {
        if (get_forum_type() != 'cns') {
            return array();
        }
        require_code('cns_members');

        $remapped_orderer = '';
        switch ($sort) {
            case 'title':
                $remapped_orderer = 'm_username';
                break;

            case 'add_date':
                $remapped_orderer = 'm_join_time';
                break;

            case 'relevance':
            case 'average_rating':
            case 'compound_rating':
                break;

            default:
                if (preg_match('#^field\_\d+$#', $sort) != 0) {
                    $remapped_orderer = $sort;
                }
                break;
        }

        require_lang('cns');

        $indexes = collapse_2d_complexity('i_fields', 'i_name', $GLOBALS['FORUM_DB']->query_select('db_meta_indices', array('i_fields', 'i_name'), array('i_table' => 'f_member_custom_fields'), 'ORDER BY i_name'));

        // Calculate our where clause (search)
        if ($author != '') {
            $where_clause .= ' AND ';
            $where_clause .= db_string_equal_to('m_username', $author);
        }
        $this->_handle_date_check($cutoff, 'm_join_time', $where_clause);
        $raw_fields = array('m_username');
        $trans_fields = array();
        $rows = cns_get_all_custom_fields_match(null, has_privilege(get_member(), 'view_any_profile_field') ? null : 1, has_privilege(get_member(), 'view_any_profile_field') ? null : 1);
        $table = '';
        require_code('fields');
        $non_trans_fields = 0;
        foreach ($rows as $i => $row) {
            $ob = get_fields_hook($row['cf_type']);
            list(, , $storage_type) = $ob->get_field_value_row_bits($row);
            if (strpos($storage_type, '_trans') === false) {
                $non_trans_fields++;
            }
        }
        $index_issue = (get_param_integer('force_like', 0) == 0) && ($non_trans_fields > 16); // MySQL limit for fulltext index querying. We'll therefore not throw EVERY searchable field into the search query (only core ones, and ones we're explicitly filtering on)
        if ($index_issue) {
            $boolean_search = true;
            list($content_where) = build_content_where($content, $boolean_search, $boolean_operator); // Rebuilding $content_where from what was passed to this function
        }
        foreach ($rows as $i => $row) {
            $ob = get_fields_hook($row['cf_type']);
            list(, , $storage_type) = $ob->get_field_value_row_bits($row);

            // Filter form
            $param = get_param_string('option_' . strval($row['id']), '');
            if ($param != '') {
                $where_clause .= ' AND ';

                if ($storage_type == 'integer') {
                    $temp = '?=' . strval(intval($param));
                } elseif ($storage_type == 'float') {
                    $temp = '?=' . float_to_raw_string(floatval($param));
                } elseif ($storage_type == 'list') {
                    $temp = db_string_equal_to('?', $param);
                } elseif (
                    (array_key_exists('field_' . strval($row['id']), $indexes)) && ($indexes['field_' . strval($row['id'])][0] == '#') &&
                    (db_has_full_text($GLOBALS['SITE_DB']->connection_read)) &&
                    ((method_exists($GLOBALS['SITE_DB']->static_ob, 'db_has_full_text_boolean')) && ($GLOBALS['SITE_DB']->static_ob->db_has_full_text_boolean()) || (!$boolean_search)) &&
                    (!is_under_radar($param))
                ) {
                    $temp = db_full_text_assemble('"' . $param . '"', true);
                } else {
                    list($temp,) = db_like_assemble($param);
                }
                if ((($row['cf_type'] == 'short_trans') || ($row['cf_type'] == 'long_trans') || ($row['cf_type'] == 'posting_field') || ($row['cf_type'] == 'short_trans_multi')) && (multi_lang_content())) {
                    // Goes through translate table
                    $where_clause .= preg_replace('#\?#', 't' . strval(count($trans_fields) + 2/*for the 2 fields prepended to $trans_fields in the get_search_rows call*/) . '.text_original', $temp);
                } else {
                    // Direct field access
                    $where_clause .= preg_replace('#\?#', 'field_' . strval($row['id']), $temp);
                }
            }

            // Standard search
            if (((array_key_exists('field_' . strval($row['id']), $indexes)) && ($indexes['field_' . strval($row['id'])][0] == '#')) || ($boolean_search)) {
                if (strpos($storage_type, '_trans') === false) {
                    if ((!$index_issue) || ($boolean_search)) {
                        $raw_fields[] = 'field_' . strval($row['id']);
                    }
                } else {
                    if ((!$index_issue) || ($boolean_search) || (multi_lang_content())) { // MySQL limit for fulltext index querying
                        $trans_fields['field_' . strval($row['id'])] = 'LONG_TRANS__COMCODE';
                    }
                }
            }
        }
        $age_range = get_param_string('option__age_range', get_param_string('option__age_range_from', '') . '-' . get_param_string('option__age_range_to', ''));
        if (($age_range != '') && ($age_range != '-')) {
            $bits = explode('-', $age_range);
            if (count($bits) == 2) {
                $lower = strval(intval(date('Y', utctime_to_usertime())) - intval($bits[0]));
                $upper = strval(intval(date('Y', utctime_to_usertime())) - intval($bits[1]));

                $where_clause .= ' AND ';
                $where_clause .= '(m_dob_year<' . $lower . ' OR m_dob_year=' . $lower . ' AND (m_dob_month<' . date('m') . ' OR m_dob_month=' . date('m') . ' AND m_dob_day<=' . date('d') . '))';
                $where_clause .= ' AND ';
                $where_clause .= '(m_dob_year>' . $upper . ' OR m_dob_year=' . $upper . ' AND (m_dob_month>' . date('m') . ' OR m_dob_month=' . date('m') . ' AND m_dob_day>=' . date('d') . '))';
            }
            if (either_param_integer('option__photo_thumb_url', 0) == 1) {
                $where_clause .= ' AND ';
                $where_clause .= db_string_not_equal_to('m_photo_thumb_url', '');
            }
        }
        $user_group = get_param_string('option__user_group', '');
        if ($user_group != '') {
            $bits = explode(',', $user_group);
            $where_clause .= ' AND ';
            $group_where_clause = '';
            foreach ($bits as $i => $bit) {
                $group = intval($bit);
                $table .= ' LEFT JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_group_members g' . strval($i) . ' ON (g' . strval($i) . '.gm_group_id=' . strval($group) . ' AND g' . strval($i) . '.gm_member_id=r.id)';
                if ($group_where_clause != '') {
                    $group_where_clause .= ' OR ';
                }
                $group_where_clause .= 'g' . strval($i) . '.gm_validated=1 OR m_primary_group=' . strval($group);
            }
            $where_clause .= '(' . $group_where_clause . ')';
        }

        if ((!has_privilege(get_member(), 'see_unvalidated')) && (addon_installed('unvalidated'))) {
            $where_clause .= ' AND ';
            $where_clause .= 'm_validated=1';
        }

        $where_clause .= ' AND r.id IS NOT NULL';

        // Calculate and perform query
        $rows = get_search_rows(null, null, $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, 'f_members r JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_member_custom_fields a ON r.id=a.mf_member_id' . $table, array('!' => '!', 'm_signature' => 'LONG_TRANS__COMCODE') + $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*,a.*,r.id AS id', $raw_fields);

        $out = array();
        foreach ($rows as $i => $row) {
            if (!is_guest($row['id'])) {
                $out[$i]['data'] = $row;
                if (($remapped_orderer != '') && (array_key_exists($remapped_orderer, $row))) {
                    $out[$i]['orderer'] = $row[$remapped_orderer];
                } elseif (strpos($remapped_orderer, '_rating:') !== false) {
                    $out[$i]['orderer'] = $row[$remapped_orderer];
                }
            } else {
                $out[$i]['data'] = null;
            }
            unset($rows[$i]);
        }

        return $out;
    }

    /**
     * Run function for rendering a search result.
     *
     * @param  array $row The data row stored when we retrieved the result
     * @return Tempcode The output
     */
    public function render($row)
    {
        if (is_null($row['id'])) {
            return new Tempcode(); // Should not happen, some weird DB corruption probably
        }

        require_code('cns_members');
        if (get_param_integer('option__emails_only', 0) == 1) {
            $link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($row['id'], false, $row['m_username'], false);
            $link2 = ($row['m_email_address'] == '') ? new Tempcode() : hyperlink('mailto: ' . $row['m_email_address'], $row['m_email_address'], false, true);
            return paragraph($link->evaluate() . ' &lt;' . $link2->evaluate() . '&gt;', 'e3f;l23kf;l320932kl');
        }
        require_code('cns_members2');
        $GLOBALS['CNS_DRIVER']->MEMBER_ROWS_CACHED[$row['id']] = $row;
        $box = render_member_box($row['id']);
        return $box;
    }
}
