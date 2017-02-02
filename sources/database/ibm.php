<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: odbc\_.+*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_database_drivers
 */

/*
This driver works by ODBC. You create a mdb database in access, then create a mapping in the
ODBC part of control panel. You need to add a 'System DSN' (the DSN is the database name mapping
to the mdb file). In the properties there is option to choose username and password.
*/

/**
 * Database Driver.
 *
 * @package    core_database_drivers
 */
class Database_Static_ibm extends DatabaseDriver
{
    public $cache_db = array();

    /**
     * Get the default user for making db connections (used by the installer as a default).
     *
     * @return string The default user for db connections
     */
    public function default_user()
    {
        return ''; // db2admin  ... ODBC does not need to use this
    }

    /**
     * Get the default password for making db connections (used by the installer as a default).
     *
     * @return string The default password for db connections
     */
    public function default_password()
    {
        return '';
    }

    /**
     * Get a database connection. This function shouldn't be used by you, as a connection to the database is established automatically.
     *
     * @param  boolean $persistent Whether to create a persistent connection
     * @param  string $db_name The database name
     * @param  string $db_host The database host (the server)
     * @param  string $db_user The database connection username
     * @param  string $db_password The database connection password
     * @param  boolean $fail_ok Whether to on error echo an error and return with a null, rather than giving a critical error
     * @return ?array A database connection (null: failed)
     */
    public function get_connection($persistent, $db_name, $db_host, $db_user, $db_password, $fail_ok = false)
    {
        if ($db_host != 'localhost') {
            fatal_exit(do_lang_tempcode('ONLY_LOCAL_HOST_FOR_TYPE'));
        }

        // Potential caching
        if (isset($this->cache_db[$db_name][$db_host])) {
            return $this->cache_db[$db_name][$db_host];
        }

        if (!function_exists('odbc_connect')) {
            $error = 'The ODBC PHP extension not installed (anymore?). You need to contact the system administrator of this server.';
            if ($fail_ok) {
                echo $error;
                return null;
            }
            critical_error('PASSON', $error);
        }

        $connection = $persistent ? @odbc_pconnect($db_name, $db_user, $db_password) : @odbc_connect($db_name, $db_user, $db_password);
        if ($connection === false) {
            $error = 'Could not connect to database-server (' . odbc_errormsg() . ')';
            if ($fail_ok) {
                echo $error;
                return null;
            }
            critical_error('PASSON', $error); //warn_exit(do_lang_tempcode('CONNECT_DB_ERROR'));
        }

        if (!$connection) {
            fatal_exit(do_lang('CONNECT_DB_ERROR'));
        }
        $this->cache_db[$db_name][$db_host] = $connection;
        return $connection;
    }

    /**
     * Get a map of Composr field types, to actual database types.
     *
     * @return array The map
     */
    public function get_type_remap()
    {
        $type_remap = array(
            'AUTO' => 'integer generated by default as identity',
            'AUTO_LINK' => 'integer',
            'INTEGER' => 'integer',
            'UINTEGER' => 'bigint',
            'SHORT_INTEGER' => 'smallint',
            'REAL' => 'real',
            'BINARY' => 'smallint',
            'MEMBER' => 'integer',
            'GROUP' => 'integer',
            'TIME' => 'bigint',
            'LONG_TRANS' => 'bigint',
            'SHORT_TRANS' => 'bigint',
            'LONG_TRANS__COMCODE' => 'integer',
            'SHORT_TRANS__COMCODE' => 'integer',
            'SHORT_TEXT' => 'text',
            'LONG_TEXT' => 'clob',
            'ID_TEXT' => 'varchar(80)',
            'MINIID_TEXT' => 'varchar(40)',
            'IP' => 'varchar(40)',
            'LANGUAGE_NAME' => 'varchar(5)',
            'URLPATH' => 'varchar(255)',
        );
        return $type_remap;
    }

    /**
     * Create a new table.
     *
     * @param  ID_TEXT $table_name The table name
     * @param  array $fields A map of field names to Composr field types (with *#? encodings)
     * @param  array $connection The DB connection to make on
     */
    public function create_table($table_name, $fields, $connection)
    {
        $type_remap = $this->get_type_remap();

        $_fields = '';
        $keys = '';
        foreach ($fields as $name => $type) {
            if ($type[0] == '*') { // Is a key
                $type = substr($type, 1);
                if ($keys != '') {
                    $keys .= ', ';
                }
                $keys .= $name;
            }

            if ($type[0] == '?') { // Is perhaps null
                $type = substr($type, 1);
                $perhaps_null = '';
            } else {
                $perhaps_null = 'NOT NULL';
            }

            $type = isset($type_remap[$type]) ? $type_remap[$type] : $type;

            $_fields .= '    ' . $name . ' ' . $type;
            if (substr($name, -13) == '__text_parsed') {
                $_fields .= ' DEFAULT \'\'';
            } elseif (substr($name, -13) == '__source_user') {
                $_fields .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $_fields .= ' ' . $perhaps_null . ',' . "\n";
        }

        $query = 'CREATE TABLE ' . $table_name . ' (
          ' . $_fields . '
          PRIMARY KEY (' . $keys . ')
        )';
        $this->query($query, $connection, null, null);
    }

    /**
     * Create a table index.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  ID_TEXT $index_name The index name (not really important at all)
     * @param  string $_fields Part of the SQL query: a comma-separated list of fields to use on the index
     * @param  array $connection The DB connection to make on
     */
    public function create_index($table_name, $index_name, $_fields, $connection)
    {
        if ($index_name[0] == '#') {
            return;
        }
        $this->query('CREATE INDEX index' . $index_name . '_' . strval(mt_rand(0, mt_getrandmax())) . ' ON ' . $table_name . '(' . $_fields . ')', $connection);
    }

