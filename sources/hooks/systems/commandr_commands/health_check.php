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
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_commandr_command_health_check
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
        if (!addon_installed('health_check')) {
            return array('', '', '', do_lang('INTERNAL_ERROR'));
        }

        require_lang('health_check');

        if ((array_key_exists('h', $options)) || (array_key_exists('help', $options))) {
            return array('', do_command_help('health_check', array('h'), array()), '', '');
        } else {
            if (!array_key_exists(0, $parameters)) {
                return array('', '', '', do_lang('MISSING_PARAM', '1', 'health_check'));
            }

            require_code('health_check');

            $has_fails = false;
            $_categories = run_health_check($has_fails, $parameters, true, true, true);

            $result = '';
            foreach ($_categories as $_category_label => $_sections) {
                foreach ($_sections['SECTIONS'] as $_section_label => $_section) {
                    foreach ($_section['RESULTS'] as $_result) {
                        $result .= $_result['RESULT'] . ': ' . strip_html($_result['MESSAGE']->evaluate()) . ' (' . $_category_label . ' \\ ' . $_section_label . ')' . "\n";
                    }
                }
            }

            return array('', $result, '', '');
        }
    }
}