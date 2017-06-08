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
 * Change whatever global context that is required in order to run from a different context.
 *
 * @sets_input_state
 *
 * @param  array $new_get The URL component map (must contain 'page').
 * @param  ID_TEXT $new_zone The zone.
 * @param  ID_TEXT $new_current_script The running script.
 * @param  boolean $erase_keep_also Whether to get rid of keep_ variables in current URL.
 * @return array A list of parameters that would be required to be passed back to reset the state.
 */
function set_execution_context($new_get, $new_zone = '_SEARCH', $new_current_script = 'index', $erase_keep_also = false)
{
    $old_get = $_GET;
    $old_zone = get_zone_name();
    $old_current_script = current_script();

    foreach ($_GET as $key => $val) {
        if (is_integer($key)) {
            $key = strval($key);
        }

        if ((substr($key, 0, 5) != 'keep_') || ($erase_keep_also)) {
            unset($_GET[$key]);
        }
    }

    foreach ($new_get as $key => $val) {
        $_GET[$key] = is_integer($val) ? strval($val) : $val;
    }

    global $RELATIVE_PATH, $ZONE, $SELF_URL_CACHED;
    $RELATIVE_PATH = ($new_zone == '_SEARCH') ? get_page_zone(get_page_name()) : $new_zone;
    if ($new_zone != $old_zone) {
        $ZONE = null; // So zone details will have to reload
    }
    $SELF_URL_CACHED = null;

    global $PAGE_NAME_CACHE;
    $PAGE_NAME_CACHE = null;
    global $RUNNING_SCRIPT_CACHE, $WHAT_IS_RUNNING_CACHE;
    $RUNNING_SCRIPT_CACHE = array();
    $WHAT_IS_RUNNING_CACHE = $new_current_script;

    return array($old_get, $old_zone, $old_current_script, true);
}

/**
 * Map spaces to %20 and put http:// in front of URLs starting www.
 *
 * @param  URLPATH $url The URL to fix
 * @return URLPATH The fixed result
 */
function remove_url_mistakes($url)
{
    if (substr($url, 0, 4) == 'www.') {
        $url = 'http://' . $url;
    }
    $url = @html_entity_decode($url, ENT_NOQUOTES);
    $url = str_replace(' ', '%20', $url);
    $url = preg_replace('#keep_session=\w*#', 'filtered=1', $url);
    return $url;
}

/**
 * Get hidden fields for a form representing 'keep_x'. If we are having a GET form instead of a POST form, we need to do this. This function also encodes the page name, as we'll always want that.
 *
 * @param  ID_TEXT $page The page for the form to go to (blank: don't attach)
 * @param  boolean $keep_all Whether to keep all elements of the current URL represented in this form (rather than just the keep_ fields, and page)
 * @param  ?array $exclude A list of parameters to exclude (null: don't exclude any)
 * @return Tempcode The builtup hidden form fields
 *
 * @ignore
 */
function _build_keep_form_fields($page = '', $keep_all = false, $exclude = null)
{
    if (is_null($exclude)) {
        $exclude = array();
    }

    if ($page == '_SELF') {
        $page = get_page_name();
    }
    $out = new Tempcode();

    if (count($_GET) > 0) {
        foreach ($_GET as $key => $val) {
            $process_for_key = ((substr($key, 0, 5) == 'keep_') || ($keep_all)) && (!in_array($key, $exclude)) && ($key != 'page') && (!skippable_keep($key, $val));

            if (is_array($val)) {
                foreach ($val as $_key => $_val) { // We'll only support one level deep. Also no keep parameter array support.
                    if (get_magic_quotes_gpc()) {
                        $_val = stripslashes($_val);
                    }

                    if ($process_for_key) {
                        $out->attach(form_input_hidden($key . '[' . $_key . ']', $_val));
                    }
                }
            } else {
                if (!is_string($val)) {
                    continue;
                }

                if (is_integer($key)) {
                    $key = strval($key);
                }

                if (get_magic_quotes_gpc()) {
                    $val = stripslashes($val);
                }

                if ($process_for_key) {
                    $out->attach(form_input_hidden($key, $val));
                }
            }
        }
    }
    if ($page != '') {
        $out->attach(form_input_hidden('page', $page));
    }
    return $out;
}

/**
 * Recurser helper function for _build_keep_post_fields.
 *
 * @param  ID_TEXT $key Key name to put value under
 * @param  mixed $value Value (string or array)
 * @return string The builtup hidden form fields
 *
 * @ignore
 */
