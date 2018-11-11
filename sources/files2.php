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
 * @package    core
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__files2()
{
    require_code('files');
}

/**
 * Make a missing required directory, or exit with an error if we cannot (unless error suppression is on).
 *
 * @param  PATH $dir Path to create
 * @return boolean Success status
 */
function make_missing_directory($dir)
{
    if (@mkdir($dir, 0777, true) === false) {
        if (error_reporting() == 0) {
            return false;
        }
        if (function_exists('do_lang_tempcode')) {
            warn_exit(do_lang_tempcode('WRITE_ERROR_DIRECTORY_REPAIR', escape_html($dir)), false, true);
        } else {
            warn_exit('Could not auto-create missing directory ' . htmlentities($dir), false, true);
        }
    }
    fix_permissions($dir);
    sync_file($dir);
    return true;
}

/**
 * Discern the cause of a file-write error, and show an appropriate error message.
 *
 * @param  PATH $path File path that could not be written (full path, not relative)
 * @ignore
 */
function _intelligent_write_error($path)
{
    if (error_reporting() == 0) {
        return;
    }

    if (!function_exists('do_lang_tempcode')) {
        warn_exit('Could not write to ' . htmlentities($path));
    }

    if (file_exists($path)) {
        if (filesize($path) == 0) {
            return; // Probably was OR'd where 0 casted to false
        }

        warn_exit(do_lang_tempcode('WRITE_ERROR', escape_html($path)), false, true);
    } elseif (file_exists(dirname($path))) {
        if (strpos($path, '/templates_cached/') !== false) {
            critical_error('PASSON', do_lang('WRITE_ERROR_CREATE', escape_html($path), escape_html(dirname($path))));
        }
        warn_exit(do_lang_tempcode('WRITE_ERROR_CREATE', escape_html($path), escape_html(dirname($path))), false, true);
    } else {
        warn_exit(do_lang_tempcode('WRITE_ERROR_MISSING_DIRECTORY', escape_html(dirname($path)), escape_html(dirname(dirname($path)))), false, true);
    }
}

/**
 * Discern the cause of a file-write error, and return an appropriate error message.
 *
 * @param  PATH $path File path that could not be written
 * @return Tempcode Message
 *
 * @ignore
 */
function _intelligent_write_error_inline($path)
{
    static $looping = false;
    if ($looping || !function_exists('do_lang_tempcode')) { // In case do_lang_tempcode below spawns a recursive failure, due to the file being the language cache itself
        critical_error('PASSON', 'Could not write to ' . htmlentities($path)); // Bail out hard if would cause a loop
    }
    $looping = true;

    if (file_exists($path)) {
        $ret = do_lang_tempcode('WRITE_ERROR', escape_html($path));
    } elseif (file_exists(dirname($path))) {
        $ret = do_lang_tempcode('WRITE_ERROR_CREATE', escape_html($path), escape_html(dirname($path)));
    } else {
        $ret = do_lang_tempcode('WRITE_ERROR_MISSING_DIRECTORY', escape_html(dirname($path)), escape_html(dirname(dirname($path))));
    }

    $looping = false;

    return $ret;
}

/**
 * Find details of where we can save temporary files, taking into account PHP's platform-dependent difficulties.
 *
 * @return array A tuple: preferred temporary path to save to, whether there's a problem saving in the system path, the system path to save to, the local path to save to
 */
function cms_get_temp_dir()
{
    $local_path = get_custom_file_base() . '/temp';
    if (!file_exists($local_path)) {
        make_missing_directory($local_path);
    }
    $server_path = rtrim(sys_get_temp_dir(), '/\\');
    $problem_saving = ((get_option('force_local_temp_dir') == '1') || ((ini_get('open_basedir') != '') && (preg_match('#(^|:|;)' . preg_quote($server_path, '#') . '($|:|;|/)#', ini_get('open_basedir')) == 0)));
    $path = ($problem_saving ? $local_path : $server_path);
    return array($path, $problem_saving, $server_path, $local_path);
}

