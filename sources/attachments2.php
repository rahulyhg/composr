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
 * @package    core_rich_media
 */

/*
Adding attachments.
(Editing/deleting is in attachments3.php)
*/

/**
 * Get an array containing new Comcode, and Tempcode. The function wraps the normal comcode_to_tempcode function. The function will do attachment management, including deleting of attachments that have become unused due to editing of some Comcode and removing of the reference.
 *
 * @param  LONG_TEXT $comcode The unparsed Comcode that references the attachments
 * @param  ID_TEXT $type The type the attachment will be used for (e.g. download)
 * @param  ID_TEXT $id The ID the attachment will be used for
 * @param  boolean $previewing_only Whether we are only previewing the attachments (i.e. don't store them!)
 * @param  ?object $db The database connector to use (null: standard site connector)
 * @param  ?boolean $insert_as_admin Whether to insert it as an admin (any Comcode parsing will be carried out with admin privileges) (null: autodetect)
 * @param  ?MEMBER $for_member The member to use for ownership permissions (null: current member)
 * @return array A map containing 'Comcode' (after substitution for tying down the new attachments) and 'tempcode'
 */
function do_comcode_attachments($comcode, $type, $id, $previewing_only = false, $db = null, $insert_as_admin = null, $for_member = null)
{
    require_lang('comcode');
    require_code('comcode_compiler');

    if (php_function_allowed('set_time_limit')) {
        @set_time_limit(600); // Thumbnail generation etc can take some time
    }

    global $COMCODE_ATTACHMENTS;
    unset($COMCODE_ATTACHMENTS[$id]); // In case we have some kind of conflict

    if ($db === null) {
        $db = $GLOBALS['SITE_DB'];
    }

    if ($for_member !== null) {
        $member_id = $for_member;
    } else {
        $member_id = function_exists('get_member') ? get_member() : db_get_first_id();
    }
    if ($insert_as_admin === null) {
        $insert_as_admin = false;
    }

    // Handle data URLs for attachment embedding
    _handle_data_url_attachments($comcode, $type, $id, $db);

    // Find out about attachments already involving this content
    global $ATTACHMENTS_ALREADY_REFERENCED;
    $old_already = $ATTACHMENTS_ALREADY_REFERENCED;
    $ATTACHMENTS_ALREADY_REFERENCED = array();
    $before = $db->query_select('attachment_refs', array('a_id', 'id'), array('r_referer_type' => $type, 'r_referer_id' => $id));
    foreach ($before as $ref) {
        $ATTACHMENTS_ALREADY_REFERENCED[$ref['a_id']] = 1;
    }

    // Find if we have an attachment(s), and tidy up the Comcode enough to handle the attachment Comcode properly
    $has_one = false;
    $may_have_one = false;
    foreach ($_POST as $key => $value) {
        if (is_string($key) && preg_match('#^hid_file_id_#i', $key) != 0) {
            require_code('uploads');
            $may_have_one = is_plupload();
        }
    }
    if ($may_have_one) {
        require_code('uploads');
        is_plupload(true);

        require_code('comcode_from_html');
        remove_wysiwyg_comcode_markup($comcode);
    }

    // Go through all uploaded attachment files
    foreach ($_FILES as $key => $file) {
        $matches = array();
        if ((($may_have_one) && (is_plupload()) || (is_uploaded_file($file['tmp_name']))) && (preg_match('#file(\d+)#', $key, $matches) != 0)) {
            $has_one = true;

            $matches_extract = array();
            if ((!browser_matches('simplified_attachments_ui')) && (strpos($comcode, ']new_' . $matches[1] . '[/attachment]') === false) && (strpos($comcode, ']new_' . $matches[1] . '[/attachment_safe]') === false)) {
                if (preg_match('#\]\d+\[/attachment\]#', $comcode) == 0) { // Attachment could have already been put through (e.g. during a preview). If we have actual ID's referenced, it's almost certainly the case.
                    $comcode .= "\n\n" . '[attachment]new_' . $matches[1] . '[/attachment]';
                }
            }
        }
    }

    // Parse the Comcode to find details of attachments (and add into the database)
    if ($has_one) {
        push_lax_comcode(true); // We don't want a simple syntax error to cause us to lose our attachments
    }
    $tempcode = comcode_to_tempcode($comcode, $member_id, $insert_as_admin, $id, $db, COMCODE_NORMAL, array(), $for_member);
    if ($has_one) {
        pop_lax_comcode();
    }
    $ATTACHMENTS_ALREADY_REFERENCED = $old_already;
    if (!array_key_exists($id, $COMCODE_ATTACHMENTS)) {
        $COMCODE_ATTACHMENTS[$id] = array();
    }

    // Also the WYSIWYG-edited ones, which the Comcode parser won't find
    $matches = array();
    $num_matches = preg_match_all('#attachment.php\?id=(\d+)#', $comcode, $matches);
    for ($i = 0; $i < $num_matches; $i++) {
        $COMCODE_ATTACHMENTS[$id][] = array('tag_type' => null, 'time' => time(), 'type' => 'existing', 'initial_id' => null, 'id' => $matches[1][$i], 'attachmenttype' => '', 'comcode' => null);
    }

    // Put in our new attachment IDs (replacing the new_* markers)
    $ids_present = array();
    for ($i = 0; $i < count($COMCODE_ATTACHMENTS[$id]); $i++) {
        $attachment = $COMCODE_ATTACHMENTS[$id][$i];

        if ($attachment['initial_id'] !== null) {
            // If it's a new one, we need to change the comcode to reference the ID we made for it
            if ($attachment['type'] == 'new') {
                $marker_id = intval(substr($attachment['initial_id'], 4)); // After 'new_'

                $comcode = preg_replace('#(\[(attachment|attachment_safe)[^\]]*\])new_' . strval($marker_id) . '(\[/)#', '${1}' . strval($attachment['id']) . '${3}', $comcode);

                if ($type !== null) {
                    $db->query_insert('attachment_refs', array('r_referer_type' => $type, 'r_referer_id' => $id, 'a_id' => $attachment['id']));
                }
            } else {
                // (Re-)Reference it
                $db->query_delete('attachment_refs', array('r_referer_type' => $type, 'r_referer_id' => $id, 'a_id' => $attachment['id']), '', 1);
                $db->query_insert('attachment_refs', array('r_referer_type' => $type, 'r_referer_id' => $id, 'a_id' => $attachment['id']));
            }
        }

        $ids_present[] = $attachment['id'];
    }
    // Tidy out any attachment references to files that clearly are not here
    $comcode = preg_replace('#\[(attachment|attachment_safe)[^\]]*\]new_\d+\[/(attachment|attachment_safe)\]#', '', $comcode);

    if ((!$previewing_only) && (get_option('attachment_cleanup') == '1')) {
        // Clear any de-referenced attachments
        foreach ($before as $ref) {
            if ((!in_array($ref['a_id'], $ids_present)) && (strpos($comcode, 'attachment.php?id=') === false) && (!multi_lang())) {
                // Delete reference (as it's not actually in the new comcode!)
                $db->query_delete('attachment_refs', array('id' => $ref['id']), '', 1);

                // Was that the last reference to this attachment? (if so -- delete attachment)
                $test = $db->query_select_value_if_there('attachment_refs', 'id', array('a_id' => $ref['a_id']));
                if ($test === null) {
                    require_code('attachments3');
                    _delete_attachment($ref['a_id'], $db);
                }
            }
        }
    }

    return array(
        'comcode' => $comcode,
        'tempcode' => $tempcode,
    );
}

