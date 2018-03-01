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
 * @package    awards
 */

/**
 * Get details of awards won for a content item.
 *
 * @param  ID_TEXT $content_type The award content type
 * @param  ID_TEXT $id The content ID
 * @return array List of awards won
 */
function find_awards_for($content_type, $id)
{
    $awards = array();

    $rows = $GLOBALS['SITE_DB']->query_select('award_archive a LEFT JOIN ' . get_table_prefix() . 'award_types t ON t.id=a.a_type_id', array('date_and_time', 'a_type_id'), array('a_content_type' => $content_type, 'content_id' => $id), 'ORDER BY date_and_time DESC');
    foreach ($rows as $row) {
        require_lang('awards');
        $awards[] = array(
            'AWARD_TYPE' => get_translated_text($GLOBALS['SITE_DB']->query_select_value('award_types', 'a_title', array('id' => $row['a_type_id']))),
            'AWARD_TIMESTAMP' => strval($row['date_and_time']),
        );
    }

    return $awards;
}

/**
 * Give an award.
 *
 * @param  AUTO_LINK $award_id The award ID
 * @param  ID_TEXT $content_id The content ID
 * @param  ?TIME $time Time the award was given (null: now)
 */
function give_award($award_id, $content_id, $time = null)
{
    require_lang('awards');

    if ($time === null) {
        $time = time();
    }

    $awards = $GLOBALS['SITE_DB']->query_select('award_types', array('*'), array('id' => $award_id), '', 1);
    if (!array_key_exists(0, $awards)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'award_type'));
    }
    $award_title = get_translated_text($awards[0]['a_title']);
    log_it('GIVE_AWARD', strval($award_id), $award_title);

    require_code('content');
    $object = get_content_object($awards[0]['a_content_type']);
    $info = $object->info();
    if ($info === null) {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }
    if ((array_key_exists('submitter_field', $info)) && ($info['submitter_field'] !== null)) {
        require_code('content');
        list($content_title, $member_id, , $content) = content_get_details($awards[0]['a_content_type'], $content_id);

        if ($content === null) {
            warn_exit(do_lang_tempcode('_MISSING_RESOURCE', escape_html($awards[0]['a_content_type'] . ':' . $content_id), 'award_type'));
        }

        // Lots of fiddling around to work out how to check permissions for this
        $permission_type_code = convert_composr_type_codes('content_type', $awards[0]['a_content_type'], 'permissions_type_code');
        $module = convert_composr_type_codes('module', $awards[0]['a_content_type'], 'permissions_type_code');
        if ($module == '') {
            $module = $content_id;
        }
        $category_id = null;
        if (isset($info['category_field'])) {
            if (is_array($info['category_field'])) {
                $category_id = $content[$info['category_field'][1]];
            } else {
                $category_id = $content[$info['category_field']];
            }
        }
        require_code('users2');
        if ((has_actual_page_access(get_modal_user(), 'awards')) && (has_actual_page_access(get_modal_user(), $module)) && (($permission_type_code == '') || ($category_id === null) || (has_category_access(get_modal_user(), $permission_type_code, is_integer($category_id) ? strval($category_id) : $category_id)))) {
            $privacy_ok = true;
            if (addon_installed('content_privacy')) {
                require_code('content_privacy');
                $privacy_ok = has_privacy_access($awards[0]['a_content_type'], $content_id, $GLOBALS['FORUM_DRIVER']->get_guest_id());
            }
            if ($privacy_ok) {
                require_code('activities');
                syndicate_described_activity((($member_id === null) || (is_guest($member_id))) ? 'awards:_ACTIVITY_GIVE_AWARD' : 'awards:ACTIVITY_GIVE_AWARD', $award_title, $content_title, '', '_SEARCH:awards:award:' . strval($award_id), '', '', 'awards', 1, null, false, $member_id);
            }
        }
    } else {
        $member_id = null;
    }
    if ($member_id === null) {
        $member_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
    }

    if ((!is_guest($member_id)) && (addon_installed('points'))) {
        require_code('points2');
        system_gift_transfer(do_lang('_AWARD', get_translated_text($awards[0]['a_title'])), $awards[0]['a_points'], $member_id);
    }

    $GLOBALS['SITE_DB']->query_insert('award_archive', array('a_type_id' => $award_id, 'member_id' => $member_id, 'content_id' => $content_id, 'date_and_time' => $time));

    delete_cache_entry('main_awards');
    delete_cache_entry('main_multi_content');
}