function _fixed_post_parser($key, $value)
{
    $out = '';

    if (!is_string($key)) {
        $key = strval($key);
    }

    if (is_array($value)) {
        foreach ($value as $k => $v) {
            if (is_string($k)) {
                $out .= _fixed_post_parser($key . '[' . $k . ']', $v);
            } else {
                $out .= _fixed_post_parser($key . '[' . strval($k) . ']', $v);
            }
        }
    } else {
        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        $out .= static_evaluate_tempcode(form_input_hidden($key, is_string($value) ? $value : strval($value)));
    }

    return $out;
}

/**
 * Relay all POST variables for this URL, to the URL embedded in the form.
 *
 * @param  ?array $exclude A list of parameters to exclude (null: exclude none)
 * @param  boolean $force_everything Force field labels and descriptions to copy through even when there are huge numbers of parameters
 * @return Tempcode The builtup hidden form fields
 *
 * @ignore
 */
function _build_keep_post_fields($exclude = null, $force_everything = false)
{
    $out = '';
    foreach ($_POST as $key => $val) {
        if (is_integer($key)) {
            $key = strval($key);
        }

        if (((!is_null($exclude)) && (in_array($key, $exclude))) || ($key == 'session_id'/*for spam blackhole*/) || ($key == 'csrf_token')) {
            continue;
        }

        if (count($_POST) > 80 && !$force_everything) {
            if (substr($key, 0, 14) == 'tick_on_form__') {
                continue;
            }
            if (substr($key, 0, 11) == 'label_for__') {
                continue;
            }
            if (substr($key, 0, 9) == 'require__') {
                continue;
            }
        }

        $out .= _fixed_post_parser($key, $val);
    }
    return make_string_tempcode($out);
}

/**
 * Takes a URL, and converts it into a file system storable filename. This is used to cache URL contents to the servers filesystem.
 *
 * @param  URLPATH $url_full The URL to convert to an encoded filename
 * @return string A usable filename based on the URL
 *
 * @ignore
 */
function _url_to_filename($url_full)
{
    $bad_chars = array('!', '/', '\\', '?', '*', '<', '>', '|', '"', ':', '%', ' ');
    $new_name = $url_full;
    foreach ($bad_chars as $bad_char) {
        $good_char = '!' . strval(ord($bad_char));
        if ($bad_char == ':') {
            $good_char = ';'; // So page_links save nice
        }
        $new_name = str_replace($bad_char, $good_char, $new_name);
    }

    if (strlen($new_name) <= 200/*technically 256 but something may get put on the start, so be cautious*/) {
        return $new_name;
    }

    // Non correspondance, but at least we have something
    if (strpos($new_name, '.') === false) {
        return md5($new_name);
    }
    return md5($new_name) . '.' . get_file_extension($new_name);
}

/**
 * Take a URL and base-URL, and fully qualify the URL according to it.
 *
 * @param  URLPATH $url The URL to fully qualified
 * @param  URLPATH $url_base The base-URL
 * @return URLPATH Fully qualified URL
 *
 * @ignore
 */
function _qualify_url($url, $url_base)
{
    require_code('obfuscate');
    $mto = mailto_obfuscated();
    if (($url != '') && ($url[0] != '#') && (substr($url, 0, 5) != 'data:') && (substr($url, 0, 7) != 'mailto:') && (substr($url, 0, strlen($mto)) != $mto)) {
        if (url_is_local($url)) {
            if ($url[0] == '/') {
                $parsed = @parse_url($url_base);
                if ($parsed === false) {
                    return '';
                }
                if (!array_key_exists('scheme', $parsed)) {
                    $parsed['scheme'] = 'http';
                }
                if (!array_key_exists('host', $parsed)) {
                    $parsed['host'] = 'localhost';
                }
                if (substr($url, 0, 2) == '//') {
                    $url = $parsed['scheme'] . ':' . $url;
                } else {
                    $url = $parsed['scheme'] . '://' . $parsed['host'] . (array_key_exists('port', $parsed) ? (':' . $parsed['port']) : '') . $url;
                }
            } else {
                $url = $url_base . '/' . $url;
            }
        }
    }

    $url = str_replace('/./', '/', $url);
    $pos = strpos($url, '/../');
    while ($pos !== false) {
        $pos_2 = strrpos(substr($url, 0, $pos - 1), '/');
        if ($pos_2 === false) {
            break;
        }
        $url = substr($url, 0, $pos_2) . '/' . substr($url, $pos + 4);
        $pos = strpos($url, '/../');
    }

    return $url;
}