/**
 * Convert attachments embedded as data URLs (usually the result of pasting in) to real attachment Comcode.
 *
 * @param  string $comcode Our Comcode
 * @param  ID_TEXT $type The type the attachment will be used for (e.g. download)
 * @param  ID_TEXT $id The ID the attachment will be used for
 * @param  object $db The database connector to use
 *
 * @ignore
 */
function _handle_data_url_attachments(&$comcode, $type, $id, $db)
{
    if (substr($comcode, 0, 6) == '[html]') {
        return; // The whole thing is probably [html]. Probably came from WYSIWYG. We can't do the "data:" conversion during semihtml_to_comcode as that doesn't know $type and $id. And would be a bad idea to re-parse [html] context here.
    }

    if (function_exists('imagepng')) {
        $matches = array();
        $matches2 = array();
        $num_matches = preg_match_all('#<img[^<>]*src="data:image/\w+;base64,([^"]*)"[^<>]*>#', $comcode, $matches);
        $num_matches2 = preg_match_all('#\[img[^\[\]]*\]data:image/\w+;base64,([^"]*)\[/img\]#', $comcode, $matches2);
        for ($i = 0; $i < $num_matches2; $i++) {
            $matches[0][$num_matches] = $matches2[0][$i];
            $matches[1][$num_matches] = $matches2[1][$i];
            $num_matches++;
        }
        for ($i = 0; $i < $num_matches; $i++) {
            if (strpos($comcode, $matches[0][$i]) !== false) { // Check still here (if we have same image in multiple places, may have already been attachment-ified)
                $data = @base64_decode($matches[1][$i]);
                if (($data !== false) && (function_exists('imagepng'))) {
                    $image = @imagecreatefromstring($data);
                    if ($image !== false) {
                        require_code('urls2');
                        list($new_path, $new_url, $new_filename) = find_unique_path('uploads/attachments', null, true);
                        imagepng($image, $new_path, 9);
                        imagedestroy($image);

                        fix_permissions($new_path);
                        sync_file($new_path);

                        require_code('uploads');
                        $test = handle_upload_post_processing(CMS_UPLOAD_IMAGE, $new_path, 'uploads/attachments', $new_filename, 0);
                        if ($test !== null) {
                            unlink($new_path);
                            sync_file($new_path);

                            $new_url = $test;
                        }

                        $db = $GLOBALS[((substr($type, 0, 4) == 'cns_') && (get_forum_type() == 'cns')) ? 'FORUM_DB' : 'SITE_DB'];
                        $attachment_id = $db->query_insert('attachments', array(
                            'a_member_id' => get_member(),
                            'a_file_size' => strlen($data),
                            'a_url' => $new_url,
                            'a_thumb_url' => '',
                            'a_original_filename' => basename($new_filename),
                            'a_num_downloads' => 0,
                            'a_last_downloaded_time' => time(),
                            'a_description' => '',
                            'a_add_time' => time(),
                        ), true);
                        $db->query_insert('attachment_refs', array('r_referer_type' => $type, 'r_referer_id' => $id, 'a_id' => $attachment_id));

                        $comcode = str_replace($matches[0][$i], '[attachment framed="0" thumb="0"]' . strval($attachment_id) . '[/attachment]', $comcode);
                    }
                }
            }
        }
    }
}