/**
 * Create file with unique file name, but works around compatibility issues between servers. Note that the file is NOT automatically deleted. You should also delete it using "@unlink", as some servers have problems with permissions.
 *
 * @param  string $prefix The prefix of the temporary file name
 * @return ~string The name of the temporary file (false: error)
 *
 * @ignore
 */
function _cms_tempnam($prefix = '')
{
    list($tmp_path, $problem_saving, $server_path, $local_path) = cms_get_temp_dir();
    if (php_function_allowed('tempnam')) {
        // Create a real temporary file
        $tempnam = tempnam($tmp_path, 'tmpfile__' . $prefix);
        if ((($tempnam === false) || ($tempnam == ''/*Should not be blank, but seen in the wild*/)) && (!$problem_saving)) {
            $tempnam = tempnam($local_path, 'tmpfile__' . $prefix); // Try saving in local path even if we didn't think there'd be a problem saving into the system path
        }
    } else {
        // A fake temporary file, as true ones have been disabled on PHP
        require_code('crypt');
        $tempnam = 'tmpfile__' . $prefix . get_secure_random_string();
        $myfile = fopen($local_path . '/' . $tempnam, 'wb');
        fclose($myfile);
        fix_permissions($local_path . '/' . $tempnam);
    }
    return $tempnam;
}

/**
 * Find if a file is a temporary file.
 *
 * @param  PATH $path File path
 * @return boolean Whether it is
 */
function is_temp_file($path)
{
    $path = realpath($path);

    $_temp_dir = cms_get_temp_dir();
    $temp_dirs = array(
        realpath($_temp_dir[0]),
        get_custom_file_base() . '/temp',
    );

    foreach ($temp_dirs as $temp_dir) {
        if (substr($path, 0, strlen($temp_dir) + 1) == $temp_dir . '/') {
            return true;
        }
        if (substr($path, 0, strlen($temp_dir) + 1) == $temp_dir . '\\') {
            return true;
        }
    }

    return false;
}

/**
 * Delete any attachment files from disk that were created as temporary files.
 * We cannot do this after the mail_wrap function is called because the mail queue will need them - it has to be once the mail is finished with.
 *
 * @param  ?array $attachments A list of attachments (each attachment being a map, absolute path=>filename) (null: none)
 */
function clean_temporary_mail_attachments($attachments)
{
    if ($attachments !== null) {
        foreach (array_keys($attachments) as $path) {
            if (is_temp_file($path)) {
                unlink($path);
            }
        }
    }
}

/**
 * Provides a hook for file synchronisation between mirrored servers. Called after any file creation, deletion or edit.
 *
 * @param  PATH $filename File/directory name to sync on (full path)
 * @ignore
 */
function _sync_file($filename)
{
    global $FILE_BASE, $_MODIFIED_FILES, $_CREATED_FILES;
    if (substr($filename, 0, strlen($FILE_BASE) + 1) == $FILE_BASE . '/') {
        $filename = substr($filename, strlen($FILE_BASE) + 1);
    }
    static $has_sync_script = null;
    if ($has_sync_script === null) {
        $has_sync_script = is_file($FILE_BASE . '/data_custom/sync_script.php');
    }
    if ($has_sync_script) {
        require_once($FILE_BASE . '/data_custom/sync_script.php');
        if (function_exists('master__sync_file')) {
            master__sync_file($filename);
        }
    }
    if (isset($_MODIFIED_FILES)) {
        foreach ($_MODIFIED_FILES as $i => $x) {
            if (($x == $FILE_BASE . '/' . $filename) || ($x == $filename)) {
                unset($_MODIFIED_FILES[$i]);
            }
        }
    }
    if (isset($_CREATED_FILES)) {
        foreach ($_CREATED_FILES as $i => $x) {
            if (($x == $FILE_BASE . '/' . $filename) || ($x == $filename)) {
                unset($_CREATED_FILES[$i]);
            }
        }
    }
}

/**
 * Provides a hook for file-move synchronisation between mirrored servers. Called after any rename or move action.
 *
 * @param  PATH $old File/directory name to move from (may be full or relative path)
 * @param  PATH $new File/directory name to move to (may be full or relative path)
 * @ignore
 */