/**
 * Convert a URL to a local file path.
 *
 * @param  URLPATH $url The value to convert
 * @return ?PATH File path (null: is not local)
 * @ignore
 */
function _convert_url_to_path($url)
{
    if (strpos($url, '?') !== false) {
        return null;
    }
    if ((strpos($url, '://') === false) || (substr($url, 0, strlen(get_base_url()) + 1) == get_base_url() . '/') || (substr($url, 0, strlen(get_custom_base_url()) + 1) == get_custom_base_url() . '/')) {
        if (substr($url, 0, strlen(get_base_url()) + 1) == get_base_url() . '/') {
            $file_path_stub = urldecode(substr($url, strlen(get_base_url()) + 1));
        } elseif (substr($url, 0, strlen(get_custom_base_url()) + 1) == get_custom_base_url() . '/') {
            $file_path_stub = urldecode(substr($url, strlen(get_custom_base_url()) + 1));
        } else {
            $file_path_stub = urldecode($url);
        }
        if (((substr($file_path_stub, 0, 7) == 'themes/') && (substr($file_path_stub, 0, 15) != 'themes/default/')) || (substr($file_path_stub, 0, 8) == 'uploads/') || (strpos($file_path_stub, '_custom/') !== false)) {
            $_file_path_stub = get_custom_file_base() . '/' . $file_path_stub;
            if (!is_file($_file_path_stub)) {
                $_file_path_stub = get_file_base() . '/' . $file_path_stub;
            }
        } else {
            $_file_path_stub = get_file_base() . '/' . $file_path_stub;
        }

        if (!is_file($_file_path_stub)) {
            return null;
        }

        return $_file_path_stub;
    }

    return null;
}

/**
 * Sometimes users don't enter full URLs but do intend for them to be absolute. This code tries to see what relative URLs are actually absolute ones, via an algorithm. It then fixes the URL.
 *
 * @param  URLPATH $in The URL to fix
 * @return URLPATH The fixed URL (or original one if no fix was needed)
 * @ignore
 */
function _fixup_protocolless_urls($in)
{
    if ($in == '') {
        return $in;
    }

    $in = remove_url_mistakes($in); // Chain in some other stuff

    if (strpos($in, ':') !== false) {
        return $in; // Absolute (e.g. http:// or mailto:)
    }

    if (substr($in, 0, 1) == '#') {
        return $in;
    }
    if (substr($in, 0, 1) == '%') {
        return $in;
    }
    if (substr($in, 0, 1) == '{') {
        return $in;
    }

    // Rule 1: // If we have a dot somewhere before a slash, then this dot is likely part of a domain name (not a file extension)- thus we have an absolute URL.
    if (preg_match('#\..*/#', $in) != 0) {
        return 'http://' . $in; // Fix it
    }
    // Rule 2: // If we have no slashes and we don't recognise a file type then they've probably just entered a domain name- thus we have an absolute URL.
    if ((preg_match('#^[^/]+$#', $in) != 0) && (preg_match('#\.(php|htm|asp|jsp|swf|gif|png|jpg|jpe|txt|pdf|odt|ods|odp|doc|mdb|xls|ppt|xml|rss|ppt|svg|wrl|vrml|gif|psd|rtf|bmp|avi|mpg|mpe|webm|mp4|mov|wmv|ram|rm|asf|ra|wma|wav|mp3|ogg|torrent|csv|ttf|tar|gz|rar|bz2)#', $in) == 0)) {
        return 'http://' . $in . '/'; // Fix it
    }

    return $in; // Relative
}

/**
 * Convert a local URL to a page-link.
 *
 * @param  URLPATH $url The URL to convert. Note it may not be a URL Scheme, and it must be based on the local base URL (else failure WILL occur).
 * @param  boolean $abs_only Whether to only convert absolute URLs. Turn this on if you're not sure what you're passing is a URL not and you want to be extra safe.
 * @param  boolean $perfect_only Whether to only allow perfect conversions.
 * @return string The page-link (blank: could not convert).
 *
 * @ignore
 */
