<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    commandr
 */

// Opposite would be executing SQL with a '@' prefix.

/**
 * Hook class.
 */
class Hook_commandr_command_sql_dump
{
    /**
     * Run function for Commandr hooks.
     *
     * @param  array $options The options with which the command was called
     * @param  array $parameters The parameters with which the command was called
     * @param  object $commandr_fs A reference to the Commandr filesystem object
     * @return array Array of stdcommand, stdhtml, stdout, and stderr responses
     */
    public function run($options, $parameters, &$commandr_fs)
    {
        if ((array_key_exists('h', $options)) || (array_key_exists('help', $options))) {
            return array('', do_command_help('sql_dump', array('h'), array(true, true)), '', '');
        } else {
            $intended_db_type = empty($parameters[0]) ? get_db_type() : $parameters[0];

            if (count($parameters) > 2) {
                $only = array();
                for ($i = 2; $i < count($parameters); $i++) {
                    $only[] = $parameters[$i];
                }
            } else {
                $only = null;
            }

            // Where to save dump
            if (isset($parameters[1])) {
                $out_filename = $parameters[1];
            } else {
                $out_filename = 'dump_' . uniqid('', true) . '.sql';
            }
            $out_file_path = get_custom_file_base() . '/exports/backups/' . $out_filename;

            // Generate dump
            require_code('database_relations');
            $out_file = fopen($out_file_path, 'wb');
            fwrite($out_file, chr(hexdec('EF')) . chr(hexdec('BB')) . chr(hexdec('BF')));
            get_sql_dump($out_file, true, false, array(), $only, null, $intended_db_type);
            fclose($out_file);
            sync_file($out_file_path);
            fix_permissions($out_file_path);

            $out = do_lang('SQL_DUMP_SAVED_TO', escape_html('exports/backups/' . $out_filename), escape_html(get_custom_base_url() . '/exports/backups/' . rawurlencode($out_filename)));

            return array('', $out, '', '');
        }
    }
}