function _sync_file_move($old, $new)
{
    global $FILE_BASE;
    if (is_file($FILE_BASE . '/data_custom/sync_script.php')) {
        require_once($FILE_BASE . '/data_custom/sync_script.php');
        if (substr($old, 0, strlen($FILE_BASE)) == $FILE_BASE) {
            $old = substr($old, strlen($FILE_BASE));
        }
        if (substr($new, 0, strlen($FILE_BASE)) == $FILE_BASE) {
            $new = substr($new, strlen($FILE_BASE));
        }
        if (function_exists('master__sync_file_move')) {
            master__sync_file_move($old, $new);
        }
    }
}

/**
 * Delete all the contents of a directory, and any subdirectories of that specified directory (recursively).
 * Does not delete the directory itself.
 *
 * @param  PATH $dir The pathname to the directory to delete
 * @param  boolean $default_preserve Whether to preserve files there by default
 * @param  boolean $just_files Whether to just delete files
 *
 * @ignore
 */
function _deldir_contents($dir, $default_preserve = false, $just_files = false)
{
    $current_dir = @opendir($dir);
    if ($current_dir !== false) {
        while (false !== ($entryname = readdir($current_dir))) {
            if ($default_preserve) {
                if ($entryname == 'index.html') {
                    continue;
                }
                if ($entryname[0] == '.') {
                    continue;
                }
                if (in_array(str_replace(get_file_base() . '/', '', $dir) . '/' . $entryname, array('uploads/banners/advertise_here.png', 'uploads/banners/donate.png', 'themes/map.ini', 'themes/default'))) {
                    continue;
                }
            }
            if ((is_dir($dir . '/' . $entryname)) && ($entryname != '.') && ($entryname != '..')) {
                deldir_contents($dir . '/' . $entryname, $default_preserve, $just_files);
                if (!$just_files) {
                    $test = @rmdir($dir . '/' . $entryname);
                    if (($test === false) && (!$just_files/*tolerate weird locked dirs if we only need to delete files anyways*/) && (function_exists('attach_message'))) {
                        attach_message(do_lang_tempcode('WRITE_ERROR', escape_html($dir . '/' . $entryname)), 'warn', false, true);
                    }
                }
            } elseif (($entryname != '.') && ($entryname != '..')) {
                $test = @unlink($dir . '/' . $entryname);
                if (($test === false) && (function_exists('attach_message'))) {
                    attach_message(do_lang_tempcode('WRITE_ERROR', escape_html($dir . '/' . $entryname)), 'warn', false, true);
                }
            }
            sync_file($dir . '/' . $entryname);
        }
        closedir($current_dir);
    }
}

/**
 * Output data to a CSV file.
 *
 * @param  array $data List of maps, each map representing a row
 * @param  ID_TEXT $filename Filename to output
 * @param  boolean $headers Whether to output CSV headers
 * @param  boolean $output_and_exit Whether to output/exit when we're done instead of return
 * @param  ?PATH $outfile_path File to spool into (null: none)
 * @param  ?mixed $callback Callback for dynamic row insertion (null: none). Only implemented for the excel_support addon. Is passed: row just done, next row (or null), returns rows to insert
 * @param  array $metadata List of maps, each map representing metadata of a row; supports 'url'
 * @return string CSV data (we might not return though, depending on $exit; if $outfile_path is not null, this will be blank)
 */
