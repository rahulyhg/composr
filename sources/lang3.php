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
 * @package    core
 */

/**
 * UI to choose a language.
 *
 * @param  Tempcode $title Title for the form
 * @param  boolean $tip Whether to give a tip about edit order
 * @param  boolean $allow_all_selection Whether to add an 'all' entry to the list
 * @return mixed The UI (Tempcode) or the language to use (string/LANGUAGE_NAME)
 * @ignore
 */
function _choose_language($title, $tip = false, $allow_all_selection = false)
{
    if (!multi_lang()) {
        return user_lang();
    }

    $lang = either_param_string('lang', /*get_param_string('keep_lang', null)*/
        null);
    if ($lang !== null) {
        return filter_naughty($lang);
    }

    if (!$tip) {
        $text = do_lang_tempcode('CHOOSE_LANG_DESCRIP');
    } else {
        global $LANGS_MAP_CACHE;
        if ($LANGS_MAP_CACHE === null) {
            require_code('files');
            $map_a = get_file_base() . '/lang/langs.ini';
            $map_b = get_custom_file_base() . '/lang_custom/langs.ini';
            if (!is_file($map_b)) {
                $map_b = $map_a;
            }
            $LANGS_MAP_CACHE = better_parse_ini_file($map_b);
        }

        $lang_name = get_site_default_lang();
        if (array_key_exists($lang_name, $LANGS_MAP_CACHE)) {
            $lang_name = $LANGS_MAP_CACHE[$lang_name];
        }

        $text = do_lang_tempcode('CHOOSE_LANG_DESCRIP_ADD_TO_MAIN_LANG_FIRST', escape_html($lang_name));
    }

    $langs = new Tempcode();
    if ($allow_all_selection) {
        $langs->attach(form_input_list_entry('', false, do_lang_tempcode('_ALL')));
    }
    $langs->attach(create_selection_list_langs());
    require_code('form_templates');
    $fields = form_input_list(do_lang_tempcode('LANGUAGE'), do_lang_tempcode('DESCRIPTION_LANGUAGE'), 'lang', $langs, null, true);

    $hidden = build_keep_post_fields();
    $url = get_self_url();

    breadcrumb_set_self(do_lang_tempcode('LANGUAGE'));

    return do_template('FORM_SCREEN', array('_GUID' => '1a2823d450237aa299c095bf9c689a2a', 'SKIP_WEBSTANDARDS' => true, 'HIDDEN' => $hidden, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => do_lang_tempcode('PROCEED'), 'TITLE' => $title, 'FIELDS' => $fields, 'URL' => $url, 'TEXT' => $text));
}

/**
 * Get an array of all the installed languages that can be found in root/lang/ and root/lang_custom/
 *
 * @param  boolean $even_empty_langs Whether to even find empty languages
 * @return array The installed languages (map, lang=>type)
 * @ignore
 */
