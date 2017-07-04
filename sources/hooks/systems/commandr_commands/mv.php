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
 * @package    commandr
 */

/**
 * Hook class.
 */
class Hook_commandr_command_mv
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
            return array('', do_command_help('mv', array('h'), array(true, true)), '', '');
        } else {
            if (!array_key_exists(0, $parameters)) {
                return array('', '', '', do_lang('MISSING_PARAM', '1', 'mv'));
            } else {
                $parameters[0] = $commandr_fs->_pwd_to_array($parameters[0]);
            }
            if (!array_key_exists(1, $parameters)) {
                $parameters[1] = $commandr_fs->print_working_directory(true);
            } else {
                $parameters[1] = $commandr_fs->_pwd_to_array($parameters[1]);
            }

            if (!$commandr_fs->_is_file($parameters[0])) {
                return array('', '', '', do_lang('NOT_A_FILE', '1'));
            }
            if (!$commandr_fs->_is_dir($parameters[1])) {
                return array('', '', '', do_lang('NOT_A_DIR', '2'));
            }

            $success = $commandr_fs->move_file($parameters[0], $parameters[1]);
            if ($success) {
                return array('', '', do_lang('SUCCESS'), '');
            } else {
                return array('', '', '', do_lang('INCOMPLETE_ERROR'));
            }
        }
    }
}