function make_csv($data, $filename = 'data.csv', $headers = true, $output_and_exit = true, $outfile_path = null, $callback = null, $metadata = array())
{
    if ($headers) {
        header('Content-Type: text/csv; charset=' . get_charset());
        header('Content-Disposition: attachment; filename="' . escape_header($filename, true) . '"');

        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            return '';
        }
    }

    $outfile = null;
    if ($outfile_path !== null) {
        $outfile = fopen($outfile_path, 'w+b');
        // TODO: #3032
        flock($outfile, LOCK_EX);
    }

    $out = '';

    if (get_charset() == 'utf-8') {
        $bom = chr(0xEF) . chr(0xBB) . chr(0xBF);
        //$out .= $bom; Shows as gibberish on Mac unless you explicitly import it with the correct settings
    }

    foreach ($data as $i => $line) {
        if ($i == 0) { // Header
            foreach (array_keys($line) as $j => $val) {
                if ($j != 0) {
                    $out .= ',';
                }
                $out .= '"' . str_replace('"', '""', $val) . '"';
            }
            $out .= "\n";
        }

        // Main data
        $j = 0;
        foreach ($line as $val) {
            if ($val === null) {
                $val = '';
            } elseif (!is_string($val)) {
                $val = strval($val);
            }
            if ($j != 0) {
                $out .= ',';
            }
            $out .= '"' . str_replace('"', '""', $val) . '"';
            $j++;
        }
        $out .= "\n";

        if ($outfile !== null) {
            fwrite($outfile, $out);
            $out = '';
        }
    }

    if ($output_and_exit) {
        $GLOBALS['SCREEN_TEMPLATE_CALLED'] = '';

        cms_ini_set('ocproducts.xss_detect', '0');

        if ($outfile !== null) {
            cms_ob_end_clean();

            rewind($outfile);
            fpassthru($outfile);
            flock($outfile, LOCK_UN);
            fclose($outfile);
            @unlink($outfile_path);
        }
        exit($out);
    }

    if ($outfile !== null) {
        flock($outfile, LOCK_UN);
        fclose($outfile);
    }

    return $out;
}

/**
 * Delete a column from a CSV file.
 *
 * @param  PATH $in_path Path to the CSV file
 * @param  string $column_name Column name
 */
function delete_csv_column($in_path, $column_name)
{
    if (!cms_is_writable($in_path)) {
        fatal_exit(do_lang_tempcode('WRITE_ERROR', escape_html($in_path)));
    }

    // Find which field index this named column is
    $in_file = fopen($in_path, 'rb');
    // TODO: #3032
    flock($in_file, LOCK_SH);
    $header_row = fgetcsv($in_file);
    $column_i = null;
    foreach ($header_row as $i => $h) {
        if ($h == $column_name) {
            $column_i = $i;
            break;
        }
    }
    if ($column_i === null) {
        return;
    }

    // Rewrite out to a temp file
    $tmp_path = cms_tempnam();
    $tmp_file = fopen($tmp_path, 'wb');

    // Write out header
    unset($header_row[$i]);
    foreach ($header_row as $i => $h) {
        if ($i != 0) {
            fwrite($tmp_file, ',');
        }
        fwrite($tmp_file, str_replace('"', '""', $h));
    }
    fwrite($tmp_file, "\n");

    // Write out each row
    while (($row = fgetcsv($in_file)) !== false) {
        unset($row[$column_i]);

        foreach ($row as $i => $c) {
            if ($i != 0) {
                fwrite($tmp_file, ',');
            }
            fwrite($tmp_file, '"' . str_replace('"', '""', $c) . '"');
        }
        fwrite($tmp_file, "\n");
    }

    // Clean up; put temp file back over main file
    flock($in_file, LOCK_UN);
    fclose($in_file);
    fclose($tmp_file);
    @unlink($in_path);
    rename($tmp_path, $in_path);
    sync_file($in_path);
    fix_permissions($in_path);
}

/**
 * Find path to the PHP executable.
 *
 * @param  boolean $cgi Whether we need a CGI interpreter
 * @return PATH Path to PHP
 */
function find_php_path($cgi = false)
{
    $search_dirs = array(
        '/bin',
        '/usr/bin',
        '/usr/local/bin',
        '/usr/php/bin',
        '/usr/php/sbin',
        '/usr/php5/bin',
        '/usr/php5/sbin',
        '/usr/php7/bin',
        '/usr/php7/sbin',
        'c:\\php',
        'c:\\php5',
        'c:\\php7',
        'c:\\progra~1\\php',
        'c:\\progra~1\\php5',
        'c:\\progra~1\\php7',
    );
    $filenames = array(
        'php.dSYM',
        'php',
        'php5',
        'php7',
        'php-cli.dSYM',
        'php-cli',
        'php5-cli',
        'php7-cli',
        'php-cgi.dSYM',
        'php-cgi',
        'php5-cgi',
        'php7-cgi',
        'php-win.exe',
    );
    foreach ($search_dirs as $dir) {
        foreach ($filenames as $file) {
            if ((!$cgi) || (strpos($file, 'cgi') !== false)) {
                if (@file_exists($dir . '/' . $file)) {
                    break 2;
                }
            }
        }
    }
    if (!@file_exists($dir . '/' . $file)) {
        $php_path = $cgi ? 'php-cgi' : 'php';
    } else {
        $php_path = $dir . '/' . $file;
    }
    return $php_path;
}