function _url_to_page_link($url, $abs_only = false, $perfect_only = true)
{
    if (($abs_only) && (substr($url, 0, 7) != 'http://') && (substr($url, 0, 8) != 'https://')) {
        return '';
    }

    // Try and strip any variants of the base URL from our $url variable, to make it relative
    $non_www_base_url = str_replace('https://www.', 'https://', str_replace('http://www.', 'http://', get_base_url()));
    $www_base_url = str_replace('https://', 'https://www.', str_replace('http://', 'http://www.', get_base_url()));
    $url = preg_replace('#^' . preg_quote(get_base_url() . '/', '#') . '#', '', $url);
    $url = preg_replace('#^' . preg_quote($non_www_base_url . '/', '#') . '#', '', $url);
    $url = preg_replace('#^' . preg_quote($www_base_url . '/', '#') . '#', '', $url);
    if (substr($url, 0, 7) == 'http://') {
        return '';
    }
    if (substr($url, 0, 8) == 'https://') {
        return '';
    }
    if (substr($url, 0, 1) != '/') {
        $url = '/' . $url;
    }

    // Parse the URL
    $parsed_url = @parse_url($url);
    if ($parsed_url === false) {
        require_code('site');
        attach_message(do_lang_tempcode('HTTP_DOWNLOAD_BAD_URL', escape_html($url)), 'warn');
        return '';
    }

    // Work out the zone
    $slash_pos = strpos($parsed_url['path'], '/', 1);
    $zone = ($slash_pos !== false) ? substr($parsed_url['path'], 1, $slash_pos - 1) : '';
    if (!in_array($zone, find_all_zones())) {
        $zone = '';
        $slash_pos = false;
    }
    $parsed_url['path'] = ($slash_pos === false) ? substr($parsed_url['path'], 1) : substr($parsed_url['path'], $slash_pos + 1); // everything AFTER the zone
    $parsed_url['path'] = preg_replace('#/index\.php$#', '', $parsed_url['path']);
    $attributes = array();
    $attributes['page'] = ''; // hopefully will get overwritten with a real one

    // Convert URL Scheme path info into extra implied attribute data
    require_code('url_remappings');
    $does_match = false;
    foreach (array('PG', 'HTM', 'SIMPLE', 'RAW') as $url_scheme) {
        $mappings = get_remappings($url_scheme);
        foreach ($mappings as $mapping) { // e.g. array(array('page' => 'wiki', 'id' => null), 'pg/s/ID', true),
            if (is_null($mapping)) {
                continue;
            }

            list($params, $match_string,) = $mapping;
            $match_string_pattern = preg_replace('#[A-Z]+#', '[^\&\?]+', preg_quote($match_string)); // Turn match string into a regexp

            $does_match = (preg_match('#^' . $match_string_pattern . '#', $parsed_url['path']) != 0);
            if ($does_match) {
                $attributes = array_merge($attributes, $params);

                if ($url_scheme == 'HTM') {
                    if (strpos($parsed_url['path'], '.htm') === false) {
                        continue;
                    }

                    $_match_string = preg_replace('#\.htm$#', '', $match_string);
                    $_path = preg_replace('#\.htm($|\?)#', '', $parsed_url['path']);
                } else {
                    if (strpos($parsed_url['path'], '.htm') !== false) {
                        continue;
                    }

                    $_match_string = $match_string;
                    $_path = $parsed_url['path'];
                }

                $bits_pattern = explode('/', $_match_string);
                $bits_real = explode('/', $_path, count($bits_pattern));

                foreach ($bits_pattern as $i => $bit) {
                    if ((strtoupper($bit) == $bit) && (array_key_exists(strtolower($bit), $params)) && (is_null($params[strtolower($bit)]))) {
                        $attributes[strtolower($bit)] = $bits_real[$i];
                    }
                }

                foreach ($attributes as &$attribute) {
                    $attribute = cms_url_decode_post_process(urldecode($attribute));
                }

                break 2;
            }
        }
    }
    if (!$does_match) {
        return ''; // No match was found
    }

    // Parse query string component into the waiting (and partly-filled-already) attribute data array
    if (array_key_exists('query', $parsed_url)) {
        $bits = explode('&', $parsed_url['query']);
        foreach ($bits as $bit) {
            $_bit = explode('=', $bit, 2);

            if (count($_bit) == 2) {
                $attributes[$_bit[0]] = cms_url_decode_post_process($_bit[1]);
                if (strpos($attributes[$_bit[0]], ':') !== false) {
                    if ($perfect_only) {
                        return ''; // Could not convert this URL to a page-link, because it contains a colon
                    }
                    unset($attributes[$_bit[0]]);
                }
            }
        }
    }

    require_code('site');
    if (_request_page($attributes['page'], $zone) === false) {
        return '';
    }

    $page = fix_page_name_dashing($zone, $attributes['page']);

    // Put it together
    $page_link = $zone . ':' . $page;
    if (array_key_exists('type', $attributes)) {
        $page_link .= ':' . $attributes['type'];
    } elseif (array_key_exists('id', $attributes)) {
        $page_link .= ':';
    }
    if (array_key_exists('id', $attributes)) {
        $page_link .= ':' . $attributes['id'];
    }
    foreach ($attributes as $key => $val) {
        if (!is_string($val)) {
            $val = strval($val);
        }

        if (($key != 'page') && ($key != 'type') && ($key != 'id')) {
            $page_link .= ':' . $key . '=' . cms_url_encode($val);
        }
    }

    // Hash bit?
    if (array_key_exists('fragment', $parsed_url)) {
        $page_link .= '#' . $parsed_url['fragment'];
    }

    return $page_link;
}

