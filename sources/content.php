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
 * @package    core
 */

/*

Notes about hook info...
 - id_field may be array (which means that ":" works as a delimiter) (if so, the first one is the main ID, while the second one is assumed to be a qualifier)
  - unless, parent_spec__table_name!=table, where we require a single id_field, knowing it is a join field in all tables
 - category_field may be array of two (if so, the second one is assumed the main category, while the first is assumed to be for supplemental permission checking)
 - category_field may be null
 - category_type may be array
 - category_type may be '<page>' or '<zone>' (meaning "use page/zone permissions instead")
 - category_type may be null
 - category_type may be missing
 - add_url may contain '!' (meaning "parent category ID goes here")
 - submitter_field may be a field:regexp

*/

/**
 * Given a particular bit of feedback content, check if the user may access it.
 *
 * @param  MEMBER $member_id User to check
 * @param  ID_TEXT $content_type Content type
 * @param  ID_TEXT $content_id Content ID
 * @param  ID_TEXT $type_has Content type type
 * @return boolean Whether there is permission
 */
function may_view_content_behind($member_id, $content_type, $content_id, $type_has = 'content_type')
{
    $permission_type_code = convert_composr_type_codes($type_has, $content_type, 'permissions_type_code');

    $module = convert_composr_type_codes($type_has, $content_type, 'module');
    if ($module == '') {
        $module = $content_id;
    }

    $category_id = mixed();
    $content_type = convert_composr_type_codes($type_has, $content_type, 'content_type');
    if ($content_type != '') {
        $content_type_ob = get_content_object($content_type);
        $info = $content_type_ob->info();
        if (isset($info['category_field'])) {
            list(, , , $content) = content_get_details($content_type, $content_id);
            if ($content !== null) {
                $category_field = $info['category_field'];
                if (is_array($category_field)) {
                    $category_field = array_pop($category_field);
                    $category_id = is_integer($content[$category_field]) ? strval($content[$category_field]) : $content[$category_field];
                    if ($content_type == 'catalogue_entry') {
                        $catalogue_name = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'c_name', array('id' => $category_id));
                        if (!has_category_access($member_id, 'catalogues_catalogue', $catalogue_name)) {
                            return false;
                        }
                    }
                } else {
                    $category_id = is_integer($content[$category_field]) ? strval($content[$category_field]) : $content[$category_field];
                }
            }
        }
    }

    // FUDGE: Extra check for private topics
    $topic_id = null;
    if (($content_type == 'post') && (get_forum_type() == 'cns')) {
        $post_rows = $GLOBALS['FORUM_DB']->query_select('f_posts', array('p_topic_id', 'p_intended_solely_for', 'p_poster'), array('id' => intval($content_id)), '', 1);
        if (!array_key_exists(0, $post_rows)) {
            return false;
        }
        if ($post_rows[0]['p_intended_solely_for'] !== null && ($post_rows[0]['p_intended_solely_for'] != $member_id && $post_rows[0]['p_poster'] != $member_id || is_guest($member_id))) {
            return false;
        }
        $topic_id = $post_rows[0]['p_topic_id'];
    }
    if (($content_type == 'topic') && (get_forum_type() == 'cns')) {
        $topic_id = intval($content_id);
    }
    if ($topic_id !== null) {
        $topic_rows = $GLOBALS['FORUM_DB']->query_select('f_topics', array('t_forum_id', 't_pt_from', 't_pt_to'), array('id' => $topic_id), '', 1);
        if (!array_key_exists(0, $topic_rows)) {
            return false;
        }
        require_code('cns_topics');
        if ($topic_rows[0]['t_forum_id'] === null && ($topic_rows[0]['t_pt_from'] != $member_id && $topic_rows[0]['t_pt_to'] != $member_id && !cns_has_special_pt_access($topic_id, $member_id) || is_guest($member_id))) {
            return false;
        }
    }

    return ((has_actual_page_access($member_id, $module)) && (($permission_type_code == '') || ($category_id === null) || (has_category_access($member_id, $permission_type_code, $category_id))));
}

/**
 * Get the CMA hook object for a content type. Also works for resource types (i.e. if it's a resource, although not actually considered content technically).
 *
 * @param  ID_TEXT $content_type The content type
 * @return ?object The object (null: could not get one)
 */