/**
 * Get the contents of a directory, recursively. It is assumed that the directory exists.
 *
 * @param  PATH $path The path to search
 * @param  PATH $rel_path The path we prepend to everything we find (intended to be used inside the recursion)
 * @param  ?integer $bitmask Bitmask of extra stuff to ignore (see IGNORE_* constants) (null: do not use)
 * @param  boolean $recurse Whether to recurse (if not, will return directories as files)
 * @param  boolean $files_wanted Whether to get files (if not, will return directories instead of files)
 * @param  ?array $file_extensions File extensions to limit to (no dots), if $files_wanted set (null: no limit)
 * @return array The contents of the directory
 */
function get_directory_contents($path, $rel_path = '', $bitmask = IGNORE_ACCESS_CONTROLLERS, $recurse = true, $files_wanted = true, $file_extensions = null)
{
    if (($files_wanted) && ($file_extensions === array())) {
        return array(); // Optimisation
    }

    $out = array();

    require_code('files');

    $dh = @opendir($path);
    if ($dh === false) {
        return array();
    }
    while (($file = readdir($dh)) !== false) {
        if ($file == '_meta_tree') { // Very special case, directory can get huge
            continue;
        }

        if ($bitmask !== null) {
            if (should_ignore_file($rel_path . (($rel_path == '') ? '' : '/') . $file, $bitmask)) {
                continue;
            }
        } elseif (($file == '.') || ($file == '..')) {
            continue;
        }

        if (is_file($path . '/' . $file)) {
            if (($files_wanted) && (($file_extensions === null) || (in_array(get_file_extension($file), $file_extensions)))) {
                $out[] = $rel_path . (($rel_path == '') ? '' : '/') . $file;
            }
        } elseif (is_dir($path . '/' . $file)) {
            if (!$files_wanted) {
                $out[] = $rel_path . (($rel_path == '') ? '' : '/') . $file;
            }
            if ($recurse) {
                $out = array_merge($out, get_directory_contents($path . '/' . $file, $rel_path . (($rel_path == '') ? '' : '/') . $file, $bitmask, $recurse, $files_wanted, $file_extensions));
            }
        }
    }
    closedir($dh);

    return $out;
}

/**
 * Get the size in bytes of a directory. It is assumed that the directory exists.
 *
 * @param  PATH $path The path to search
 * @param  boolean $recurse Whether to recurse (if not, will return directories as files)
 * @return integer The extra space requested
 */
function get_directory_size($path, $recurse = true)
{
    $size = 0;

    $dh = @opendir($path);
    if ($dh === false) {
        return 0;
    }
    while (($e = readdir($dh)) !== false) {
        if (($e == '.') || ($e == '..')) {
            continue;
        }

        if (is_file($path . '/' . $e)) {
            $size += filesize($path . '/' . $e);
        } elseif (is_dir($path . '/' . $e)) {
            if ($recurse) {
                $size += get_directory_size($path . '/' . $e, $recurse);
            }
        }
    }
    closedir($dh);

    return $size;
}

/**
 * Get a message for maximum uploads.
 *
 * @param  float $max Maximum size in MB
 * @return Tempcode The message
 */
function get_maximum_upload_message($max)
{
    $config_url = get_upload_limit_config_url();
    return paragraph(do_lang_tempcode(($config_url === null) ? 'MAXIMUM_UPLOAD' : 'MAXIMUM_UPLOAD_STAFF', escape_html(($max > 10.0) ? integer_format(intval($max)) : float_format($max)), escape_html(($config_url === null) ? '' : $config_url)), '0oa9paovv3xj12dqlny21zwajoh1f90q');
}