function _find_all_langs($even_empty_langs = false)
{
    require_code('files');

    // NB: This code is heavily optimised

    $_langs = array(fallback_lang() => 'lang');

    if (!in_safe_mode()) {
        $test = persistent_cache_get('LANGS_LIST');
        if ($test !== null) {
            return $test;
        }

        $_dir = @opendir(get_custom_file_base() . '/lang_custom/');
        if ($_dir !== false) {
            while (false !== ($file = readdir($_dir))) {
                if ((!isset($file[5])) && ($file[0] != '.') && (($file == 'EN') || (!should_ignore_file('lang_custom/' . $file, IGNORE_ACCESS_CONTROLLERS)))) {
                    if (is_dir(get_custom_file_base() . '/lang_custom/' . $file)) {
                        if (($even_empty_langs) || (/*optimisation*/is_file(get_custom_file_base() . '/lang_custom/' . $file . '/global.ini'))) {
                            $_langs[$file] = 'lang_custom';
                        } else {
                            $_dir2 = @opendir(get_custom_file_base() . '/lang_custom/' . $file);
                            if ($_dir2 !== false) {
                                while (false !== ($file2 = readdir($_dir2))) {
                                    if (substr($file2, -4) == '.ini') {
                                        $_langs[$file] = 'lang_custom';
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            closedir($_dir);
        }
        if (get_custom_file_base() != get_file_base()) {
            $_dir = @opendir(get_file_base() . '/lang_custom/');
            if ($_dir !== false) {
                while (false !== ($file = readdir($_dir))) {
                    if ((!isset($file[5])) && ($file[0] != '.') && (($file == 'EN') || (!should_ignore_file('lang_custom/' . $file, IGNORE_ACCESS_CONTROLLERS)))) {
                        if (is_dir(get_file_base() . '/lang_custom/' . $file)) {
                            if ($even_empty_langs) {
                                $_langs[$file] = 'lang_custom';
                            } else {
                                $_dir2 = opendir(get_file_base() . '/lang_custom/' . $file);
                                while (false !== ($file2 = readdir($_dir2))) {
                                    if (substr($file2, -4) == '.ini') {
                                        $_langs[$file] = 'lang_custom';
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                closedir($_dir);
            }
        }
    }
    $_dir = @opendir(get_file_base() . '/lang/');
    if ($_dir !== false) {
        while (false !== ($file = readdir($_dir))) {
            if ((!isset($_langs[$file])) && ($file[0] != '.') && (!isset($file[5])) && (($file == 'EN') || (!should_ignore_file('lang/' . $file, IGNORE_ACCESS_CONTROLLERS)))) {
                if (is_dir(get_file_base() . '/lang/' . $file)) {
                    $_langs[$file] = 'lang';
                }
            }
        }
        closedir($_dir);
    }

    if (!in_safe_mode()) {
        persistent_cache_set('LANGS_LIST', $_langs);
    }

    return $_langs;
}

/**
 * Get a nice formatted XHTML listed language selector.
 *
 * @param  ?LANGUAGE_NAME $select_lang The language to have selected by default (null: uses the current language)
 * @param  boolean $show_unset Whether to show languages that have no language details currently defined for them
 * @return Tempcode The language selector
 *
 * @ignore
 */
function _create_selection_list_langs($select_lang = null, $show_unset = false)
{
    $langs = new Tempcode();
    $_langs = find_all_langs();

    if ($select_lang === null) {
        $select_lang = user_lang();
    }

    require_code('lang2');

    foreach (array_keys($_langs) as $lang) {
        $langs->attach(form_input_list_entry($lang, ($lang == $select_lang), lookup_language_full_name($lang)));
    }

    if ($show_unset) {
        global $LANGS_MAP_CACHE;
        if (!is_null($LANGS_MAP_CACHE)) {
            asort($LANGS_MAP_CACHE);
            foreach ($LANGS_MAP_CACHE as $lang => $full) {
                if (!array_key_exists($lang, $_langs)) {
                    $_full = make_string_tempcode($full);
                    $_full->attach(do_lang_tempcode('_UNSET'));
                    $langs->attach(form_input_list_entry($lang, false, protect_from_escaping($_full)));
                }
            }
        }
    }

    return $langs;
}

/**
 * Take a .ini language string and save it into a translated language string in the database, for all translations.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  ID_TEXT $code The language string codename
 * @param  boolean $comcode Whether the given codes value is to be parsed as Comcode
 * @param  integer $level The level of importance this language string holds
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @return array The language string ID save fields
 */
function lang_code_to_default_content($field_name, $code, $comcode = false, $level = 2, $connection = null)
{
    $insert_map = insert_lang($field_name, do_lang($code), $level, null, $comcode);
    if (multi_lang_content()) {
        $langs = find_all_langs();
        foreach ($langs as $lang => $lang_type) {
            if ($lang != user_lang()) {
                if (is_file(get_file_base() . '/' . $lang_type . '/' . $lang . '/critical_error.ini')) { // Make sure it's a reasonable looking pack, not just a stub (Google Translate addon can be made to go nuts otherwise)
                    insert_lang($field_name, do_lang($code, '', '', '', $lang), $level, $connection, true, $insert_map[$field_name], $lang);
                }
            }
        }
    }
    return $insert_map;
}

/**
 * Take a static string and save it into a translated language string in the database, for all translations.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  ID_TEXT $str The static string
 * @param  boolean $comcode Whether the given codes value is to be parsed as Comcode
 * @param  integer $level The level of importance this language string holds
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @return array The language string ID save fields
 */
function lang_code_to_static_content($field_name, $str, $comcode = false, $level = 2, $connection = null)
{
    $insert_map = insert_lang($field_name, $str, $level, $connection, $comcode);
    if (multi_lang_content()) {
        $langs = find_all_langs();
        foreach ($langs as $lang => $lang_type) {
            if ($lang != user_lang()) {
                if (is_file(get_file_base() . '/' . $lang_type . '/' . $lang . '/critical_error.ini')) { // Make sure it's a reasonable looking pack, not just a stub
                    insert_lang($field_name, $str, $level, $connection, $comcode, $insert_map[$field_name], $lang);
                }
            }
        }
    }
    return $insert_map;
}

/**
 * Insert a language string into the translation table, and returns the ID.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  string $text The text
 * @param  integer $level The level of importance this language string holds
 * @set    1 2 3 4
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @param  boolean $comcode Whether it is to be parsed as Comcode
 * @param  ?integer $id The ID to use for the language string (null: work out next available)
 * @param  ?LANGUAGE_NAME $lang The language (null: uses the current language)
 * @param  boolean $insert_as_admin Whether to insert it as an admin (any Comcode parsing will be carried out with admin privileges)
 * @param  ?string $pass_id The special identifier for this language string on the page it will be displayed on; this is used to provide an explicit binding between languaged elements and greater templated areas (null: none)
 * @param  ?string $text_parsed Assembled Tempcode portion (null: work it out)
 * @param  ?integer $wrap_pos Comcode parser wrap position (null: no wrapping)
 * @param  boolean $preparse_mode Whether to generate a fatal error if there is invalid Comcode
 * @param  boolean $save_as_volatile Whether we are saving as a 'volatile' file extension (used in the XML DB driver, to mark things as being non-syndicated to subversion)
 * @return array The language string ID save fields
 *
 * @ignore
 */
function _insert_lang($field_name, $text, $level, $connection = null, $comcode = false, $id = null, $lang = null, $insert_as_admin = false, $pass_id = null, $text_parsed = null, $wrap_pos = null, $preparse_mode = true, $save_as_volatile = false)
{
    if ($connection === null) {
        $connection = $GLOBALS['SITE_DB'];
    }

    if ($lang === null) {
        $lang = user_lang();
    }
    $_text_parsed = null;

    if ($comcode && !get_mass_import_mode()) {
        if ($text_parsed === null) {
            if ((function_exists('get_member')) && (!$insert_as_admin)) {
                $member_id = get_member();
            } else {
                $member_id = is_object($GLOBALS['FORUM_DRIVER']) ? $GLOBALS['FORUM_DRIVER']->get_guest_id() : 0;
                $insert_as_admin = true;
            }
            require_code('comcode');
            $_text_parsed = comcode_to_tempcode($text, $member_id, $insert_as_admin, $wrap_pos, $pass_id, $connection, false, $preparse_mode);
            $text_parsed = $_text_parsed->to_assembly();
        }
    } else {
        $text_parsed = '';
    }

    $source_user = (function_exists('get_member')) ? get_member() : $GLOBALS['FORUM_DRIVER']->get_guest_id();

    if (!multi_lang_content()) {
        $ret = array();
        $ret[$field_name] = $text;
        if ($comcode) {
            $ret[$field_name . '__text_parsed'] = $text_parsed;
            $ret[$field_name . '__source_user'] = $source_user;
        }
        return $ret;
    }

    if (($id === null) && (multi_lang())) { // Needed as MySQL auto-increment works separately for each combo of other key values (i.e. language in this case). We can't let a language string ID get assigned to something entirely different in another language. This MySQL behaviour is not well documented, it may work differently on different versions.
        $connection->query('LOCK TABLES ' . $connection->get_table_prefix() . 'translate', null, null, true);
        $lock = true;
        $id = $connection->query_select_value('translate', 'MAX(id)');
        $id = ($id === null) ? null : ($id + 1);
    } else {
        $lock = false;
    }

    if ($lang == 'Gibb') { // Debug code to help us spot language layer bugs. We expect &keep_lang=EN to show EnglishEnglish content, but otherwise no EnglishEnglish content.
        if ($id === null) {
            $id = $connection->query_insert('translate', array('source_user' => $source_user, 'broken' => 0, 'importance_level' => $level, 'text_original' => 'EnglishEnglishWarningWrongLanguageWantGibberishLang', 'text_parsed' => '', 'language' => 'EN'), true, false, $save_as_volatile);
        } else {
            $connection->query_insert('translate', array('id' => $id, 'source_user' => $source_user, 'broken' => 0, 'importance_level' => $level, 'text_original' => 'EnglishEnglishWarningWrongLanguageWantGibberishLang', 'text_parsed' => '', 'language' => 'EN'), false, false, $save_as_volatile);
        }
    }
    if (($id === null) || ($id === 0)) { //==0 because unless MySQL NO_AUTO_VALUE_ON_ZERO is on, 0 insertion is same as null is same as "use autoincrement"
        $id = $connection->query_insert('translate', array('source_user' => $source_user, 'broken' => 0, 'importance_level' => $level, 'text_original' => $text, 'text_parsed' => $text_parsed, 'language' => $lang), true, false, $save_as_volatile);
    } else {
        $connection->query_insert('translate', array('id' => $id, 'source_user' => $source_user, 'broken' => 0, 'importance_level' => $level, 'text_original' => $text, 'text_parsed' => $text_parsed, 'language' => $lang), false, false, $save_as_volatile);
    }

    if ($lock) {
        $connection->query('UNLOCK TABLES', null, null, true);
    }

    if (count($connection->text_lookup_cache) < 5000) {
        if ($_text_parsed !== null) {
            $connection->text_lookup_cache[$id] = $_text_parsed;
        } else {
            $connection->text_lookup_original_cache[$id] = $text;
        }
    }

    return array(
        $field_name => $id
    );
}

/**
 * Remap the specified language string ID, and return the ID again - the ID isn't changed.
 *
 * @param  ID_TEXT $field_name The field name
 * @param  mixed $id The ID (if multi-lang-content on), or the string itself
 * @param  string $text The text to remap to
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @param  boolean $comcode Whether it is to be parsed as Comcode
 * @param  ?string $pass_id The special identifier for this language string on the page it will be displayed on; this is used to provide an explicit binding between languaged elements and greater templated areas (null: none)
 * @param  ?MEMBER $for_member The member that owns the content this is for (null: current member)
 * @param  boolean $as_admin Whether to generate Comcode as arbitrary admin
 * @param  boolean $leave_source_user Whether to leave the source member as-is (as opposed to resetting it to the current member)
 * @return array The language string ID save fields
 *
 * @ignore
 */
function _lang_remap($field_name, $id, $text, $connection = null, $comcode = false, $pass_id = null, $for_member = null, $as_admin = false, $leave_source_user = false)
{
    if ($id === 0) {
        return insert_lang($field_name, $text, 3, $connection, $comcode, null, null, $as_admin, $pass_id);
    }

    if ($text === STRING_MAGIC_NULL) {
        return array(
            $field_name => $id
        );
    }

    if ($connection === null) {
        $connection = $GLOBALS['SITE_DB'];
    }

    $lang = user_lang();

    $member_id = (function_exists('get_member')) ? get_member() : $GLOBALS['FORUM_DRIVER']->get_guest_id(); // This updates the Comcode reference to match the current user, which may not be the owner of the content this is for. This is for a reason - we need to parse with the security token of the current user, not the original content submitter.
    if (($for_member === null) || ($GLOBALS['FORUM_DRIVER']->get_username($for_member) === null)) {
        $for_member = $member_id;
    }

    if ($leave_source_user) {
        $source_user = null;
    } else {
        /*
        We set the Comcode user to the editing user (not the content owner) if the editing user does not have full HTML/Dangerous-Comcode privileges.
        The Comcode user is set to the content owner if the editing user does have those privileges (which is the idealised, consistent state).
        This is necessary as editing admin's content shouldn't let you write content with admin's privileges, even if you have privilege to edit their content
         - yet also, if the source_user is changed, when admin edits it has to change back again.
        */
        if ((function_exists('cms_admirecookie')) && ((cms_admirecookie('use_wysiwyg', '1') == '0') && (get_value('edit_with_my_comcode_perms') === '1')) || (!has_privilege($member_id, 'allow_html')) || (!has_privilege($member_id, 'comcode_dangerous')) || (!has_privilege($member_id, 'use_very_dangerous_comcode'))) {
            $source_user = $member_id;
        } else {
            $source_user = $for_member; // Reset to latest submitter for main record
        }
    }

    if ($comcode) {
        $_text_parsed = comcode_to_tempcode($text, ($source_user === null) ? $for_member : $source_user, $as_admin, null, $pass_id, $connection);
        $connection->text_lookup_cache[$id] = $_text_parsed;
        $text_parsed = $_text_parsed->to_assembly();
    } else {
        $text_parsed = '';
    }

    if (!multi_lang_content()) {
        $ret = array();
        $ret[$field_name] = $text;
        if ($comcode) {
            $ret[$field_name . '__text_parsed'] = $text_parsed;
            if ($source_user !== null) {
                $ret[$field_name . '__source_user'] = $source_user;
            }
        }
        return $ret;
    }

    $test = $connection->query_select_value_if_there('translate', 'text_original', array('id' => $id, 'language' => $lang));

    // Mark old as out-of-date
    if ($test !== $text) {
        $GLOBALS['SITE_DB']->query_update('translate', array('broken' => 1), array('id' => $id));
    }

    $remap = array(
        'broken' => 0,
        'text_original' => $text,
        'text_parsed' => $text_parsed,
    );
    if ($source_user !== null) {
        $remap['source_user'] = $source_user;
    }

    if ($test !== null) { // Good, we save into our own language, as we have a translation for the lang entry setup properly
        $connection->query_update('translate', $remap, array('id' => $id, 'language' => $lang), '', 1);
    } else { // Darn, we'll have to save over whatever we did load from
        $connection->query_update('translate', $remap, array('id' => $id), '', 1);
    }

    $connection->text_lookup_original_cache[$id] = $text;

    return array(
        $field_name => $id
    );
}

/**
 * get_translated_tempcode was asked for a lang entry that had not been parsed into Tempcode yet.
 *
 * @param  ID_TEXT $table The table name
 * @param  array $row The database row
 * @param  ID_TEXT $field_name The field name
 * @param  ?object $connection The database connection to use (null: standard site connection)
 * @param  ?LANGUAGE_NAME $lang The language (null: uses the current language)
 * @param  boolean $force Whether to force it to the specified language
 * @param  boolean $as_admin Whether to force as_admin, even if the language string isn't stored against an admin (designed for Comcode page caching)
 * @return ?Tempcode The parsed Comcode (null: the text couldn't be looked up)
 */
function parse_translated_text($table, &$row, $field_name, $connection, $lang, $force, $as_admin)
{
    global $SEARCH__CONTENT_BITS, $LAX_COMCODE;

    $nql_backup = $GLOBALS['NO_QUERY_LIMIT'];
    $GLOBALS['NO_QUERY_LIMIT'] = true;

    $entry = $row[$field_name];

    $result = mixed();
    if (multi_lang_content()) {
        $_result = $connection->query_select('translate', array('text_original', 'source_user'), array('id' => $entry, 'language' => $lang), '', 1);
        if (array_key_exists(0, $_result)) {
            $result = $_result[0];
        }

        if ($result === null) { // A missing translation
            if ($force) {
                $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
                return null;
            }

            $result = $connection->query_select_value_if_there('translate', 'text_parsed', array('id' => $entry, 'language' => get_site_default_lang()));
            if ($result === null) {
                $result = $connection->query_select_value_if_there('translate', 'text_parsed', array('id' => $entry));
            }

            if (($result !== null) && ($result != '')) {
                $connection->text_lookup_cache[$entry] = new Tempcode();
                if (!$connection->text_lookup_cache[$entry]->from_assembly($result, true)) {
                    $result = null;
                }
            }

            if (($result === null) || ($result == '')) {
                require_code('comcode'); // might not have been loaded for a quick-boot
                require_code('permissions');

                $result = $connection->query_select('translate', array('text_original', 'source_user'), array('id' => $entry, 'language' => get_site_default_lang()), '', 1);
                if (!array_key_exists(0, $result)) {
                    $result = $connection->query_select('translate', array('text_original', 'source_user'), array('id' => $entry), '', 1);
                }
                $result = array_key_exists(0, $result) ? $result[0] : null;

                $temp = $LAX_COMCODE;
                $LAX_COMCODE = true;
                _lang_remap($field_name, $entry, ($result === null) ? '' : $result['text_original'], $connection, true, null, $result['source_user'], $as_admin, true);
                if ($SEARCH__CONTENT_BITS !== null) {
                    $ret = comcode_to_tempcode($result['text_original'], $result['source_user'], $as_admin, null, null, $connection, false, false, false, false, false, $SEARCH__CONTENT_BITS);
                    $LAX_COMCODE = $temp;
                    $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
                    return $ret;
                }
                $LAX_COMCODE = $temp;
                $ret = get_translated_tempcode($table, $row, $field_name, $connection, $lang);
                $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
                return $ret;
            }

            $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
            return $connection->text_lookup_cache[$entry];
        }
    }

    // Missing parsed Comcode...

    require_code('comcode'); // might not have been loaded for a quick-boot
    require_code('permissions');

    $temp = $LAX_COMCODE;
    $LAX_COMCODE = true;

    if (multi_lang_content()) {
        _lang_remap($field_name, $entry, $result['text_original'], $connection, true, null, $result['source_user'], $as_admin, true);

        if ($SEARCH__CONTENT_BITS !== null) {
            $ret = comcode_to_tempcode($result['text_original'], $result['source_user'], $as_admin, null, null, $connection, false, false, false, false, false, $SEARCH__CONTENT_BITS);
            $LAX_COMCODE = $temp;
            $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
            return $ret;
        }
    } else {
        $map = _lang_remap($field_name, $entry, $row[$field_name], $connection, true, null, $row[$field_name . '__source_user'], $as_admin, true);

        $connection->query_update($table, $map, $row, '', 1);
        $row = $map + $row;

        if ($SEARCH__CONTENT_BITS !== null) {
            $ret = comcode_to_tempcode($row[$field_name], $row[$field_name . '__source_user'], $as_admin, null, null, $connection, false, false, false, false, false, $SEARCH__CONTENT_BITS);
            $LAX_COMCODE = $temp;
            $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
            return $ret;
        }
    }

    $LAX_COMCODE = $temp;
    $ret = get_translated_tempcode($table, $row, $field_name, $connection, $lang, false, false, false, true);
    $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;
    return $ret;
}

/**
 * Convert a language string that is Comcode to Tempcode, with potential caching in the db.
 *
 * @param  ID_TEXT $lang_code The language string ID
 * @return Tempcode The parsed Comcode
 *
 * @ignore
 */
function _comcode_lang_string($lang_code)
{
    global $COMCODE_LANG_STRING_CACHE;
    if (array_key_exists($lang_code, $COMCODE_LANG_STRING_CACHE)) {
        return $COMCODE_LANG_STRING_CACHE[$lang_code];
    }

    if (multi_lang_content()) {
        $comcode_page = $GLOBALS['SITE_DB']->query_select('cached_comcode_pages p LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'translate t ON t.id=string_index AND ' . db_string_equal_to('t.language', user_lang()), array('string_index', 'text_parsed', 'source_user'), array('the_page' => $lang_code, 'the_zone' => '!'), '', 1);
        if ((array_key_exists(0, $comcode_page)) && (!is_browser_decaching())) {
            $comcode_page_row_cached_only = array(
                'the_zone' => '!',
                'the_page' => $lang_code,
                'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme(),
                'string_index' => $comcode_page[0]['string_index'],
                'string_index__text_parsed' => $comcode_page[0]['text_parsed'],
                'string_index__source_user' => $comcode_page[0]['source_user'],
            );
            if (($comcode_page[0]['text_parsed'] !== null) && ($comcode_page[0]['text_parsed'] != '')) {
                $parsed = new Tempcode();
                if (!$parsed->from_assembly($comcode_page[0]['text_parsed'], true)) {
                    $ret = get_translated_tempcode('cached_comcode_pages', $comcode_page_row_cached_only, 'string_index');
                    unset($GLOBALS['RECORDED_LANG_STRINGS_CONTENT'][$comcode_page[0]['string_index']]);
                }
            } else {
                $ret = get_translated_tempcode('cached_comcode_pages', $comcode_page_row_cached_only, 'string_index', null, null, true);
                if ($ret === null) { // Not existent in our language, we'll need to lookup and insert, and get again
                    $looked_up = do_lang($lang_code, null, null, null, null, false);
                    if ($looked_up === null) {
                        return make_string_tempcode(escape_html('{!' . $lang_code . '}'));
                    }
                    $GLOBALS['SITE_DB']->query_insert('translate', array('id' => $comcode_page[0]['string_index'], 'source_user' => get_member(), 'broken' => 0, 'importance_level' => 1, 'text_original' => $looked_up, 'text_parsed' => '', 'language' => user_lang()), true, false, true);
                    $ret = get_translated_tempcode('cached_comcode_pages', $comcode_page_row_cached_only, 'string_index');
                }
                unset($GLOBALS['RECORDED_LANG_STRINGS_CONTENT'][$comcode_page[0]['string_index']]);
                return $ret;
            }
            $COMCODE_LANG_STRING_CACHE[$lang_code] = $parsed;
            return $parsed;
        } elseif (array_key_exists(0, $comcode_page)) {
            $GLOBALS['SITE_DB']->query_delete('cached_comcode_pages', array('the_page' => $lang_code, 'the_zone' => '!'));
            delete_lang($comcode_page[0]['string_index']);
        }
    } else {
        $comcode_page = $GLOBALS['SITE_DB']->query_select('cached_comcode_pages', array('*'), array('the_page' => $lang_code, 'the_zone' => '!'), '', 1);
        if ((array_key_exists(0, $comcode_page)) && (!is_browser_decaching())) {
            $ret = get_translated_tempcode('cached_comcode_pages', $comcode_page[0], 'string_index');
            $COMCODE_LANG_STRING_CACHE[$lang_code] = $ret;
            return $ret;
        } elseif (array_key_exists(0, $comcode_page)) {
            $GLOBALS['SITE_DB']->query_delete('cached_comcode_pages', array('the_page' => $lang_code, 'the_zone' => '!'));
        }
    }

    $nql_backup = $GLOBALS['NO_QUERY_LIMIT'];
    $GLOBALS['NO_QUERY_LIMIT'] = true;
    $looked_up = do_lang($lang_code, null, null, null, null, false);
    if ($looked_up === null) {
        return make_string_tempcode(escape_html('{!' . $lang_code . '}'));
    }
    $map = array(
        'the_zone' => '!',
        'the_page' => $lang_code,
        'the_theme' => $GLOBALS['FORUM_DRIVER']->get_theme(),
        'cc_page_title' => multi_lang_content() ? null : '',
    );
    $map += insert_lang_comcode('string_index', $looked_up, 4, null, true, null, null, false, true);
    $GLOBALS['SITE_DB']->query_insert('cached_comcode_pages', $map, false, true); // Race conditions
    $parsed = get_translated_tempcode('cached_comcode_pages', $map, 'string_index');
    $COMCODE_LANG_STRING_CACHE[$lang_code] = $parsed;

    $GLOBALS['NO_QUERY_LIMIT'] = $nql_backup;

    return $parsed;
}