/**
 * Check that not too many attachments have been uploaded for the member submitting.
 *
 * @ignore
 */
function _check_attachment_count()
{
    if ((get_forum_type() == 'cns') && (function_exists('get_member'))) {
        require_code('cns_groups');
        require_lang('cns');
        require_lang('comcode');
        $max_attachments_per_post = cns_get_member_best_group_property(get_member(), 'max_attachments_per_post');

        $may_have_one = false;
        foreach ($_POST as $key => $value) {
            if (is_string($key) && preg_match('#^hid_file_id_#i', $key) != 0) {
                require_code('uploads');
                $may_have_one = is_plupload();
            }
        }
        if ($may_have_one) {
            require_code('uploads');
            is_plupload(true);
        }
        foreach (array_keys($_FILES) as $name) {
            if ((substr($name, 0, 4) == 'file') && (is_numeric(substr($name, 4)) && ($_FILES[$name]['tmp_name'] != ''))) {
                $max_attachments_per_post--;
            }
        }

        if ($max_attachments_per_post < 0) {
            warn_exit(do_lang_tempcode('TOO_MANY_ATTACHMENTS'));
        }
    }
}

/**
 * Insert some Comcode content that may contain attachments, and return the language string ID.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  integer $level The level of importance this language string holds
 * @set    1 2 3 4
 * @param  LONG_TEXT $text The Comcode content
 * @param  ID_TEXT $type The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT $id The ID in the set of the arbitrary types that the attached is for
 * @param  ?object $db The database connector to use (null: standard site connector)
 * @param  boolean $insert_as_admin Whether to insert it as an admin (any Comcode parsing will be carried out with admin privileges)
 * @param  ?MEMBER $for_member The member to use for ownership permissions (null: current member)
 * @return array The language string ID save fields
 */