    /**
     * Change the primary key of a table.
     *
     * @param  ID_TEXT $table_name The name of the table to create the index on
     * @param  array $new_key A list of fields to put in the new key
     * @param  array $connection The DB connection to make on
     */
    public function change_primary_key($table_name, $new_key, $connection)
    {
        $this->query('ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY', $connection);
        $this->query('ALTER TABLE ' . $table_name . ' ADD PRIMARY KEY (' . implode(',', $new_key) . ')', $connection);
    }

    /**
     * This function is a very basic query executor. It shouldn't usually be used by you, as there are abstracted versions available.
     *
     * @param  string $query The complete SQL query
     * @param  array $connection A DB connection
     * @param  ?integer $max The maximum number of rows to affect (null: no limit)
     * @param  ?integer $start The start row to affect (null: no specification)
     * @param  boolean $fail_ok Whether to output an error on failure
     * @param  boolean $get_insert_id Whether to get the autoincrement ID created for an insert query
     * @return ?mixed The results (null: no results), or the insert ID
     */
    public function query($query, $connection, $max = null, $start = null, $fail_ok = false, $get_insert_id = false)
    {
        if ($max !== null) {
            if ($start === null) {
                $max += $start;
            }

            if ((strtoupper(substr($query, 0, 7)) == 'SELECT ') || (strtoupper(substr($query, 0, 8)) == '(SELECT ')) { // Unfortunately we can't apply to DELETE FROM and update :(. But its not too important, LIMIT'ing them was unnecessarily anyway
                $query .= ' FETCH FIRST ' . strval($max + $start) . ' ROWS ONLY';
            }
        }

        $results = @odbc_exec($connection, $query);
        if (($results === false) && (!$fail_ok)) {
            $err = odbc_errormsg($connection);
            if (function_exists('ocp_mark_as_escaped')) {
                ocp_mark_as_escaped($err);
            }
            if ((!running_script('upgrader')) && (!get_mass_import_mode())) {
                if ((!function_exists('do_lang')) || (do_lang('QUERY_FAILED', null, null, null, null, false) === null)) {
                    $this->failed_query_exit(htmlentities('Query failed: ' . $query . ' : ' . $err));
                }

                $this->failed_query_exit(do_lang_tempcode('QUERY_FAILED', escape_html($query), ($err)));
            } else {
                $this->failed_query_echo(htmlentities('Database query failed: ' . $query . ' [') . ($err) . htmlentities(']'));
                return null;
            }
        }

        if ((strtoupper(substr($query, 0, 7)) == 'SELECT ') || (strtoupper(substr($query, 0, 8)) == '(SELECT ') && (!$results !== false)) {
            return $this->get_query_rows($results);
        }

        if ($get_insert_id) {
            if (strtoupper(substr($query, 0, 7)) == 'UPDATE ') {
                return null;
            }

            $pos = strpos($query, '(');
            $table_name = substr($query, 12, $pos - 13);

            $res2 = odbc_exec($connection, 'SELECT MAX(id) FROM ' . $table_name);
            $ar2 = odbc_fetch_row($res2);
            return $ar2[0];
        }

        return null;
    }

    /**
     * Get the rows returned from a SELECT query.
     *
     * @param  resource $results The query result pointer
     * @param  ?integer $start Whether to start reading from (null: irrelevant for this forum driver)
     * @return array A list of row maps
     */
    public function get_query_rows($results, $start = null)
    {
        $out = array();
        $i = 0;

        $num_fields = odbc_num_fields($results);
        $types = array();
        $names = array();
        for ($x = 1; $x <= $num_fields; $x++) {
            $types[$x] = odbc_field_type($results, $x);
            $names[$x] = odbc_field_name($results, $x);
        }

        while (odbc_fetch_row($results)) {
            if (($start === null) || ($i >= $start)) {
                $newrow = array();

                for ($j = 1; $j <= $num_fields; $j++) {
                    $v = odbc_result($results, $j);

                    $type = $types[$j];
                    $name = strtolower($names[$j]);

                    if (($type == 'INTEGER') || ($type == 'SMALLINT') || ($type == 'UINTEGER')) {
                        if ($v !== null) {
                            $newrow[$name] = intval($v);
                        } else {
                            $newrow[$name] = null;
                        }
                    } else {
                        $newrow[$name] = $v;
                    }
                }

                $out[] = $newrow;
            }

            $i++;
        }
        odbc_free_result($results);
        return $out;
    }

    /**
     * Encode an SQL statement fragment for a conditional to see if two strings are equal.
     *
     * @param  ID_TEXT $attribute The attribute
     * @param  string $compare The comparison
     * @return string The SQL
     */
    public function string_equal_to($attribute, $compare)
    {
        return $attribute . " LIKE '" . $this->escape_string($compare) . "'";
    }

    /**
     * Escape a string so it may be inserted into a query. If SQL statements are being built up and passed using db_query then it is essential that this is used for security reasons. Otherwise, the abstraction layer deals with the situation.
     *
     * @param  string $string The string
     * @return string The escaped string
     */
    public function escape_string($string)
    {
        $string = fix_bad_unicode($string);
        return str_replace("'", "''", $string);
    }

    /**
     * Close the database connections. We don't really need to close them (will close at exit), just disassociate so we can refresh them.
     */
    public function close_connections()
    {
        foreach ($this->cache_db as $connection) {
            foreach ($connection as $_db) {
                odbc_commit($_db);
            }
        }
    }
}