/**
 * Get the URL to the config option group for editing limits.
 *
 * @return ?URLPATH The URL to the config option group for editing limits (null: no access)
 */
function get_upload_limit_config_url()
{
    $config_url = null;
    if (has_actual_page_access(get_member(), 'admin_config')) {
        $_config_url = build_url(array('page' => 'admin_config', 'type' => 'category', 'id' => 'SITE'), get_module_zone('admin_config'));
        $config_url = $_config_url->evaluate();
        $config_url .= '#group_UPLOAD';
    }
    return $config_url;
}

/**
 * Get the maximum allowed upload filesize, as specified in the configuration.
 *
 * @param  ?MEMBER $source_member Member we consider quota for (null: do not consider quota)
 * @param  ?object $db Database connector to get quota from (null: site DB)
 * @param  boolean $consider_php_limits Whether to consider limitations in PHP's configuration
 * @return integer The maximum allowed upload filesize, in bytes
 */
function get_max_file_size($source_member = null, $db = null, $consider_php_limits = true)
{
    $possibilities = array();

    require_code('files');
    $a = php_return_bytes(ini_get('upload_max_filesize'));
    $b = GOOGLE_APPENGINE ? 0 : php_return_bytes(ini_get('post_max_size'));
    $c = intval(get_option('max_download_size')) * 1024;
    if (has_privilege(get_member(), 'exceed_filesize_limit')) {
        $c = 0;
    }

    $d = null;
    if (($source_member !== null) && (!has_privilege(get_member(), 'exceed_filesize_limit'))) { // We'll be considering quota also
        if (get_forum_type() == 'cns') {
            require_code('cns_groups');
            $daily_quota = cns_get_member_best_group_property($source_member, 'max_daily_upload_mb');
        } else {
            $daily_quota = NON_CNS_QUOTA;
        }
        if ($db === null) {
            $db = $GLOBALS['SITE_DB'];
        }
        $_size_uploaded_today = $db->query('SELECT SUM(a_file_size) AS the_answer FROM ' . $db->get_table_prefix() . 'attachments WHERE a_member_id=' . strval($source_member) . ' AND a_add_time>' . strval(time() - 60 * 60 * 24) . ' AND a_add_time<=' . strval(time()));
        $size_uploaded_today = intval($_size_uploaded_today[0]['the_answer']);
        $d = max(0, $daily_quota * 1024 * 1024 - $size_uploaded_today);
    }

    if ($consider_php_limits) {
        if ($a != 0) {
            $possibilities[] = $a;
        }
        if ($b != 0) {
            $possibilities[] = $b;
        }
    }
    if ($c != 0) {
        $possibilities[] = $c;
    }
    if ($d !== null) {
        $possibilities[] = $d;
    }

    return (count($possibilities) == 0) ? (1024 * 1024 * 1024 * 1024) : min($possibilities);
}

/**
 * Check uploaded file extensions for possible malicious intent, and if some is found, an error is put out, and the hackattack logged.
 *
 * @param  string $name The filename
 * @param  boolean $skip_server_side_security_check Whether to skip the server side security check
 * @param  ?string $file_to_delete Delete this file if we have to exit (null: no file to delete)
 * @param  boolean $accept_errors Whether to allow errors without dying
 * @param  ?MEMBER $member_id Member to check as (null: current member)
 * @return boolean Success status
 */
