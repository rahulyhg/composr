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
 * @package    core_database_drivers
 */

/**
 * Base class for MySQL database drivers.
 *
 * @package    core_database_drivers
 */
class Database_super_mysql
{
    /**
     * Adjust an SQL query to apply offset/limit restriction.
     *
     * @param  string $query The complete SQL query
     * @param  ?integer $max The maximum number of rows to affect (null: no limit)
     * @param  ?integer $start The start row to affect (null: no specification)
     */
    public function apply_sql_limit_clause(&$query, $max = null, $start = 0)
    {
        if (($max !== null) && ($start !== null)) {
            $query .= ' LIMIT ' . strval($start) . ',' . strval($max);
        } elseif ($max !== null) {
            $query .= ' LIMIT ' . strval($max);
        } elseif ($start !== null) {
            $query .= ' LIMIT ' . strval($start) . ',30000000';
        }
    }

    /**
     * Find whether the database may run GROUP BY unfettered with restrictions on the SELECT'd fields having to be represented in it or aggregate functions
     *
     * @return boolean Whether it can
     */
    public function can_arbitrary_groupby()
    {
        return true;
    }

    /**
     * Get the default user for making db connections (used by the installer as a default).
     *
     * @return string The default user for db connections
     */
    public function db_default_user()
    {
        return 'root';
    }

    /**
     * Get the default password for making db connections (used by the installer as a default).
     *
     * @return string The default password for db connections
     */
    public function db_default_password()
    {
        return '';
    }

    /**
     * Get SQL for creating a table index.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The index name (not really important at all)
     * @param  string $_fields Part of the SQL query: a comma-separated list of fields to use on the index
     * @param  array $db The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  string $unique_key_fields The name of the unique key field for the table
     * @return array List of SQL queries to run
     */
    public function db_create_index($table_name, $index_name, $_fields, $db, $raw_table_name, $unique_key_fields)
    {
        if ($index_name[0] == '#') {
            $index_name = substr($index_name, 1);
            $type = 'FULLTEXT';
        } else {
            $type = 'INDEX';
        }
        return array('ALTER TABLE ' . $table_name . ' ADD ' . $type . ' ' . $index_name . ' (' . $_fields . ')');
    }

    /**
     * Change the primary key of a table.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  array $new_key A list of fields to put in the new key
     * @param  array $db The DB connection to make on
     */
    public function db_change_primary_key($table_name, $new_key, $db)
    {
        $this->db_query('ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY, ADD PRIMARY KEY (' . implode(',', $new_key) . ')', $db);
    }

    /**
     * Get the number of rows in a table, with approximation support for performance (if necessary on the particular database backend).
     *
     * @param string $table The table name
     * @param array $where WHERE clauses if it will help get a more reliable number when we're not approximating in map form
     * @param string $where_clause WHERE clauses if it will help get a more reliable number when we're not approximating in SQL form
     * @param object $db The DB connection to check against
     * @return ?integer The count (null: do it normally)
     */
    public function get_table_count_approx($table, $where, $where_clause, $db)
    {
        if (get_value('slow_counts') === '1') {
            $sql = 'SELECT TABLE_ROWS FROM information_schema.tables WHERE table_schema=DATABASE() AND TABLE_NAME=\'' . $db->get_table_prefix() . $table . '\'';
            return $db->query_value_if_there($sql, false, true);
        }

        return null;
    }