/**
 * Get all the award selection fields for a content type and content ID.
 *
 * @param  mixed $content_type The content type
 * @param  ?ID_TEXT $id The content ID (null: not added yet - therefore can't be holding the award yet)
 * @return Tempcode The fields
 */
function get_award_fields($content_type, $id = null)
{
    require_code('form_templates');
    require_lang('awards');

    $fields = new Tempcode();

    foreach (is_array($content_type) ? $content_type : array($content_type) as $_content_type) {
        $rows = $GLOBALS['SITE_DB']->query_select('award_types', array('*'), array('a_content_type' => $_content_type));
        foreach ($rows as $i => $row) {
            $rows[$i]['_title'] = get_translated_text($row['a_title']);
        }
        sort_maps_by($rows, '_title');

        foreach ($rows as $row) {
            if (has_category_access(get_member(), 'award', strval($row['id']))) {
                $test = $GLOBALS['SITE_DB']->query_select_value_if_there('award_archive', 'content_id', array('a_type_id' => $row['id']), 'ORDER BY date_and_time DESC');

                if ($id !== null) {
                    $has_award = ($test === $id);
                } else {
                    $has_award = (get_param_integer('award', null) === $row['id']);
                }

                if (get_translated_text($row['a_description']) == '') {
                    $description = new Tempcode();
                } else {
                    $just_row = db_map_restrict($row, array('id', 'a_description'));
                    $description = do_lang_tempcode('PRESENT_AWARD', get_translated_tempcode('award_types', $just_row, 'a_description'));
                }

                if (!$has_award) {
                    $current_content_title = null;
                    if ($test !== null) {
                        require_code('content');
                        list($current_content_title) = content_get_details($_content_type, $test);
                    }
                    $description->attach(paragraph(do_lang_tempcode('CURRENTLY_AWARDED_TO', ($current_content_title === null) ? do_lang_tempcode('NA_EM') : make_string_tempcode(escape_html($current_content_title)))));
                }

                $fields->attach(form_input_tick(get_translated_text($row['a_title']), $description, 'award_' . strval($row['id']), $has_award));
            }
        }
    }

    if (!$fields->is_empty()) {
        $help = paragraph(do_lang_tempcode('AWARDS_AFTER_VALIDATION'));
        if (get_option('show_docs') == '1') {
            $help_link = do_lang_tempcode('TUTORIAL_ON_THIS', get_tutorial_url('tut_featured'));
            $help->attach(paragraph($help_link));
        }
        $_fields = do_template('FORM_SCREEN_FIELD_SPACER', array(
            '_GUID' => '5b91c53ff3966c13407d33680354fd5d',
            'SECTION_HIDDEN' => (get_param_integer('award', null) === null),
            'TITLE' => do_lang_tempcode('AWARDS'),
            'HELP' => protect_from_escaping($help),
        ));
        $_fields->attach($fields);
        $fields = $_fields;
    }

    return $fields;
}

/**
 * Situation: something that may have awards has just been added/edited. Action: add any specified awards.
 *
 * @param  ID_TEXT $content_type The content type
 * @param  ID_TEXT $id The content ID
 */
function handle_award_setting($content_type, $id)
{
    if (fractional_edit()) {
        return;
    }

    $rows = $GLOBALS['SITE_DB']->query_select('award_types', array('*'), array('a_content_type' => $content_type));

    foreach ($rows as $row) {
        if (has_category_access(get_member(), 'award', strval($row['id']))) {
            $test = $GLOBALS['SITE_DB']->query_select_value_if_there('award_archive', 'content_id', array('a_type_id' => $row['id']), 'ORDER BY date_and_time DESC');
            $has_award = ($test !== null) && ($test === $id);
            $will_have_award = (post_param_integer('award_' . strval($row['id']), 0) == 1);

            if (($will_have_award) && ($has_award)) { // Has to be recached
                delete_cache_entry('main_awards');
            }

            if (($will_have_award) && (!$has_award)) { // Set
                give_award($row['id'], $id);
            } elseif ((!$will_have_award) && ($has_award)) { // Unset
                $GLOBALS['SITE_DB']->query_delete('award_archive', array('a_type_id' => $row['id'], 'content_id' => strval($id)), '', 1);
            } // Otherwise we're happy with the current situation (regardless of whether it is set or unset)
        }
    }
}