function check_extension($name, $skip_server_side_security_check = false, $file_to_delete = null, $accept_errors = false, $member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }

    $ext = get_file_extension($name);

    $_types = get_option('valid_types');
    $types = array_flip(explode(',', $_types));
    ksort($types);
    if (!$skip_server_side_security_check) {
        if (!has_privilege($member_id, 'use_very_dangerous_comcode')) {
            $dangerous_markup_types = array(
                'js',
                'json',
                'html',
                'htm',
                'shtml',
                'svg',
                'xml',
                'rss',
                'atom',
                'xsd',
                'xsl',
                'css',
                'woff',
            );
            foreach ($dangerous_markup_types as $type) {
                unset($types[$type]);
            }
        }
    }
    $types = array_flip($types);

    $_types = '';
    foreach ($types as $val) {
        if ($_types != '') {
            $_types .= ',';
        }
        $_types .= $val;
    }

    if (!$skip_server_side_security_check) {
        $dangerous_code_types = array(
            'py',
            'dll',
            'cfm',
            'vbs',
            'rhtml',
            'rb',
            'pl',
            'phtml',
            'php',
            'php3',
            'php4',
            'php5',
            'php7',
            'phps',
            'aspx',
            'ashx',
            'asmx',
            'asx',
            'axd',
            'asp',
            'aspx',
            'asmx',
            'ashx',
            'jsp',
            'sh',
            'cgi',
            'fcgi',
        );
        if ((in_array($ext, $dangerous_code_types)) || (strtolower($name) == '.htaccess')) {
            if ($file_to_delete !== null) {
                unlink($file_to_delete);
            }
            if ($accept_errors) {
                return false;
            }
            log_hack_attack_and_exit('SCRIPT_UPLOAD_HACK');
        }
    }

    if ($_types != '') {
        foreach ($types as $val) {
            if (strtolower(trim($val)) == $ext) {
                return true;
            }
        }

        if ($file_to_delete !== null) {
            unlink($file_to_delete);
        }
        $message = do_lang_tempcode('INVALID_FILE_TYPE', escape_html($ext), escape_html(str_replace(',', ', ', $_types)));
        if (has_actual_page_access(get_member(), 'admin_config')) {
            $_url = build_url(array('page' => 'admin_config', 'type' => 'category', 'id' => 'SECURITY'), get_module_zone('admin_config'));
            $url = $_url->evaluate();
            $url .= '#group_UPLOAD';
            $message = do_lang_tempcode('INVALID_FILE_TYPE_ADMIN', escape_html($ext), escape_html(str_replace(',', ', ', $_types)), escape_html($url));
        }
        if ($accept_errors) {
            require_code('site');
            attach_message($message, 'warn');
            return false;
        } else {
            warn_exit($message);
        }
    }

    return true;
}

/**
 * Delete an uploaded file from disk, if it's URL has changed (i.e. it's been replaced, leaving a redundant disk file).
 * This MUST be run before the edit/delete operation, as it scans for the existing value to know what is changing.
 *
 * @param  string $upload_path The path to the upload directory
 * @param  ID_TEXT $table The table name
 * @param  ID_TEXT $field The table field name
 * @param  mixed $id_field The table ID field name, or a map array
 * @param  mixed $id The table ID
 * @param  ?string $new_url The new URL to use (null: deleting without replacing: no change check)
 */
function delete_upload($upload_path, $table, $field, $id_field, $id, $new_url = null)
{
    // Try and delete the file
    if ((has_actual_page_access(get_member(), 'admin_cleanup')) || (get_option('cleanup_files') == '1')) { // This isn't really a permission - more a failsafe in case there is a security hole. Staff can cleanup leftover files from the Cleanup module anyway. NB: Also repeated in cms_galleries.php.
        $where = is_array($id_field) ? $id_field : array($id_field => $id);
        $url = $GLOBALS['SITE_DB']->query_select_value_if_there($table, filter_naughty_harsh($field), $where);
        if (empty($url)) {
            return;
        }

        if (($new_url === null) || ((($url != $new_url) && (rawurldecode($url) != rawurldecode($new_url))) && ($new_url != STRING_MAGIC_NULL))) {
            if ((url_is_local($url)) && (substr($url, 0, strlen($upload_path) + 1) == $upload_path . '/')) {
                $count = $GLOBALS['SITE_DB']->query_select_value($table, 'COUNT(*)', array($field => $url));

                if ($count <= 1) {
                    @unlink(get_custom_file_base() . '/' . rawurldecode($url));
                    sync_file(rawurldecode($url));
                }
            }
            if ((url_is_local($url)) && (substr($url, 0, strlen('themes/default/images_custom') + 1) == 'themes/default/images_custom/')) {
                require_code('themes2');
                tidy_theme_img_code($new_url, $url, $table, $field, $GLOBALS['SITE_DB']);
            }
        }
    }
}

