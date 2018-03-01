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
 * @package    commandr
 */

/**
 * Hook class.
 */
class Hook_commandr_command_antispam_check
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
            return array('', do_command_help('antispam_check', array('h'), array(true, true)), '', '');
        } else {
            if (!array_key_exists(0, $parameters)) {
                return array('', '', '', do_lang('MISSING_PARAM', '1', 'antispam_check'));
            }

            require_code('antispam');

            $user_ip = $parameters[0];

            $ret = '';
            $rbl_list = explode(',', get_option('spam_block_lists'));
            $rbl_list[] = 'Stop Forum Spam';
            foreach ($rbl_list as $rbl) {
                if ($rbl == 'Stop Forum Spam') {
                    list($_is_potential_blocked, $_confidence_level) = _check_stopforumspam($user_ip);
                } else {
                    list($_is_potential_blocked, $_confidence_level) = check_rbl($rbl, $user_ip);
                }
                $blocked_by = preg_replace('#(^|\.)\*(\.|$)#', '', $rbl);
                $ret .= $blocked_by . ': ';
                switch ($_is_potential_blocked) {
                    case ANTISPAM_RESPONSE_SKIP:
                        $ret .= do_lang('ANTISPAM_RESPONSE_SKIP');
                        break;
                    case ANTISPAM_RESPONSE_ERROR:
                        $ret .= do_lang('ANTISPAM_RESPONSE_ERROR');
                        break;
                    case ANTISPAM_RESPONSE_UNLISTED:
                        $ret .= do_lang('ANTISPAM_RESPONSE_UNLISTED');
                        break;
                    case ANTISPAM_RESPONSE_STALE:
                        $ret .= do_lang('ANTISPAM_RESPONSE_STALE');
                        break;
                    case ANTISPAM_RESPONSE_ACTIVE:
                        $ret .= do_lang('ANTISPAM_RESPONSE_ACTIVE');
                        break;
                    case ANTISPAM_RESPONSE_ACTIVE_UNKNOWN_STALE:
                        $ret .= do_lang('ANTISPAM_RESPONSE_ACTIVE_UNKNOWN_STALE');
                        break;
                    default:
                        $ret .= do_lang('INTERNAL_ERROR') . ' (unexpected code: ' . strval($_is_potential_blocked) . ')';
                        break;
                }
                if ($_confidence_level !== null) {
                    $ret .= ', ';
                    $ret .= do_lang('ANTISPAM_CONFIDENCE', float_to_raw_string(min(100.0, $_confidence_level * 100.0), 0));
                } elseif (($_is_potential_blocked == ANTISPAM_RESPONSE_STALE) || ($_is_potential_blocked == ANTISPAM_RESPONSE_ACTIVE) || ($_is_potential_blocked == ANTISPAM_RESPONSE_ACTIVE_UNKNOWN_STALE)) {
                    $ret .= ', ';
                    $ret .= do_lang('ANTISPAM_CONFIDENCE_NA');
                }
                $ret .= "\n";
            }

            return array('', '', $ret, '');
        }
    }
}