    /**
     * Assemble part of a WHERE clause for doing full-text search
     *
     * @param  string $content Our match string (assumes "?" has been stripped already)
     * @param  boolean $boolean Whether to do a boolean full text search
     * @return string Part of a WHERE clause for doing full-text search
     */
    public function db_full_text_assemble($content, $boolean)
    {
        static $stopwords = null;
        if (is_null($stopwords)) {
            require_code('database_search');
            $stopwords = get_stopwords_list();
        }
        if (isset($stopwords[trim(strtolower($content), '"')])) {
            // This is an imperfect solution for searching for a stop-word
            // It will not cover the case where the stop-word is within the wider text. But we can't handle that case efficiently anyway
            return db_string_equal_to('?', trim($content, '"'));
        }

        if (!$boolean) {
            // These just cause muddling during full-text natural search
            $content = str_replace('"', '', $content);
            $content = str_replace('?', '', $content);
            db_escape_string($content); // Hack to so SQL injection detector doesn't get confused

            if ((strtoupper($content) == $content) && (!is_numeric($content))) {
                return 'MATCH (?) AGAINST (_latin1\'' . $this->db_escape_string($content) . '\' COLLATE latin1_general_cs)';
            }
            return 'MATCH (?) AGAINST (\'' . $this->db_escape_string($content) . '\')';
        }

        // These risk parse errors during full-text natural search and aren't supported for Composr searching
        $content = str_replace(array('>', '<', '(', ')', '~', '?', '@'), array('', '', '', '', '', '', ''), $content); // Risks parse error and not supported
        $content = preg_replace('#([\-+])[\-+]+#', '$1', $content); // Parse error if repeated on some servers
        $content = preg_replace('#[\-+]($|\s)#', '$1', $content); // Parse error if on end on some servers
        $content = preg_replace('#(^|\s)\*#', '$1', $content); // Parse error if on start on some servers
        db_escape_string($content); // Hack to so SQL injection detector doesn't get confused

        return 'MATCH (?) AGAINST (\'' . $this->db_escape_string($content) . '\' IN BOOLEAN MODE)';
    }

    /**
     * Get the ID of the first row in an auto-increment table (used whenever we need to reference the first).
     *
     * @return integer First ID used
     */
    public function db_get_first_id()
    {
        return 1;
    }

    /**
     * Get a map of Composr field types, to actual database types.
     *
     * @return array The map
     */
    public function db_get_type_remap()
    {
        $type_remap = array(
            'AUTO' => 'integer unsigned auto_increment',
            'AUTO_LINK' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing (NB: *_TRANS is signed, so trans fields are not perfectly AUTO_LINK compatible and can have double the positive range -- in the real world it will not matter though)
            'INTEGER' => 'integer',
            'UINTEGER' => 'integer unsigned',
            'SHORT_INTEGER' => 'tinyint',
            'REAL' => 'real',
            'BINARY' => 'tinyint(1)',
            'MEMBER' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing
            'GROUP' => 'integer', // not unsigned because it's useful to have -ve for temporary usage while importing
            'TIME' => 'integer unsigned',
            'LONG_TRANS' => 'integer unsigned',
            'SHORT_TRANS' => 'integer unsigned',
            'LONG_TRANS__COMCODE' => 'integer',
            'SHORT_TRANS__COMCODE' => 'integer',
            'SHORT_TEXT' => 'varchar(255)',
            'LONG_TEXT' => 'longtext',
            'ID_TEXT' => 'varchar(80)',
            'MINIID_TEXT' => 'varchar(40)',
            'IP' => 'varchar(40)', // 15 for ip4, but we now support ip6
            'LANGUAGE_NAME' => 'varchar(5)',
            'URLPATH' => 'varchar(255) BINARY',
        );
        return $type_remap;
    }