/**
 * Check bandwidth usage against page view ratio for shared hosting.
 *
 * @param  integer $extra The extra bandwidth requested
 */
function check_shared_bandwidth_usage($extra)
{
    global $SITE_INFO;
    if (!empty($SITE_INFO['throttle_bandwidth_registered'])) {
        $views_till_now = intval(get_value('page_views'));
        $bandwidth_allowed = $SITE_INFO['throttle_bandwidth_registered'];
        $total_bandwidth = intval(get_value('download_bandwidth'));
        if ($bandwidth_allowed * 1024 * 1024 >= $total_bandwidth + $extra) {
            return;
        }
    }
    if (!empty($SITE_INFO['throttle_bandwidth_complementary'])) {
        // $timestamp_start = $SITE_INFO['custom_user_'] . current_share_user(); Actually we'll do by views
        // $days_till_now = (time() - $timestamp_start) / (24 * 60 * 60);
        $views_till_now = intval(get_value('page_views'));
        $bandwidth_allowed = $SITE_INFO['throttle_bandwidth_complementary'] + $SITE_INFO['throttle_bandwidth_views_per_meg'] * $views_till_now;
        $total_bandwidth = intval(get_value('download_bandwidth'));
        if ($bandwidth_allowed * 1024 * 1024 < $total_bandwidth + $extra) {
            critical_error('RELAY', 'The hosted user has exceeded their shared-hosting "bandwidth-limit to page-view" ratio. More pages must be viewed before this may be downloaded.');
        }
    }
}

/**
 * Check disk space usage against page view ratio for shared hosting.
 *
 * @param  integer $extra The extra space in bytes requested
 */
function check_shared_space_usage($extra)
{
    global $SITE_INFO;
    if (!empty($SITE_INFO['throttle_space_registered'])) {
        $views_till_now = intval(get_value('page_views'));
        $bandwidth_allowed = $SITE_INFO['throttle_space_registered'];
        $total_space = get_directory_size(get_custom_file_base() . '/uploads');
        if ($bandwidth_allowed * 1024 * 1024 >= $total_space + $extra) {
            return;
        }
    }
    if (!empty($SITE_INFO['throttle_space_complementary'])) {
        // $timestamp_start = $SITE_INFO['custom_user_'] . current_share_user(); Actually we'll do by views
        // $days_till_now = (time() - $timestamp_start) / (24 * 60 * 60);
        $views_till_now = intval(get_value('page_views'));
        $space_allowed = $SITE_INFO['throttle_space_complementary'] + $SITE_INFO['throttle_space_views_per_meg'] * $views_till_now;
        $total_space = get_directory_size(get_custom_file_base() . '/uploads');
        if ($space_allowed * 1024 * 1024 < $total_space + $extra) {
            critical_error('RELAY', 'The hosted user has exceeded their shared-hosting "disk-space to page-view" ratio. More pages must be viewed before this may be uploaded.');
        }
    }
}

/*
TODO #3032
Define: clean_csv_file();

We should ideally be able to handle any of these scenarios, using a clean API:
1) User accidentally uploads CSV with tab delimiter (TSV) not proper CSV
2) User accidentally uploads CSV with semicolon delimiter not proper CSV
3) User uploads a spreadsheet with no header row in a situation where we don't strictly need one (newsletter raw e-mail list import comes to mind)
4) User uploads a CSV with old-Mac-style line endings (this should not happen in 2016 but some aging codebases export bizarre stuff; the PHP enable auto_detect_line_endings setting may help)
5) User uploads a spreadsheet with a different character set, esp UTF-16 or ANSI (to solve this we'll probably need to have a character-set selector box and also support BOM markers)

Define some automated tests
*/

/*
TODO: #3467
Document assumptions in automated test / documentation
 Composr is all using ASCII
 User content may be in any character set, and must be dealt with; although cms_file_get_contents_safe will always do conversions automatically if given a parameter to
 If user content has no BOM then it will be assumed to be in the site's default character set. User's should save with BOMs to make it clear!
 Content saved back into Composr from Composr will not need BOM markers as it's going to default to read with what it was saved as
 Exported content needs to be converted to utf-8 with BOM
*/