function get_content_object($content_type)
{
    static $cache = array();
    if (isset($cache[$content_type])) {
        return $cache[$content_type];
    }

    $path = 'hooks/systems/content_meta_aware/' . filter_naughty_harsh($content_type, true);
    if ((file_exists(get_file_base() . '/sources/' . $path . '.php')) || (file_exists(get_file_base() . '/sources_custom/' . $path . '.php'))) {
        require_code($path);
        $ob = object_factory('Hook_content_meta_aware_' . filter_naughty_harsh($content_type), true);
    } else {
        // Okay, maybe it's a resource type (more limited functionality).
        $path = 'hooks/systems/resource_meta_aware/' . filter_naughty_harsh($content_type, true);
        if ((file_exists(get_file_base() . '/sources/' . $path . '.php')) || (file_exists(get_file_base() . '/sources_custom/' . $path . '.php'))) {
            require_code('hooks/systems/resource_meta_aware/' . filter_naughty_harsh($content_type));
            $ob = object_factory('Hook_resource_meta_aware_' . filter_naughty_harsh($content_type), true);
        } else {
            $ob = null;
        }
    }

    $cache[$content_type] = $ob;
    return $ob;
}

/**
 * Find a different content type code from the one had.
 *
 * @param  ID_TEXT $type_has Content type type we know
 * @set addon content_type meta_hook search_hook seo_type_code feedback_type_code permissions_type_code module table commandr_filesystem_hook rss_hook attachment_hook unvalidated_hook notification_hook sitemap_hook
 * @param  ID_TEXT $type_id Content type ID we know
 * @param  ID_TEXT $type_wanted Desired content type
 * @set addon content_type meta_hook search_hook seo_type_code feedback_type_code permissions_type_code module table commandr_filesystem_hook rss_hook attachment_hook unvalidated_hook notification_hook sitemap_hook
 * @return ID_TEXT Corrected content type type (blank: could not find)
 */
function convert_composr_type_codes($type_has, $type_id, $type_wanted)
{
    $real_type_wanted = $type_wanted;

    $type_id = preg_replace('#^catalogues__[' . URL_CONTENT_REGEXP . ']+_#', 'catalogues_', $type_id);

    // Search content-meta-aware hooks
    $found_type_id = '';
    $cma_hooks = find_all_hooks('systems', 'content_meta_aware') + find_all_hooks('systems', 'resource_meta_aware');
    foreach (array_keys($cma_hooks) as $content_type) {
        if ((($type_has == 'content_type') && ($content_type == $type_id)) || ($type_has != 'content_type')) {
            $cma_ob = get_content_object($content_type);
            $cma_info = $cma_ob->info();
            $cma_info['content_type'] = $content_type;
            if ((isset($cma_info[$type_has])) && (isset($cma_info[$type_wanted])) && (($cma_info[$type_has] == $type_id) || ($cma_info[$type_has] == preg_replace('#__.*$#', '', $type_id)))) {
                $found_type_id = $cma_info[$type_wanted];
                break;
            }
        }
    }

    if ($found_type_id === null) {
        $found_type_id = '';
    }
    return $found_type_id;
}

/**
 * Find content type info, for a particular content type type we know.
 *
 * @param  ID_TEXT $type_has Content type type we know
 * @set addon content_type meta_hook search_hook seo_type_code feedback_type_code permissions_type_code module table commandr_filesystem_hook rss_hook attachment_hook unvalidated_hook notification_hook sitemap_hook
 * @param  ID_TEXT $type_id Content type ID we know
 * @return array Content type info list (blank: could not find)
 */
function convert_composr_type_codes_multiple($type_has, $type_id)
{
    $type_id = preg_replace('#^catalogues__[' . URL_CONTENT_REGEXP . ']+_#', 'catalogues_', $type_id);

    // Search content-meta-aware hooks
    $found_type_ids = array();
    $cma_hooks = find_all_hooks('systems', 'content_meta_aware') + find_all_hooks('systems', 'resource_meta_aware');
    foreach (array_keys($cma_hooks) as $content_type) {
        if ((($type_has == 'content_type') && ($content_type == $type_id)) || ($type_has != 'content_type')) {
            $cma_ob = get_content_object($content_type);
            $cma_info = $cma_ob->info();
            $cma_info['content_type'] = $content_type;
            if ((isset($cma_info[$type_has])) && (($cma_info[$type_has] == $type_id) || ($cma_info[$type_has] == preg_replace('#__.*$#', '', $type_id)))) {
                $found_type_ids[] = $cma_info;
            }
        }
    }

    return $found_type_ids;
}

/**
 * Get meta details of a content item
 *
 * @param  ID_TEXT $content_type Content type
 * @param  ID_TEXT $content_id Content ID
 * @param  boolean $resource_fs_style Whether to use the content API as resource-fs requires (may be slightly different)
 * @return array Tuple: title, submitter, content hook info, the content row, URL (for use within current browser session), URL (for use in emails / sharing)
 */