/**
 * Convert a local page file path to a written page-link.
 *
 * @param  string $page The path.
 * @return string The page-link (blank: could not convert).
 *
 * @ignore
 */
function _page_path_to_page_link($page)
{
    if ((substr($page, 0, 1) == '/') && (substr($page, 0, 6) != '/pages')) {
        $page = substr($page, 1);
    }
    $matches = array();
    if (preg_match('#^([^/]*)/?pages/([^/]+)/(\w\w/)?([^/\.]+)\.(php|txt|htm)$#', $page, $matches) == 1) {
        $page2 = $matches[1] . ':' . $matches[4];
        if (($matches[2] == 'comcode') || ($matches[2] == 'comcode_custom')) {
            if (file_exists(get_custom_file_base() . '/' . $page)) {
                require_code('zones2');
                $page2 .= ' (' . get_comcode_page_title_from_disk(get_custom_file_base() . '/' . $page) . ')';
                $page2 = preg_replace('#\[[^\[\]]*\]#', '', $page2);
            }
        }
    } else {
        $page2 = '';
    }

    return $page2;
}

/**
 * Called from 'find_id_moniker'. We tried to lookup a moniker, found a hook, but found no stored moniker. So we'll try and autogenerate one.
 *
 * @param  array $ob_info The hooks info profile.
 * @param  array $url_parts The URL component map (must contain 'page', 'type', and 'id' if this function is to do anything).
 * @param  ID_TEXT $zone The URL zone name (only used for Comcode Page URL monikers).
 * @return ?string The moniker ID (null: error generating it somehow, can not do it)
 */
function autogenerate_new_url_moniker($ob_info, $url_parts, $zone)
{
    $effective_id = ($url_parts['type'] == '') ? $url_parts['page'] : $url_parts['id'];

    $bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
    $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
    require_code('content');
    $select = array();
    append_content_select_for_id($select, $ob_info);
    if (substr($ob_info['title_field'], 0, 5) != 'CALL:') {
        $select[] = $ob_info['title_field'];
    }
    if ($ob_info['parent_category_field'] !== null) {
        $select[] = $ob_info['parent_category_field'];
    }
    $db = ((substr($ob_info['table'], 0, 2) != 'f_') || (get_forum_type() == 'none')) ? $GLOBALS['SITE_DB'] : $GLOBALS['FORUM_DB'];
    $where = get_content_where_for_str_id($effective_id, $ob_info);
    if (isset($where['the_zone'])) {
        $where['the_zone'] = $zone;
    }
    $_moniker_src = $db->query_select($ob_info['table'], $select, $where, '', null, null, true); // NB: For Comcode pages visited, this won't return anything -- it will become more performant when the page actually loads, so the moniker won't need redoing each time
    if ($_moniker_src === null) {
        return null; // table missing?
    }
    $GLOBALS['NO_DB_SCOPE_CHECK'] = $bak;
    if (!array_key_exists(0, $_moniker_src)) {
        return null; // been deleted?
    }

    if ($ob_info['id_field_numeric']) {
        if (substr($ob_info['title_field'], 0, 5) == 'CALL:') {
            $moniker_src = call_user_func(trim(substr($ob_info['title_field'], 5)), $url_parts);
        } else {
            if ($ob_info['title_field_dereference']) {
                $moniker_src = get_translated_text($_moniker_src[0][$ob_info['title_field']]);
            } else {
                $moniker_src = $_moniker_src[0][$ob_info['title_field']];
            }
        }
    } else {
        $moniker_src = $effective_id;
    }

    if ($moniker_src == '') {
        $moniker_src = 'untitled';
    }

    return suggest_new_idmoniker_for($url_parts['page'], isset($url_parts['type']) ? $url_parts['type'] : '', $url_parts['id'], $zone, $moniker_src, true);
}

