<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: stream_wrapper_register|stream_wrapper_unregister*/

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
function init__debug_fs()
{
    if (!defined('DEBUG_FS__SLOW')) {
        define('DEBUG_FS__SLOW', 1);
        define('DEBUG_FS__CASE_SENSITIVE', 2);
    }

    global $DEBUG_FS__LOG_FILE;
    $DEBUG_FS__LOG_FILE = null;
    if (is_file(get_file_base() . '/data_custom/debug_fs.log')) {
        $DEBUG_FS__LOG_FILE = fopen(get_file_base() . '/data_custom/debug_fs.log', 'ab');
        register_shutdown_function('close_debug_fs');
    }
}

/**
 * Enable the debug file system.
 */
function enable_debug_fs()
{
    global $DEBUG_FS__BEHAVIOURS;
    $DEBUG_FS__BEHAVIOURS = DEBUG_FS__SLOW | DEBUG_FS__CASE_SENSITIVE;

    global $DEBUG_FS__LATENCY;
    $DEBUG_FS__LATENCY = 0;
    $kdfs = get_param_integer('keep_debug_fs', 0);
    if ($kdfs > 1) {
        $DEBUG_FS__LATENCY = $kdfs * 1000;
    }

    global $FILE_BASE;
    $FILE_BASE = 'debugfs://' . $FILE_BASE;

    @stream_wrapper_unregister('debugfs');
    stream_wrapper_register('debugfs', 'DebugFsStreamWrapper');
}

/**
 * Close down the debug file system log.
 */
function close_debug_fs()
{
    global $DEBUG_FS__LOG_FILE;
    if ($DEBUG_FS__LOG_FILE !== null) {
        fclose($DEBUG_FS__LOG_FILE);
        $DEBUG_FS__LOG_FILE = null;
    }
}

/**
 * A filesystem wrapper that adds some additional restrictions,
 * so that this runs as a common denominator of any file system's limitations.
 * Useful if developing on Mac/Windows with an SSD!
 *
 * @package    core
 */
class DebugFsStreamWrapper
{
    public $context = null;

    /**
     * Construct our wrapper.
     */
    public function __construct()
    {
        $this->context = stream_context_create();
    }

    /**
     * Deconstruct our wrapper.
     */
    public function __destruct()
    {
    }

    /**
     * Find if a path exists but with a case sensitivity issue.
     *
     * @param  PATH $path The path.
     * @return boolean Whether there is an issue.
     */
    protected function has_path_case_issue($path)
    {
        global $DEBUG_FS__BEHAVIOURS;
        if (($DEBUG_FS__BEHAVIOURS & DEBUG_FS__CASE_SENSITIVE) != 0) {
            return false;
        }

        static $results = array();

        if (($path == '') || ($path == '.')) {
            return false;
        }

        if (isset($results[$path])) {
            return $results[$path];
        }

        $has_issue = false;

        $dirname = dirname($path);
        $basename = basename($path);
        $dh = @opendir($dirname);
        if ($dh !== false) {
            while (($f = readdir($dh)) !== false) {
                if (strcasecmp($f, $basename) == 0) {
                    if ($f != $basename) {
                        $has_issue = true;
                    }
                }
            }

            closedir($dh);
        }

        if ($has_issue) {
            trigger_error('File path has a case sensitivity problem, ' . $path, E_USER_WARNING);
        }

        $results[$path] = $has_issue;

        return $has_issue;
    }

    /**
     * Strip back a path so the protocol handler is not on it.
     *
     * @param  string $path Path.
     */
    protected function strip_back_path(&$path)
    {
        $path = preg_replace('#^debugfs://#', '', $path);
    }

    /**
     * Called at the start of filesystem wrapper calls, so we can use as a debugging point.
     *
     * @param  string $function The function call that is happening.
     * @param  ?PATH $path Path (null: N/A).
     * @param  boolean $slowdown Whether to apply an automatic slow-down.
     */
    protected function init_call($function, $path = null, $slowdown = false)
    {
        global $DEBUG_FS__LOG_FILE;
        if ($DEBUG_FS__LOG_FILE !== null) {
            $line = date('Y-m-d H:i:s') . ' ' . cms_srv('REQUEST_URI');
            $line .= ' - ' . $function;
            if ($path !== null) {
                $line .= ' - ' . $path;
            }
            if ($slowdown) {
                $line .= ' - ADDS LATENCY';
            }
            fwrite($DEBUG_FS__LOG_FILE, $line . "\n");
        }

        if ($slowdown) {
            $this->apply_slowdown();
        }
    }