function insert_lang_comcode_attachments($field_name, $level, $text, $type, $id, $db = null, $insert_as_admin = false, $for_member = null)
{
    if ($db === null) {
        $db = $GLOBALS['SITE_DB'];
    }

    require_lang('comcode');

    _check_attachment_count();

    $_info = do_comcode_attachments($text, $type, $id, false, $db, $insert_as_admin, $for_member);
    $text_parsed = $_info['tempcode']->to_assembly();

    if ($for_member === null) {
        $source_user = (function_exists('get_member')) ? get_member() : $GLOBALS['FORUM_DRIVER']->get_guest_id();
    } else {
        $source_user = $for_member;
    }

    if (!multi_lang_content()) {
        final_attachments_from_preview($id, $db);

        $ret = array();
        $ret[$field_name] = $_info['comcode'];
        $ret[$field_name . '__text_parsed'] = $text_parsed;
        $ret[$field_name . '__source_user'] = $source_user;
        return $ret;
    }

    $lang_id = null;
    $lock = false;
    table_id_locking_start($db, $lang_id, $lock);

    if (user_lang() == 'Gibb') { // Debug code to help us spot language layer bugs. We expect &keep_lang=EN to show EnglishEnglish content, but otherwise no EnglishEnglish content.
        $map = array(
            'source_user' => $source_user,
            'broken' => 0,
            'importance_level' => $level,
            'text_original' => 'EnglishEnglishWarningWrongLanguageWantGibberishLang',
            'text_parsed' => '',
            'language' => 'EN',
        );
        if ($lang_id === null) {
            $lang_id = $db->query_insert('translate', $map, true);
        } else {
            $db->query_insert('translate', array('id' => $lang_id) + $map);
        }
    }

    $map = array(
        'source_user' => $source_user,
        'broken' => 0,
        'importance_level' => $level,
        'text_original' => $_info['comcode'],
        'text_parsed' => $text_parsed,
        'language' => user_lang(),
    );
    if ($lang_id === null) {
        $lang_id = $db->query_insert('translate', $map, true);
    } else {
        $db->query_insert('translate', array('id' => $lang_id) + $map);
    }

    table_id_locking_end($db, $lang_id, $lock);

    final_attachments_from_preview($id, $db);

    return array(
        $field_name => $lang_id,
    );
}

/**
 * Finalise attachments which were created during a preview, so that they have the proper reference IDs.
 *
 * @param  ID_TEXT $id The ID in the set of the arbitrary types that the attached is for
 * @param  ?object $db The database connector to use (null: standard site connector)
 */
function final_attachments_from_preview($id, $db = null)
{
    if ($db === null) {
        $db = $GLOBALS['SITE_DB'];
    }

    // Clean up the any attachments added at the preview stage
    $posting_ref_id = post_param_integer('posting_ref_id', null);
    if ($posting_ref_id < 0) {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }
    if ($posting_ref_id !== null) {
        $db->query_delete('attachment_refs', array('r_referer_type' => 'null', 'r_referer_id' => strval(-$posting_ref_id)), '', 1);
        $db->query_delete('attachment_refs', array('r_referer_id' => strval(-$posting_ref_id))); // Can trash this, was made during preview but we made a new one in do_comcode_attachments (recalled by insert_lang_comcode_attachments)
    }
}