    /**
     * Get SQL for creating a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to Composr field types (with *#? encodings)
     * @param  array $db The DB connection to make on
     * @param  ID_TEXT $raw_table_name The table name with no table prefix
     * @param  boolean $save_bytes Whether to use lower-byte table storage, with tradeoffs of not being able to support all unicode characters; use this if key length is an issue
     * @return array List of SQL queries to run
     */
    public function db_create_table($table_name, $fields, $db, $raw_table_name, $save_bytes = false)
    {
        $type_remap = $this->db_get_type_remap();

        $_fields = '';
        $keys = '';
        foreach ($fields as $name => $type) {
            if ($type[0] == '*') { // Is a key
                $type = substr($type, 1);
                if ($keys !== '') {
                    $keys .= ', ';
                }
                $keys .= $name;
            }

            if ($type[0] == '?') { // Is perhaps null
                $type = substr($type, 1);
                $perhaps_null = 'NULL';
            } else {
                $perhaps_null = 'NOT NULL';
            }

            $type = isset($type_remap[$type]) ? $type_remap[$type] : $type;

            $_fields .= '    ' . $name . ' ' . $type;
            /*if (substr($name, -13) == '__text_parsed') {    BLOB/TEXT column 'description__text_parsed' can't have a default value
                $_fields .= ' DEFAULT \'\'';
            } else*/
            if (substr($name, -13) == '__source_user') {
                $_fields .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $_fields .= ' ' . $perhaps_null . ',' . "\n";
        }

        $innodb = ((function_exists('get_value')) && (get_value('innodb') == '1'));
        $table_type = ($innodb ? 'INNODB' : 'MyISAM');
        $type_key = 'engine';
        /*if ($raw_table_name == 'sessions') {
            $table_type = 'HEAP';   Some MySQL servers are very regularly reset
        }*/

        $query = 'CREATE TABLE ' . $table_name . ' (' . "\n" . $_fields . '    PRIMARY KEY (' . $keys . ")\n)";

        global $SITE_INFO;
        if (empty($SITE_INFO['database_charset'])) {
            $SITE_INFO['database_charset'] = (get_charset() == 'utf-8') ? 'utf8mb4' : 'latin1';
        }
        $charset = $SITE_INFO['database_charset'];
        if ($charset == 'utf8mb4' && $save_bytes) {
            $charset = 'utf8';
        }

        $query .= ' CHARACTER SET=' . preg_replace('#\_.*$#', '', $charset);

        $query .= ' ' . $type_key . '=' . $table_type;

        return array($query);
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function db_string_equal_to($attribute, $compare)
    {
        return $attribute . "='" . db_escape_string($compare) . "'";
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are not equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function db_string_not_equal_to($attribute, $compare)
    {
        return $attribute . "<>'" . db_escape_string($compare) . "'";
    }

    /**
     * Find whether expression ordering support is present
     *
     * @param  array $db A DB connection
     * @return boolean Whether it is
     */
    public function db_has_expression_ordering($db)
    {
        return true;
    }

    /**
     * This function is internal to the database system, allowing SQL statements to be build up appropriately. Some databases require IS NULL to be used to check for blank strings.
     *
     * @return boolean Whether a blank string IS NULL
     */
    public function db_empty_is_null()
    {
        return false;
    }

    /**
     * Find whether table truncation support is present
     *
     * @return boolean Whether it is
     */
    public function db_supports_truncate_table()
    {
        return true;
    }

    /**
     * Find whether drop table "if exists" is present
     *
     * @return boolean Whether it is
     */
    public function db_supports_drop_table_if_exists()
    {
        return true;
    }

    /**
     * Delete a table.
     *
     * @param  ID_TEXT $table The table name
     * @param  array $db The DB connection to delete on
     * @return array List of SQL queries to run
     */
    public function db_drop_table_if_exists($table, $db)
    {
        return array('DROP TABLE IF EXISTS ' . $table);
    }

    /**
     * Determine whether the database is a flat file database, and thus not have a meaningful connect username and password.
     *
     * @return boolean Whether the database is a flat file database
     */
    public function db_is_flat_file_simple()
    {
        return false;
    }

    /**
     * Encode a LIKE string comparision fragement for the database system. The pattern is a mixture of characters and ? and % wildcard symbols.
     *
     * @param  string $pattern The pattern
     * @return string The encoded pattern
     */
    public function db_encode_like($pattern)
    {
        return str_replace('\\\\_'/*MySQL escaped underscores*/, '\\_', $this->db_escape_string($pattern));
    }

    /**
     * Close the database connections. We don't really need to close them (will close at exit), just disassociate so we can refresh them.
     */
    public function db_close_connections()
    {
        $this->cache_db = array();
        $this->last_select_db = null;
    }

    /**
     * Create an SQL cast.
     *
     * @param string $field The field identifier
     * @param string $type The type wanted
     * @set CHAR INT FLOAT
     * @return string The database type
     */
    public function db_cast($field, $type)
    {
        switch ($type) {
            case 'CHAR':
                $_type = $type . '(65535)';
                break;

            case 'INT':
                $_type = 'SIGNED INTEGER';
                break;

            case 'FLOAT':
                $_type = 'DECIMAL';
                break;

            default:
                fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }

        return 'CAST(' . $field . ' AS ' . $_type . ')';
    }
}