function content_get_details($content_type, $content_id, $resource_fs_style = false)
{
    $cma_ob = get_content_object($content_type);
    if (!is_object($cma_ob)) {
        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }
    $cma_info = $cma_ob->info();

    if ($cma_info === null) {
        return array(null, null, null, null, null, null);
    }

    $db = $cma_info['db'];

    $content_row = content_get_row($content_id, $cma_info);
    if ($content_row === null) {
        if (($content_type == 'comcode_page') && (strpos($content_id, ':') !== false) && (!$resource_fs_style)) {
            list($zone, $page) = explode(':', $content_id, 2);

            $members = $GLOBALS['FORUM_DRIVER']->member_group_query($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(), 1);
            if (count($members) != 0) {
                $submitter_id = $GLOBALS['FORUM_DRIVER']->mrow_id($members[key($members)]);
            } else {
                $submitter_id = db_get_first_id() + 1; // On Conversr and most forums, this is the first admin member
            }

            $content_row = array(
                'the_zone' => $zone,
                'the_page' => $page,
                'p_parent_page' => '',
                'p_validated' => 1,
                'p_edit_date' => null,
                'p_add_date' => time(),
                'p_submitter' => $submitter_id,
                'p_show_as_edit' => 0
            );

            $content_url = build_url(array('page' => $page), $zone, array(), false, false, false);
            $content_url_email_safe = build_url(array('page' => $page), $zone, array(), false, false, true);

            $_content_title = $GLOBALS['SITE_DB']->query_select_value_if_there('cached_comcode_pages', 'cc_page_title', array('the_zone' => $zone, 'the_page' => $page));
            if ($_content_title !== null) {
                $content_title = get_translated_text($_content_title);
            } else {
                $content_title = $zone . ':' . $page;
            }

            return array($content_title, $submitter_id, $cma_info, $content_row, $content_url, $content_url_email_safe);
        }

        return array(null, null, $cma_info, null, null, null);
    }

    $content_title = get_content_title($cma_info, $content_row, $content_type, $content_id, $resource_fs_style);

    if ($cma_info['submitter_field'] !== null) {
        if (strpos($cma_info['submitter_field'], ':') !== false) {
            $bits = explode(':', $cma_info['submitter_field']);
            $matches = array();
            if (preg_match('#' . $bits[1] . '#', $content_row[$bits[0]], $matches) != 0) {
                $submitter_id = intval($matches[1]);
            } else {
                $submitter_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
            }
        } else {
            $submitter_id = $content_row[$cma_info['submitter_field']];
        }
    } else {
        $submitter_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
    }

    $content_url = mixed();
    $content_url_email_safe = mixed();
    if ($cma_info['view_page_link_pattern'] !== null) {
        list($zone, $url_bits, $hash) = page_link_decode(str_replace('_WILD', $content_id, $cma_info['view_page_link_pattern']));
        $content_url = build_url($url_bits, $zone, array(), false, false, false, $hash);
        $content_url_email_safe = build_url($url_bits, $zone, array(), false, false, true, $hash);
    }

    return array($content_title, $submitter_id, $cma_info, $content_row, $content_url, $content_url_email_safe);
}

/**
 * Get the title of a content item
 *
 * @param  array $cma_info The info array for the content type
 * @param  array $content_row Content row
 * @param  ID_TEXT $content_type Content type
 * @param  ?ID_TEXT $content_id Content ID (null: find from row)
 * @param  boolean $resource_fs_style Whether to use the content API as resource-fs requires (may be slightly different)
 * @return string Title
 */
function get_content_title($cma_info, $content_row, $content_type, $content_id = null, $resource_fs_style = false)
{
    $db = $cma_info['db'];

    if ($content_id === null) {
        $content_id = @strval($content_row[$cma_info['id_field']]);
    }

    $title_field = $cma_info['title_field'];
    $title_field_dereference = $cma_info['title_field_dereference'];
    if (($resource_fs_style) && (array_key_exists('title_field__resource_fs', $cma_info))) {
        $title_field = $cma_info['title_field__resource_fs'];
        $title_field_dereference = $cma_info['title_field_dereference__resource_fs'];
    }
    if ($title_field === null) {
        $content_title = do_lang($cma_info['content_type_label']);
    } else {
        if (strpos($title_field, 'CALL:') !== false) {
            $content_title = call_user_func(trim(substr($title_field, 5)), array('id' => $content_id), $resource_fs_style);
        } else {
            $_content_title = $content_row[$title_field];
            $content_title = $title_field_dereference ? get_translated_text($_content_title, $db) : $_content_title;
            if (($content_title == '') && (!$resource_fs_style)) {
                $content_title = do_lang($cma_info['content_type_label']) . ' (#' . (is_string($content_id) ? $content_id : strval($content_id)) . ')';
                if (($content_type == 'image' || $content_type == 'video') && (addon_installed('galleries'))) { // A bit of a fudge, but worth doing
                    require_lang('galleries');
                    $fullname = $GLOBALS['SITE_DB']->query_select_value_if_there('galleries', 'fullname', array('name' => $content_row['cat']));
                    if ($fullname !== null) {
                        $content_title = do_lang('VIEW_' . strtoupper($content_type) . '_IN', get_translated_text($fullname));
                    }
                }
            }
        }
    }

    if (($content_type == 'post') && ($content_title == '')) {
        $content_title = do_lang('cns:FORUM_POST_NUMBERED', $content_id);
    }

    if ($content_title == '') {
        $content_title = $content_type . ' #' . $content_id;
    }

    return $content_title;
}

