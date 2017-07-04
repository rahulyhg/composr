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
This file provides some detection of possible security vulnerabilities at development time, so code can be hardened before delivery.
*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__database_security_filter()
{
    global $DB_ESCAPE_STRING_LIST;
    $DB_ESCAPE_STRING_LIST = array();
}

/**
 * Find if a query is a simple one involving 'AND' maps.
 *
 * @param  string $query The query
 * @return boolean Whether it is simple
 */
function is_simple_query($query)
{
    if (strpos($query, get_table_prefix()) === false) {
        return false;
    }

    $complex_keywords = array('ORDER' => true, 'GROUP' => true, 'AS' => true, 'OR' => true, 'NOT' => true, 'LIKE' => true, 'IN' => true, 'BETWEEN' => true, 'UNION' => true, 'HAVING' => true);
    $complex_operators = array('<', '>', '!', '+', '-', '/', '*');
    $query = _trim_quoted_substrings($query);
    $query_parts = explode(' ', $query);
    if (in_array(strtolower(trim($query_parts[0])), array('select', 'update', 'delete'))) {
        foreach ($query_parts as $part) {
            if (array_key_exists(strtoupper(trim($part)), $complex_keywords)) {
                return false;
            }
        }
        foreach ($complex_operators as $operator) {
            if (strpos($query, $operator) !== false) {
                return false;
            }
        }
        if (preg_match('/[a-z]\(/', strtolower($query))) {
            return false; // SQL functions
        }
        return true;
    }
    return false;
}

/**
 * Check all strings within a query were properly escaped (by checking log of what we escaped).
 *
 * @param  string $query The query
 * @return boolean Whether it is all good
 */
function has_escaped_dynamic_sql($query)
{
    if (substr(get_db_type(), 0, 5) != 'mysql') {
        // Our scanning may not work right on non-MySQL
        return true;
    }

    $query_call_strings = array('query(', 'query_value_if_there(');

    $strings = _get_quoted_substrings($query);
    foreach ($strings as $str) {
        if (!array_key_exists($str, $GLOBALS['DB_ESCAPE_STRING_LIST'])) { // Not explicitly escaped, so we scan the code to see if it was hard-coded in there
            foreach (debug_backtrace() as $backtrace_depth => $backtrace) {
                if ((isset($backtrace['file'])) && (file_exists($backtrace['file']))) {
                    $file = file($backtrace['file']);
                    $ok = false;
                    $found_query_line = false;
                    foreach ($query_call_strings as $query_call_string) {
                        $loc = $file[$backtrace['line'] - 1];
                        $offset = strpos($loc, $query_call_string);

                        if ($offset !== false) { // First do a fast check on the line itself
                            $found_query_line = true;

                            $_strings = _get_quoted_substrings(substr($loc, $offset), true);

                            if (in_array($str, $_strings)) {
                                $ok = true;
                            } else {
                                // Oh, maybe the string was somewhere escaped in the same file at least
                                $_strings = array();
                                foreach ($file as $line) {
                                    $_strings = array_merge($_strings, _get_quoted_substrings($line, true));
                                }
                                if (in_array($str, $_strings)) {
                                    $ok = true;
                                }
                            }

                            if ($ok) {
                                break 2;
                            }
                        }
                    }
                    if ((!$ok) && ($found_query_line)) {
                        //@var_dump($_strings);@exit($str); // Useful for debugging

                        return false; // :-(.
                    }
                }
            }
        }
    }
    return true; // :-)
}

/**
 * Find the quoted substrings within a query.
 *
 * @param  string $string The query
 * @param  boolean $recurse Whether to recurse (for double escaping)
 * @return array List of substrings
 *
 * @ignore
 */
function _get_quoted_substrings($string, $recurse = false)
{
    $buffer = '';
    $output = array();
    $found_start = false;
    $ignore = false;
    $len = strlen($string);
    for ($i = 0; $i < $len; $i++) {
        if (!$found_start && ($string[$i] == '\'')) {
            $found_start = true;
            continue;
        }
        if ($found_start) {
            if (($ignore !== $i/*If not escaped*/) && ($string[$i] == '\'')) { // We've found a string
                $output[] = trim($buffer, ' %');
                $buffer = '';
                $found_start = false; // We've closed our string, ready ourselves for next
                continue;
            }
            if (($ignore !== $i) && ($string[$i] == '\\')) {
                $ignore = $i + 1;
            }
            $buffer .= $string[$i];
        }
    }
    if ($recurse) {
        $_output = $output;
        $output = array();
        foreach ($_output as $str) {
            $output[] = $str;
            $output = array_merge($output, _get_quoted_substrings(stripcslashes($str)));
        }
    }
    return $output;
}

/**
 * Blank out substrings within a query, which makes it easier to analyse (no need to consider escapings).
 *
 * @param  string $string Input string
 * @return string Simplified substring
 *
 * @ignore
 */
function _trim_quoted_substrings($string)
{
    $found_start = false;
    $ignore = mixed();
    $len = strlen($string);
    for ($i = 0; $i < $len; $i++) {
        if (!$found_start && ($string[$i] == '\'')) { // We've found a string
            $found_start = true;
            continue;
        }
        if ($found_start) {
            if (($ignore !== $i/*If not escaped*/) && ($string[$i] == '\'')) {
                $found_start = false; // We've closed our string, ready ourselves for next
                continue;
            }
            if (($ignore !== $i) && ($string[$i] == '\\')) {
                $ignore = $i + 1;
            }
            $string[$i] = ' ';
        }
    }
    return $string;
}