/**
 * Called when content is added, or edited/moved, based upon a new form field that specifies what moniker to use.
 *
 * @param  ID_TEXT $page Page name.
 * @param  ID_TEXT $type Screen type code.
 * @param  ID_TEXT $id Resource ID.
 * @param  ID_TEXT $zone The URL zone name (only used for Comcode Page URL monikers).
 * @param  string $moniker_src String from which a moniker will be chosen (may not be blank).
 * @param  boolean $is_new Whether we are sure this is a new moniker (makes things more efficient, saves a query).
 * @param  ?string $moniker Actual moniker to use (null: generate from $moniker_src). Usually this is left null.
 * @return string The chosen moniker.
 */
function suggest_new_idmoniker_for($page, $type, $id, $zone, $moniker_src, $is_new = false, $moniker = null)
{
    if (get_option('url_monikers_enabled') == '0') {
        return '';
    }

    static $force_called = array();
    $ref = $zone . ':' . $page . ':' . $type . ':' . $id;
    if ($moniker !== null) {
        $force_called[$ref] = $moniker;
    } else {
        if (isset($force_called[$ref])) {
            return $force_called[$ref];
        }
    }

    if (!$is_new) {
        $manually_chosen = $GLOBALS['SITE_DB']->query_select_value_if_there('url_id_monikers', 'm_moniker', array('m_manually_chosen' => 1, 'm_resource_page' => $page, 'm_resource_type' => $type, 'm_resource_id' => $id));
        if ($manually_chosen !== null) {
            return $manually_chosen;
        }

        // Deprecate old one if already exists
        $old = $GLOBALS['SITE_DB']->query_select_value_if_there('url_id_monikers', 'm_moniker', array('m_resource_page' => $page, 'm_resource_type' => $type, 'm_resource_id' => $id, 'm_deprecated' => 0), 'ORDER BY id DESC');
        if (!is_null($old)) {
            // See if it is same as current
            if ($moniker === null) {
                $scope = _give_moniker_scope($page, $type, $id, $zone, '');
                $moniker = $scope . _choose_moniker($page, $type, $id, $moniker_src, $old, $scope);
            }
            if ($moniker == $old) {
                return $old; // hmm, ok it can stay actually
            }

            // It's not. Although, the later call to _choose_moniker will allow us to use the same stem as the current active one, or even re-activate an old deprecated one, so long as it is on this same m_resource_page/m_resource_page/m_resource_id.

            // Deprecate
            $GLOBALS['SITE_DB']->query_update('url_id_monikers', array('m_deprecated' => 1), array('m_resource_page' => $page, 'm_resource_type' => $type, 'm_resource_id' => $id, 'm_deprecated' => 0), '', 1); // Deprecate

            // Deprecate anything underneath
            global $CONTENT_OBS;
            load_moniker_hooks();
            $looking_for = '_SEARCH:' . $page . ':' . $type . ':_WILD';
            $ob_info = isset($CONTENT_OBS[$looking_for]) ? $CONTENT_OBS[$looking_for] : null;
            if (!is_null($ob_info)) {
                $parts = explode(':', $ob_info['view_page_link_pattern']);
                $category_page = $parts[1];
                $GLOBALS['SITE_DB']->query('UPDATE ' . get_table_prefix() . 'url_id_monikers SET m_deprecated=1 WHERE ' . db_string_equal_to('m_resource_page', $category_page) . ' AND m_moniker LIKE \'' . db_encode_like($old . '/%') . '\''); // Deprecate
            }
        }
    }

    if ($moniker === null) {
        if (is_numeric($moniker_src)) {
            $moniker = $id;
        } else {
            $scope = _give_moniker_scope($page, $type, $id, $zone, '');
            $moniker = $scope . _choose_moniker($page, $type, $id, $moniker_src, null, $scope);

            if (($page == 'news') && ($type == 'view') && (get_value('google_news_urls') === '1')) {
                $moniker .= '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
            }
        }
    }

    // Insert
    $GLOBALS['SITE_DB']->query_delete('url_id_monikers', array(    // It's possible we're re-activating a deprecated one
                                                                   'm_resource_page' => $page,
                                                                   'm_resource_type' => $type,
                                                                   'm_resource_id' => $id,
                                                                   'm_moniker' => $moniker,
    ), '', 1);
    $GLOBALS['SITE_DB']->query_insert('url_id_monikers', array(
        'm_resource_page' => $page,
        'm_resource_type' => $type,
        'm_resource_id' => $id,
        'm_moniker' => $moniker,
        'm_moniker_reversed' => strrev($moniker),
        'm_deprecated' => 0,
        'm_manually_chosen' => 0,
    ));

    global $LOADED_MONIKERS_CACHE;
    $LOADED_MONIKERS_CACHE = array();

    return $moniker;
}