/**
 * Get the content row of a content item.
 *
 * @param  ID_TEXT $content_id The content ID
 * @param  array $cma_info The info array for the content type
 * @return ?array The row (null: not found)
 */
function content_get_row($content_id, $cma_info)
{
    static $cache = array();
    $cache_key = $cma_info['table'] . '.' . $content_id;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $db = $cma_info['db'];

    $id_field_numeric = array_key_exists('id_field_numeric', $cma_info) ? $cma_info['id_field_numeric'] : true;
    $where = get_content_where_for_str_id($content_id, $cma_info);
    $_content = $db->query_select($cma_info['table'] . ' r', array('r.*'), $where, '', 1);

    $ret = array_key_exists(0, $_content) ? $_content[0] : null;
    $cache[$cache_key] = $ret;
    return $ret;
}

/**
 * Get the string content ID for some data.
 *
 * @param  array $data The data row
 * @param  array $cma_info The info array for the content type
 * @return ID_TEXT The ID
 */
function extract_content_str_id_from_data($data, $cma_info)
{
    $id_field = $cma_info['id_field'];
    $id = '';
    $id_field_parts = is_array($id_field) ? $id_field : array($id_field);
    $id_field_parts = array_reverse($id_field_parts);
    foreach ($id_field_parts as $id_field_part) {
        if ($id != '') {
            $id .= ':';
        }
        $id .= (is_integer($data[$id_field_part]) ? strval($data[$id_field_part]) : $data[$id_field_part]);
    }
    return $id;
}

/**
 * Given the string content ID get a mapping we could use as a WHERE map.
 *
 * @param  ID_TEXT $str_id The ID
 * @param  array $cma_info The info array for the content type
 * @param  ?string $table_alias The table alias (null: none)
 * @return array The mapping
 */
function get_content_where_for_str_id($str_id, $cma_info, $table_alias = null)
{
    $where = array();
    $id_field = $cma_info['id_field'];
    $id_parts = is_array($id_field) ? explode(':', $str_id) : array($str_id);
    $id_parts = array_reverse($id_parts);
    foreach (is_array($id_field) ? $id_field : array($id_field) as $i => $id_field_part) {
        $val = array_key_exists($i, $id_parts) ? $id_parts[$i] : '';
        $where[(($table_alias === null) ? '' : ($table_alias . '.')) . $id_field_part] = $cma_info['id_field_numeric'] ? intval($val) : $val;
    }
    return $where;
}

/**
 * Given the string content ID get a mapping we could use as a WHERE map.
 *
 * @param  array $select The ID
 * @param  array $cma_info The info array for the content type
 * @param  ?string $table_alias The table alias (null: none)
 */
function append_content_select_for_id(&$select, $cma_info, $table_alias = null)
{
    foreach (is_array($cma_info['id_field']) ? $cma_info['id_field'] : array($cma_info['id_field']) as $id_field_part) {
        $select[] = (($table_alias === null) ? '' : ($table_alias . '.')) . $id_field_part;
    }
}

/**
 * Get an action language string for a particular content type based on a stub.
 * If it can't get a match it'll just use the stub.
 *
 * @param  string $content_type The content type
 * @param  string $string The language string stub (must itself be a valid language string)
 * @return Tempcode Tempcode of language string
 */
function content_language_string($content_type, $string)
{
    $object = get_content_object($content_type);
    $info = $object->info();
    $regexp = $info['actionlog_regexp'];

    do_lang($info['content_type_label']); // This forces the language file to load if there is one, as it'll include the language file reference within content_type_label

    $string_custom = str_replace('\w+', $string, $regexp);
    $test = do_lang($string_custom, null, null, null, null, false);
    if ($test === null) {
        $test = do_lang($string);
    }

    //return do_lang_tempcode($string_custom); // Assumes that the lang string stays memory resident, but our probing only guarantees it's resident NOW
    return protect_from_escaping($test); // But this should work as the string is rolled into the Tempcode permanently
}