    /**
     * Apply internal slow-down as required.
     */
    protected function apply_slowdown()
    {
        global $DEBUG_FS__BEHAVIOURS, $DEBUG_FS__LATENCY;
        if ((($DEBUG_FS__BEHAVIOURS & DEBUG_FS__SLOW) != 0) && (php_function_allowed('usleep'))) {
            usleep($DEBUG_FS__LATENCY);
        }
    }

    /* Directory operations */

    protected $directory_handle = false;

    /**
     * Open a directory for analysis.
     *
     * @param  PATH $path The path to the directory to open.
     * @param  boolean $options Bitmask options.
     * @return boolean Success status.
     */
    public function dir_opendir($path, $options)
    {
        $this->init_call('dir_opendir', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        $this->directory_handle = @opendir($path, $this->context);
        return ($this->directory_handle !== false);
    }

    /**
     * Read entry from directory handle.
     *
     * @return ~string Next filename (false: error).
     */
    public function dir_readdir()
    {
        $this->init_call('dir_readdir');

        if ($this->directory_handle === false) {
            return false;
        }

        return @readdir($this->directory_handle);
    }

    /**
     * Rewind directory handle.
     *
     * @return boolean Success status.
     */
    public function dir_rewinddir()
    {
        $this->init_call('dir_rewinddir');

        if ($this->directory_handle === false) {
            return false;
        }

        @rewinddir($this->directory_handle);
        return true;
    }

    /**
     * Close directory handle.
     *
     * @return boolean Success status.
     */
    public function dir_closedir()
    {
        $this->init_call('dir_closedir');

        if ($this->directory_handle === false) {
            return false;
        }

        @closedir($this->directory_handle);
        $this->directory_handle = false;
        return true;
    }

    /**
     * Makes a directory. {{creates-file}}
     *
     * @param  PATH $path The path to the directory to make.
     * @param  integer $mode The mode (e.g. 0777).
     * @param  integer $options Bitmask options.
     * @return boolean Success status.
     */
    public function mkdir($path, $mode, $options)
    {
        $this->init_call('mkdir', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue(dirname($path))) {
            return false;
        }

        return @mkdir($path, $mode, ($options & STREAM_MKDIR_RECURSIVE) != 0, $this->context);
    }

    /**
     * Removes directory.
     *
     * @param  PATH $path Directory path.
     * @param  boolean $options Bitmask options.
     * @return boolean Success status.
     */
    public function rmdir($path, $options)
    {
        $this->init_call('rmdir', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        return @rmdir($path, $this->context);
    }

    /* File operations */

    /**
     * Deletes a file.
     *
     * @param  PATH $path The file path.
     * @return boolean Success status.
     */
    public function unlink($path)
    {
        $this->init_call('unlink', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        return @unlink($path, $this->context);
    }

    /**
     * Gets information about a file.
     *
     * @param  PATH $path File path.
     * @param  boolean $flags Bitmask options.
     * @return ~array Map of status information (false: error).
     */
    public function url_stat($path, $flags)
    {
        $this->init_call('url_stat', $path);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        return @stat($path);
    }

    protected $file_handle = false;

    /**
     * Opens file or URL. {{creates-file}}
     *
     * @param  PATH $path Filename.
     * @param  string $mode Mode (e.g. at).
     * @param  integer $options Bitmask options.
     * @param  string $opened_path The real path will be written into here, if requested.
     * @return boolean Success status.
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->init_call('stream_open', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        if (($options & STREAM_REPORT_ERRORS) != 0) {
            $this->file_handle = fopen($path, $mode, false, $this->context);
        } else {
            $this->file_handle = @fopen($path, $mode, false, $this->context);
        }

        if (($options & STREAM_USE_PATH) != 0) {
            $opened_path = realpath($path);
        }

        return ($this->file_handle !== false);
    }

    /**
     * Binary-safe file read.
     *
     * @param  integer $count Maximum length to read.
     * @return ~string The read data (false: error).
     */
    public function stream_read($count)
    {
        $this->init_call('stream_read');

        if ($this->file_handle === false) {
            return false;
        }

        return @fread($this->file_handle, $count);
    }

    /**
     * Binary-safe file write.
     *
     * @param  string $data The string to write to the file.
     * @return ~integer The number of bytes written (false: error).
     */
    public function stream_write($data)
    {
        $this->init_call('stream_write');

        if ($this->file_handle === false) {
            return false;
        }

        return @fwrite($this->file_handle, $data);
    }

    /**
     * Truncates a file to a given length.
     *
     * @param  integer $new_size Cut off size.
     * @return boolean Success status.
     */
    public function stream_truncate($new_size)
    {
        $this->init_call('stream_truncate', null, true);

        if ($this->file_handle === false) {
            return false;
        }

        return @ftruncate($this->file_handle, $new_size);
    }

    /**
     * Seeks on a file pointer.
     *
     * @param  integer $offset The offset (meaning depends on whence).
     * @param  integer $whence SEEK_SET, SEEK_CUR or SEEK_END.
     * @return boolean Success status.
     */
    public function stream_seek($offset, $whence = 0/*SEEK_SET*/)
    {
        $this->init_call('stream_seek');

        if ($this->file_handle === false) {
            return false;
        }

        return (@fseek($this->file_handle, $offset, $whence) == 0);
    }

    /**
     * Gets file pointer read/write position.
     *
     * @return ~integer The offset (false: error).
     */
    public function stream_tell()
    {
        $this->init_call('stream_tell');

        if ($this->file_handle === false) {
            return false;
        }

        return @ftell($this->file_handle);
    }

    /**
     * Tests for end-of-file on a file pointer.
     *
     * @return boolean Whether the end of the file has been reached.
     */
    public function stream_eof()
    {
        $this->init_call('stream_eof');

        if ($this->file_handle === false) {
            return false;
        }

        return @feof($this->file_handle);
    }

    /**
     * Flushes the output to a file.
     *
     * @return boolean Success status.
     */
    public function stream_flush()
    {
        $this->init_call('stream_flush');

        if ($this->file_handle === false) {
            return false;
        }

        return @fflush($this->file_handle);
    }

    /**
     * Portable advisory file locking.
     *
     * @param  integer $operation Operation (LOCK_SH, LOCK_EX, LOCK_UN).
     * @return boolean Success status.
     */
    public function stream_lock($operation)
    {
        $this->init_call('stream_lock', null, true);

        if ($this->file_handle === false) {
            return false;
        }

        return @flock($this->file_handle, $operation);
    }

    /**
     * Change stream options.
     *
     * @param  integer $option Option being set.
     * @param  integer $arg1 1st argument.
     * @param  integer $arg2 2nd argument.
     * @return boolean Success status.
     */
    public function stream_set_option($option, $arg1, $arg2)
    {
        $this->init_call('stream_set_option');

        return false;
    }

    /**
     * Gets information about a file using an open file pointer.
     *
     * @return ~array Map of status information (false: error).
     */
    public function stream_stat()
    {
        $this->init_call('stream_stat', null);

        if ($this->file_handle === false) {
            return false;
        }

        return @fstat($this->file_handle);
    }

    /**
     * Closes an open file pointer.
     *
     * @return boolean Success status.
     */
    public function stream_close()
    {
        $this->init_call('stream_close');

        if ($this->file_handle === false) {
            return false;
        }

        @fclose($this->file_handle);
        $this->file_handle = false;
        return true;
    }

    /* File and Directory operations */

    /**
     * Renames a file.
     *
     * @param  PATH $path_from Old name.
     * @param  PATH $path_to New name.
     * @return boolean Success status.
     */
    public function rename($path_from, $path_to)
    {
        $this->init_call('rename', $path_from, true);

        $this->strip_back_path($path_from);
        $this->strip_back_path($path_to);

        if ($this->has_path_case_issue($path_from)) {
            return false;
        }
        if ($this->has_path_case_issue(dirname($path_to))) {
            return false;
        }

        return @rename($path_from, $path_to);
    }

    /**
     * Set metadata on a file.
     *
     * @param  PATH $path Path.
     * @param  integer $option What to set on.
     * @param  mixed $value Vaue to set.
     * @return boolean Success status.
     */
    public function stream_metadata($path, $option, $value)
    {
        $this->init_call('stream_metadata', $path, true);

        $this->strip_back_path($path);

        if ($this->has_path_case_issue($path)) {
            return false;
        }

        switch ($option) {
            case STREAM_META_TOUCH:
                return @touch($path, $value);

            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                if (php_function_allowed('chown')) {
                    return @chown($path, $value);
                }
                break;

            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                if (php_function_allowed('chgrp')) {
                    return @chgrp($path, $value);
                }
                break;

            case STREAM_META_ACCESS:
                return @chmod($path, $value);
        }

        return false;
    }
}