/**
 * Delete an old moniker, and place a new one.
 *
 * @param  ID_TEXT $page Page name.
 * @param  ID_TEXT $type Screen type code.
 * @param  ID_TEXT $id Resource ID.
 * @param  string $moniker_src String from which a moniker will be chosen (may not be blank).
 * @param  ?string $no_exists_check_for Whether to skip the exists check for a certain moniker (will be used to pass "existing self" for edits) (null: nothing existing to check against).
 * @param  ?string $scope_context Where the moniker will be placed in the moniker URL tree (null: unknown, so make so no duplicates anywhere).
 * @return string Chosen moniker.
 *
 * @ignore
 */
function _choose_moniker($page, $type, $id, $moniker_src, $no_exists_check_for = null, $scope_context = null)
{
    $moniker = _generate_moniker($moniker_src);

    // Check it does not already exist
    $moniker_origin = $moniker;
    $next_num = 1;
    if (is_numeric($moniker)) {
        $moniker .= '-1';
    }
    $test = mixed();
    do {
        if (!is_null($no_exists_check_for)) {
            if ($moniker == preg_replace('#^.*/#', '', $no_exists_check_for)) {
                return $moniker; // This one is okay, we know it is safe
            }
        }

        $dupe_sql = 'SELECT m_resource_id FROM ' . get_table_prefix() . 'url_id_monikers WHERE ';
        $dupe_sql .= db_string_equal_to('m_resource_page', $page);
        if ($type == '') {
            $dupe_sql .= ' AND ' . db_string_equal_to('m_resource_id', $id);
        } else {
            $dupe_sql .= ' AND ' . db_string_equal_to('m_resource_type', $type) . ' AND ' . db_string_not_equal_to('m_resource_id', $id);
        }
        $dupe_sql .= ' AND (';
        if (!is_null($scope_context)) {
            $dupe_sql .= db_string_equal_to('m_moniker', $scope_context . $moniker);
        } else {
            // Use reversing for better indexing performance
            $dupe_sql .= db_string_equal_to('m_moniker_reversed', strrev($moniker));
            $dupe_sql .= ' OR m_moniker_reversed LIKE \'' . db_encode_like(strrev('%/' . $moniker)) . '\'';
        }
        $dupe_sql .= ')';
        $test = $GLOBALS['SITE_DB']->query_value_if_there($dupe_sql, false, true);
        if (!is_null($test)) { // Oh dear, will pass to next iteration, but trying a new moniker
            $next_num++;
            $moniker = $moniker_origin . '-' . strval($next_num);
        }
    } while (!is_null($test));

    return $moniker;
}

/**
 * Generate a moniker from an arbitrary raw string. Does not perform uniqueness checks.
 *
 * @param  string $moniker_src Raw string.
 * @return ID_TEXT Moniker.
 *
 * @ignore
 */
function _generate_moniker($moniker_src)
{
    $moniker = strip_comcode($moniker_src);

    $max_moniker_length = intval(get_option('max_moniker_length'));

    // Transliteration first
    if ((get_charset() == 'utf-8') && (get_option('moniker_transliteration') == '1')) {
        if (function_exists('transliterator_transliterate')) {
            $_moniker = @transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $moniker);
            if (!empty($_moniker)) {
                $moniker = $_moniker;
            }
        } elseif ((function_exists('iconv')) && (get_value('disable_iconv') !== '1')) {
            $_moniker = @iconv('utf-8', 'ASCII//TRANSLIT//IGNORE', $moniker);
            if (!empty($_moniker)) {
                $moniker = $_moniker;
            }
        } else {
            // German has inbuilt transliteration
            $moniker = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $moniker);
        }
    }

    // Then strip down / substitute to force it to be URL-ready
    $moniker = str_replace("'", '', $moniker);
    $moniker = cms_mb_strtolower(preg_replace('#[^' . URL_CONTENT_REGEXP . ']#', '-', $moniker));
    if (cms_mb_strlen($moniker) > $max_moniker_length) {
        $pos = strrpos(cms_mb_substr($moniker, 0, $max_moniker_length), '-');
        if (($pos === false) || ($pos < 12)) {
            $pos = $max_moniker_length;
        }
        $moniker = cms_mb_substr($moniker, 0, $pos);
    }
    $moniker = preg_replace('#\-+#', '-', $moniker);
    $moniker = rtrim($moniker, '-');

    // A bit lame, but maybe we'll have to
    if ($moniker == '') {
        $moniker = 'untitled';
    }

    return $moniker;
}

