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
IMPORTANT: This file is loaded outside Composr, so must work as standalone.
*/

/**
 * Check the given master password is valid.
 *
 * @param  SHORT_TEXT $password_given Given master password
 * @return boolean Whether it is valid
 */
function check_master_password($password_given)
{
    _master_password_check__init();

    global $SITE_INFO;

    $actual_password_hashed = $SITE_INFO['master_password'];
    if (strpos($actual_password_hashed, '$') !== false) {
        $ret = password_verify($password_given, $actual_password_hashed);
        _master_password_check__result($ret);
        return $ret;
    }
    $salt = '';
    if ((substr($actual_password_hashed, 0, 1) == '!') && (strlen($actual_password_hashed) == 33)) {
        $actual_password_hashed = substr($actual_password_hashed, 1);
        $salt = 'cms';

        // LEGACY
        if ($actual_password_hashed !== md5($password_given . $salt)) {
            $salt = 'ocp';
        }
    }
    $ret = (((strlen($password_given) != 32) && ($actual_password_hashed == $password_given)) || (hash_equals($actual_password_hashed, md5($password_given . $salt))));
    _master_password_check__result($ret);
    return $ret;
}

/**
 * Check the given master password is valid.
 *
 * @param  SHORT_TEXT $password_given_hashed Given master password
 * @return boolean Whether it is valid
 */
function check_master_password_from_hash($password_given_hashed)
{
    _master_password_check__init();

    global $SITE_INFO;

    $actual_password_hashed = $SITE_INFO['master_password'];

    if ($password_given_hashed === md5($actual_password_hashed)) {
        $ret = true; // LEGACY: Upgrade from v7 where hashed input password given even if plain-text password is in use
        _master_password_check__result($ret);
        return $ret;
    }

    $ret = hash_equals($password_given_hashed, $actual_password_hashed);
    _master_password_check__result($ret);
    return $ret;
}

/**
 * Prepare for checking the master password.
 */
function _master_password_check__init()
{
    usleep(500000); // Wait for half a second, to reduce brute force potential

    global $SITE_INFO;

    if (isset($SITE_INFO['admin_password'])) { // LEGACY
        $SITE_INFO['master_password'] = $SITE_INFO['admin_password'];
        unset($SITE_INFO['admin_password']);
    }

    if (!array_key_exists('master_password', $SITE_INFO)) {
        exit('No master password defined in _config.php currently so cannot authenticate');
    }
}

/**
 * Prepare for checking the master password.
 *
 * @param  boolean $result Whether login is successful
 */
function _master_password_check__result($result)
{
    $msg = 'Composr administrative script ' . basename($_SERVER['SCRIPT_NAME']);
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $msg .= ', by IP address ' . $_SERVER['REMOTE_ADDR'];
    }
    if (function_exists('syslog')) {
        if ($result) {
            @syslog(LOG_NOTICE, 'Successfully logged into ' . $msg);
        } else {
            @syslog(LOG_WARNING, 'Incorrect master password given while logging into ' . $msg);
        }
    }

    if (function_exists('error_log')) {
        global $FILE_BASE;
        @ini_set('error_log', $FILE_BASE . '/data_custom/errorlog.php');
        if (!$result) {
            @error_log('Incorrect master password given while logging into ' . $msg);
        }
    }
}