/**
 * Take a moniker and it's page-link details, and make a full path from it.
 *
 * @param  ID_TEXT $page Page name.
 * @param  ID_TEXT $type Screen type code.
 * @param  ID_TEXT $id Resource ID.
 * @param  ID_TEXT $zone The URL zone name (only used for Comcode Page URL monikers).
 * @param  string $main Pathless moniker.
 * @return string The fully qualified moniker.
 *
 * @ignore
 */
function _give_moniker_scope($page, $type, $id, $zone, $main)
{
    // Does this URL arrangement support monikers?
    global $CONTENT_OBS;
    load_moniker_hooks();
    $found = false;
    if ($type == '') {
        $looking_for = '_WILD:_WILD';
    } else {
        $looking_for = '_SEARCH:' . $page . ':' . $type . ':_WILD';
    }

    $ob_info = isset($CONTENT_OBS[$looking_for]) ? $CONTENT_OBS[$looking_for] : null;

    $moniker = $main;

    if (is_null($ob_info)) {
        return $moniker;
    }

    if (!is_null($ob_info['parent_category_field'])) {
        if ($ob_info['parent_category_field'] == 'the_zone') {
            $ob_info['parent_category_field'] = 'p_parent_page'; // Special exception for Comcode page monikers
        }

        // Lookup DB record so we can discern the category
        $bak = $GLOBALS['NO_DB_SCOPE_CHECK'];
        $GLOBALS['NO_DB_SCOPE_CHECK'] = true;
        require_code('content');
        $select = array();
        append_content_select_for_id($select, $ob_info);
        if (substr($ob_info['title_field'], 0, 5) != 'CALL:') {
            $select[] = $ob_info['title_field'];
        }
        if (!is_null($ob_info['parent_category_field'])) {
            $select[] = $ob_info['parent_category_field'];
        }
        $where = get_content_where_for_str_id(($type == '') ? $page : $id, $ob_info);
        if (isset($where['the_zone'])) {
            $where['the_zone'] = $zone;
        }
        $_moniker_src = $GLOBALS['SITE_DB']->query_select($ob_info['table'], $select, $where, '', null, null, true);
        if ($_moniker_src === null) {
            return $moniker; // table missing?
        }
        $GLOBALS['NO_DB_SCOPE_CHECK'] = $bak;
        if (!array_key_exists(0, $_moniker_src)) {
            return $moniker; // been deleted?
        }

        // Discern the path (will effectively recurse, due to find_id_moniker call)
        $parent = $_moniker_src[0][$ob_info['parent_category_field']];
        if (is_integer($parent)) {
            $parent = strval($parent);
        }
        if ((is_null($parent)) || ($parent === 'root') || ($parent === '') || ($parent == strval(db_get_first_id()))) {
            $tree = null;
        } else {
            $view_category_page_link_pattern = explode(':', $ob_info['view_category_page_link_pattern']);
            if ($type == '') {
                $tree = find_id_moniker(array('page' => $parent), $zone);
            } else {
                $tree = find_id_moniker(array('page' => $view_category_page_link_pattern[1], 'type' => $view_category_page_link_pattern[2], 'id' => $parent), $zone);
            }
        }

        // Okay, so our full tree path is as follows
        if (!is_null($tree)) {
            $moniker = $tree . '/' . $main;
        }
    }

    return $moniker;
}

/**
 * Take a moniker and it's page-link details, and make a full path from it.
 *
 * @param  ID_TEXT $content_type The content type.
 * @param  SHORT_TEXT $url_moniker The URL moniker.
 * @return ?ID_TEXT The ID (null: not found).
 */
function find_id_via_url_moniker($content_type, $url_moniker)
{
    $path = 'hooks/systems/content_meta_aware/' . filter_naughty($content_type, true);
    if ((!file_exists(get_file_base() . '/sources/' . $path . '.php')) && (!file_exists(get_file_base() . '/sources_custom/' . $path . '.php'))) {
        return null;
    }

    require_code($path);

    $cma_ob = object_factory('Hook_content_meta_aware_' . $content_type);
    $cma_info = $cma_ob->info();
    if (!$cma_info['support_url_monikers']) {
        return null;
    }

    list(, $url_bits) = page_link_decode($cma_info['view_page_link_pattern']);
    $where = array('m_resource_page' => $url_bits['page'], 'm_resource_type' => $url_bits['type'], 'm_moniker' => $url_moniker);

    $ret = $cma_info['connection']->query_select_value_if_there('url_id_monikers', 'm_resource_id', $where);
    return $ret;
}
