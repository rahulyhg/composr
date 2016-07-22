<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: ldap\_.+*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    ldap
 */

/*
Note: when we say 'cn' we mean whatever member_property() is. It might not actually be the 'cn' (isn't for Active Directory).

Group membership mechanism is hard-coded for Linux and Active Directory LDAP:
 - Linux, using gidnumber on user record (secondary memberships for member) and memberuid on group record (primary memberships for group)
 - Active Directory, using memberof on user record

We assume groups are always referenced as 'cn'.

When looping over results, we always have to skip non-numeric keys, which are for metadata returned within result set (e.g. 'count'). Ugly, I know - you'd think LDAP would be neater ;).
*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__cns_ldap()
{
    if (!defined('LDAP_OPT_DIAGNOSTIC_MESSAGE')) {
        define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);
    }
}

/**
 * Escape, ready for an LDAP query.
 *
 * @param  string $str The value.
 * @param  boolean $for_dn Whether this is for use in a DN string.
 * @return string The escaped value.
 */
function cms_ldap_escape($str, $for_dn = false)
{
    // see:
    // RFC2254
    // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
    // http://www-03.ibm.com/systems/i/software/ldap/underdn.html

    $meta_chars = $for_dn ? array(',', '=', '+', '<', '>', ';', '\\', '"', '#') : array('*', '(', ')', '\\', chr(0));

    $quoted_meta_chars = array();
    foreach ($meta_chars as $key => $value) {
        $quoted_meta_chars[$key] = '\\' . str_pad(dechex(ord($value)), 2, '0');
    }

    $ret = str_replace($meta_chars, $quoted_meta_chars, $str);
    require_code('character_sets');
    return convert_to_internal_encoding($ret, get_charset(), 'utf8');
}

/**
 * Unescape data from LDAP. Technically this is not unescaping, it's just a character set conversion, but function is named to provide symmetry with cms_ldap_escape which does both escaping and character set conversion.
 *
 * @param  string $str The escaped value.
 * @return string The value.
 */
function ldap_unescape($str)
{
    require_code('character_sets');
    return convert_to_internal_encoding($str, 'utf8', get_charset());
}

/**
 * Set up the Conversr LDAP connection.
 */
function cns_ldap_connect()
{
    global $LDAP_CONNECTION;
    /** Our connection to the LDAP server.
     *
     * @global ?array $LDAP_CONNECTION
     */
    $LDAP_CONNECTION = ldap_connect(get_option('ldap_hostname'));

    if ((get_option('ldap_is_windows') == '1') && (function_exists('ldap_set_option'))) {
        ldap_set_option($LDAP_CONNECTION, LDAP_OPT_REFERRALS, 0);
    }
    ldap_set_option($LDAP_CONNECTION, LDAP_OPT_PROTOCOL_VERSION, intval(get_option('ldap_version')));

    cns_ldap_bind();
}

/**
 * Where in the LDAP hierarchy to search for groups.
 *
 * @return string The property.
 */
function member_search_qualifier()
{
    $v = get_option('ldap_member_search_qualifier');
    if ($v == '') {
        return $v;
    }
    return $v . ',';
}

/**
 * The property in LDAP used for logins.
 *
 * @return string The property.
 */
function member_property()
{
    return get_option('ldap_member_property');
}

/**
 * The LDAP class indicating an account.
 *
 * @return string The property.
 */
function get_member_class()
{
    return get_option('ldap_member_class');
}

/**
 * Where in the LDAP hierarchy to search for members.
 *
 * @return string The property.
 */
function group_search_qualifier()
{
    $v = get_option('ldap_group_search_qualifier');
    if ($v == '') {
        return $v;
    }
    return $v . ',';
}

/**
 * The group naming property LDAP will be using.
 *
 * @return string The property.
 */
function group_property()
{
    return 'cn';
}

/**
 * The LDAP class indicating a group.
 *
 * @return string The property.
 */
function get_group_class()
{
    return get_option('ldap_group_class');
}

/**
 * The LDAP group that maps to the default Composr group.
 *
 * @return string The group.
 */
function get_mapped_users_group()
{
    return 'users';
}

/**
 * The LDAP group that maps to the first administrative group in Composr (db_get_first_id()+1).
 *
 * @return string The group.
 */
function get_mapped_admin_group()
{
    if (get_option('ldap_is_windows') == '0') {
        return 'admin';
    }
    require_code('lang'); // If in AJAX mode
    return do_lang('ADMINISTRATORS');
}

/**
 * Find whether a member of a certain username WOULD be bound to LDAP authentication (an exceptional situation, only for sites that use it).
 *
 * @param  string $cn The username.
 * @return boolean The answer.
 */
function cns_is_ldap_member_potential($cn)
{
    global $LDAP_CONNECTION;
    if ($LDAP_CONNECTION === null) {
        return false;
    }

    $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))', array(member_property()));
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);
    $answer = (array_key_exists(0, $entries));
    ldap_free_result($results);
    return $answer;
}

/**
 * Performs the Conversr LDAP connection bind, used to do general querying (not a user login).
 */
function cns_ldap_bind()
{
    global $LDAP_CONNECTION;

    $cn = get_option('ldap_bind_rdn');
    if ($cn == '') { // Anonymous bind
        $test = @ldap_bind($LDAP_CONNECTION); // This sometimes causes errors, and isn't always needed. Hence error output is suppressed
        if (get_param_integer('keep_ldap_debug', 0) == 1) {
            if ($test === false) {
                require_code('site');
                $extended_error = '';
                ldap_get_option($LDAP_CONNECTION, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
                fatal_exit(make_string_tempcode('LDAP: ' . ldap_error($LDAP_CONNECTION) . '; ' . $extended_error . ' -- (initial connection bind)'));
            }
        }
    } else {
        $login = ldap_get_login_string($cn);

        /*
        Example for Active Directory, if domain is chris4.com

        $login = 'cn=Administrator,cn=Users,dc=chris4,dc=com'; // Log in using full name (cn) [in this case, same as username, but not always]
        $login = 'Administrator@chris4.com'; // Log in using username (sAMAccountName)
        */
        $test = @ldap_bind($LDAP_CONNECTION, $login, get_option('ldap_bind_password')); // This sometimes causes errors, and isn't always needed. Hence error output is suppressed
        if ($test === false) {
            require_code('site');
            $extended_error = '';
            ldap_get_option($LDAP_CONNECTION, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
            $message = 'LDAP: ' . ldap_error($LDAP_CONNECTION) . '; ' . $extended_error;
            if ($GLOBALS['FORUM_DRIVER']->is_super_admin(get_member()) || $GLOBALS['IS_ACTUALLY_ADMIN']) {
                $message .= ' -- for binding (initial connection bind) with ' . $login;
            }
            attach_message(make_string_tempcode($message), 'warn', false, true);
        }
    }
}

/**
 * Find whether a member exists on the LDAP server.
 *
 * @param  SHORT_TEXT $cn The username.
 * @return boolean The answer.
 */
function cns_is_on_ldap($cn)
{
    global $LDAP_CONNECTION;
    $path = member_search_qualifier() . get_option('ldap_base_dn');
    $query = '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))';
    $results = @ldap_search($LDAP_CONNECTION, $path, $query, array(member_property()), 1);
    if ($results === false) {
        require_code('site');
        $message = 'LDAP: ' . ldap_error($LDAP_CONNECTION);
        if ($GLOBALS['FORUM_DRIVER']->is_super_admin(get_member()) || $GLOBALS['IS_ACTUALLY_ADMIN']) {
            $message .= ' -- for ' . $query . ' under ' . $path;
        }
        attach_message(make_string_tempcode($message), 'warn', false, true);
        return false;
    }
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);
    $is_on = array_key_exists(0, $entries);

    ldap_free_result($results);
    return $is_on;
}

/**
 * Find the LDAP servers password for a certain member.
 *
 * @param  string $cn The username.
 * @return ?string The password (null: no such user).
 */
function cns_get_ldap_hash($cn)
{
    global $LDAP_CONNECTION;

    $results = @ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))', array('userpassword'));
    if ($results === false) {
        require_code('site');
        attach_message(make_string_tempcode('LDAP: ' . ldap_error($LDAP_CONNECTION)), 'warn', false, true);
        return null;
    }
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);
    if (!array_key_exists(0, $entries)) {
        ldap_free_result($results);
        return null;
    }
    if (!array_key_exists('userpassword', $entries[0])) {
        require_code('site');
        attach_message(do_lang_tempcode('LDAP_CANNOT_CHECK_PASSWORDS'), 'warn', false, true);
        return uniqid('', true);
    }
    $pass = $entries[0]['userpassword'][0];
    ldap_free_result($results);

    return $pass;
}

/**
 * Convert a plain-text password into a hashed password.
 *
 * @param  string $cn The username (we use this to extract the hash algorithm being used by the member).
 * @param  string $password The password.
 * @return string The hashed password.
 */
function cns_ldap_hash($cn, $password)
{
    global $LDAP_CONNECTION;

    $stored = cns_get_ldap_hash($cn);
    if ($stored === null) {
        return '!!!'; // User is valid locally, but not on LDAP
    }
    $type_pos = strpos($stored, '}');
    $type = substr($stored, 1, $type_pos - 1);
    if ((strtolower($type) == 'crypt') && (strlen($stored) == 41)) {
        $type = 'md5crypt';
    }
    if ((strtolower($type) == 'crypt') && (strlen($stored) == 45)) {
        $type = 'blowfish';
    }

    switch (strtolower($type)) {
        case '':
            return $password;
        case 'crypt':
            $salt = substr($stored, $type_pos + 4, 2);
            return '{' . $type . '}' . crypt($password, $salt);
        case 'md5':
            return '{' . $type . '}' . base64_encode(pack('H*', md5($password)));
        case 'blowfish':
            $salt = substr($stored, $type_pos + 4, 13);
            return '{' . $type . '}' . crypt($password, '$2$' . $salt);
        case 'md5crypt':
            $salt = substr($stored, $type_pos + 4, 9);
            return '{' . $type . '}' . crypt($password, '$1$' . $salt);
        case 'sha':
            return '{' . $type . '}' . base64_encode(pack('H*', sha1($password)));
    }
    return '!!'; // Unknown password type
}

/**
 * Get an LDAP login string to do a bind against.
 *
 * @param  string $cn The username.
 * @return string The login string.
 */
function ldap_get_login_string($cn)
{
    $pre = get_option('ldap_login_qualifier');
    if ($pre != '') {
        $login = $pre . $cn;
    } else {
        if (member_property() == 'sAMAccountName') {
            $login = $cn . '@' . preg_replace('#^dc=#', '', str_replace(',dc=', '.', strtolower(get_option('ldap_base_dn'))));
        } else {
            if (strpos($cn, '=') === false) {
                $login = member_property() . '=' . $cn . ',' . member_search_qualifier() . get_option('ldap_base_dn');
            } else {
                $login = $cn;
            }
        }
    }
    return $login;
}

/**
 * Authorise an LDAP login.
 *
 * @param  string $cn The username.
 * @param  ?string $password The password (null: no such user).
 * @return array Part of the member row to put back in and authorise normally (hackerish, but it works kind of like a filter / stage in a chain).
 */
function cns_ldap_authorise_login($cn, $password)
{
    global $LDAP_CONNECTION;

    if (get_option('ldap_none_bind_logins') == '1') {
        $try = cns_ldap_hash($cn, $password);
        $there = cns_get_ldap_hash($cn);
        $good = ($try == $there);

        if ($good) {
            return array('m_pass_hash_salted' => $password, 'm_password_compat_scheme' => 'md5'); // A bit of a hack: actually we are doing a plain text check, and the 'hashed' passwords were both never hashed: still works
        }

        return array('m_pass_hash_salted' => '!!!', 'm_password_compat_scheme' => 'md5'); // Access will be denied
    }

    $login = ldap_get_login_string($cn);

    $test = (/*workaround PHP bug- blank passwords do anonymous bind*/
        $password == '') ? false : @ldap_bind($LDAP_CONNECTION, $login, $password);
    if ($test !== false) { // Note, for Windows Active Directory the CN is the full user name, not the login name. Therefore users log in with this.
        cns_ldap_bind(); // Rebind under normal name, so we're not stuck on this user's bind

        // We only get here if we are authorised. As to not complicate Composr's authentication chain, we trick it by setting our db password to that of our given password, ONLY once LDAP login is confirmed.
        return array('m_pass_hash_salted' => $password, 'm_password_compat_scheme' => 'md5'); // A bit of a hack: actually we are doing a plain text check, and the 'hashed' passwords were both never hashed: still works
    }
    if (get_param_integer('keep_ldap_debug', 0) == 1) {
        if ($test === false) {
            require_code('site');
            $message = 'LDAP: ' . ldap_error($LDAP_CONNECTION);
            if ($GLOBALS['FORUM_DRIVER']->is_super_admin(get_member()) || $GLOBALS['IS_ACTUALLY_ADMIN']) {
                $message .= ' -- for binding (active login) with ' . $login;
            }
            fatal_exit(make_string_tempcode($message));
        }
    }

    cns_ldap_bind(); // Rebind under normal name, so we're not stuck on this failed bind
    return array('m_pass_hash_salted' => '!!!', 'm_password_compat_scheme' => 'md5'); // Access will be denied
}

/**
 * Find the Composr member-ID for an LDAP username.
 *
 * @param  string $cn The username.
 * @return ?integer The Composr member-ID (null: none).
 */
function cns_member_ldapcn_to_cnsid($cn)
{
    $ret = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', array('m_username' => $cn, 'm_password_compat_scheme' => 'ldap'));
    if ($ret === null) {
        $ret = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', array('m_username' => $cn, 'm_password_compat_scheme' => 'httpauth'));
    }
    return $ret;
}

/**
 * Find the LDAP username for a Composr member-ID.
 *
 * @param  integer $id The Composr member-ID.
 * @return ?SHORT_TEXT The username (null: none).
 */
function cns_member_cnsid_to_ldapcn($id)
{
    $ret = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'm_username', array('id' => $id, 'm_password_compat_scheme' => 'ldap'));
    if ($ret === null) {
        $ret = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'm_username', array('id' => $id, 'm_password_compat_scheme' => 'httpauth'));
    }
    return $ret;
}

/**
 * Get a list of usergroups on the LDAP server.
 *
 * @return array The list of user-groups (string).
 */
function cns_get_all_ldap_groups()
{
    global $LDAP_CONNECTION;

    $groups = array();

    $results = @ldap_search($LDAP_CONNECTION, group_search_qualifier() . get_option('ldap_base_dn'), 'objectclass=' . get_group_class(), array(group_property())); // We do ldap_search as Active Directory can be fussy when looking at large sets, like all members
    if ($results === false) {
        require_code('site');
        attach_message((($LDAP_CONNECTION === null) ? do_lang_tempcode('LDAP_DISABLED') : (protect_from_escaping('LDAP: ' . ldap_error($LDAP_CONNECTION)))), 'warn', false, true);
        return array();
    }
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);

    foreach ($entries as $key => $entry) {
        if ($key === 'dn') { // May come out as 'dn'
            $group_cn = cns_long_cn_to_short_cn(ldap_unescape($entry['dn']), 'dn');
            $groups[] = $group_cn;
        }
        if (!is_numeric($key)) {
            continue;
        }
        if (!array_key_exists(group_property(), $entry)) {
            continue;
        }

        $group_cn = cns_long_cn_to_short_cn(ldap_unescape($entry[group_property()][0]), group_property());
        $groups[] = $group_cn;
    }
    ldap_free_result($results);

    return array_unique($groups);
}

/**
 * Find the Composr-ID for a named LDAP usergroup.
 *
 * @param  string $cn The usergroup.
 * @return ?GROUP The Composr-ID (null: none).
 */
function cns_group_ldapcn_to_cnsid($cn)
{
    if ($cn == get_mapped_admin_group()) {
        return db_get_first_id() + 1;
    }
    if ($cn == get_mapped_users_group()) {
        return get_first_default_group();
    }

    cns_ensure_groups_cached('*');
    global $USER_GROUPS_CACHED;
    foreach ($USER_GROUPS_CACHED as $id => $group) {
        if ($group['g__name'] == $cn) {
            return $id;
        }
    }

    return null;
}

/**
 * Find the named LDAP usergroup for an Conversr ID. Note that the returned MAY NOT ACTUALLY EXIST!
 *
 * @param  GROUP $id The Conversr ID.
 * @return ?SHORT_TEXT The named LDAP usergroup (null: none).
 */
function cns_group_cnsid_to_ldapcn($id)
{
    if ($id == db_get_first_id() + 1) {
        return get_mapped_admin_group();
    }
    if ($id == get_first_default_group()) {
        return get_mapped_users_group();
    }

    cns_ensure_groups_cached(array($id));
    global $USER_GROUPS_CACHED;
    return $USER_GROUPS_CACHED[$id]['g__name'];
}

/**
 * Get the e-mail of a member in LDAP.
 *
 * @param  ID_TEXT $cn The CN of the member.
 * @return SHORT_TEXT Guessed e-mail address (blank: couldn't find).
 */
function cns_ldap_guess_email($cn)
{
    global $LDAP_CONNECTION;

    $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))');
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);
    ldap_free_result($results);
    if (!array_key_exists(0, $entries)) {
        return '';
    }
    foreach (array('mail', 'email') as $mail_property) {
        if (array_key_exists($mail_property, $entries[0])) {
            return is_array($entries[0][$mail_property]) ? $entries[0][$mail_property][0] : $entries[0][$mail_property];
        }
    }
    return '';
}

/**
 * (LDAP helper for cns_get_group_members_raw) Get a list of members in a group (or more full details if $non_validated is true).
 *
 * @param  array $members The list is written into this.
 * @param  GROUP $group_id The ID of the usergroup.
 * @param  boolean $include_primaries Whether to include those in the usergroup as a primary member.
 * @param  boolean $non_validated Whether to include those applied to join the usergroup, but not validated in.
 * @param  boolean $include_secondaries Whether to include those in the usergroup as a secondary member.
 */
function cns_get_group_members_raw_ldap(&$members, $group_id, $include_primaries, $non_validated, $include_secondaries)
{
    global $LDAP_CONNECTION;
    $gid = null;
    $cn = cns_group_cnsid_to_ldapcn($group_id);

    if (get_option('ldap_is_windows') == '0') {
        // Members under group (secondary)
        if (($include_secondaries) && ($cn !== null)) {
            $results = ldap_search($LDAP_CONNECTION, group_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_group_class() . ')(' . group_property() . '=' . cms_ldap_escape($cn) . '))', array('memberuid', 'gidnumber'));
            $entries = ldap_get_entries($LDAP_CONNECTION, $results);
            if ((array_key_exists(0, $entries)) && (array_key_exists('memberuid', $entries[0]))) { // Might not exist in LDAP
                foreach ($entries[0]['memberuid'] as $key => $member) {
                    if (!is_numeric($key)) {
                        continue;
                    }

                    $member_id = cns_member_ldapcn_to_cnsid(ldap_unescape($member));
                    if ($member_id !== null) {
                        if ($non_validated) {
                            $members[$member_id] = array('gm_member_id' => $member_id, 'gm_validated' => 1, 'm_username' => ldap_unescape($member), 'implicit' => false);
                        } else {
                            $members[$member_id] = $member_id;
                        }
                    }
                }
                $gid = $entries[0]['gidnumber']; // Picked up for performance reasons
                ldap_free_result($results);
            }
        }

        if ($gid === null) {
            $gid = cns_group_ldapcn_to_ldapgid($cn);
        }

        // Groups under member (primary)
        if (($include_primaries) && ($gid !== null)) {
            $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(gidnumber=' . cms_ldap_escape(strval($gid)) . '))', array(member_property()));
            $entries = ldap_get_entries($LDAP_CONNECTION, $results);

            foreach ($entries as $key => $member) { // There will only be one, but I wrote a loop so lets use a loop
                if (!is_numeric($key)) {
                    continue;
                }
                if (!array_key_exists(member_property(), $member)) {
                    continue;
                }
                if (!array_key_exists(0, $member[member_property()])) {
                    continue;
                }

                $member_id = cns_member_ldapcn_to_cnsid(ldap_unescape($member[member_property()][0]));
                if ($member_id !== null) {
                    if ($non_validated) {
                        $members[$member_id] = array('m_username' => ldap_unescape($member[member_property()][0]), 'gm_member_id' => $member_id, 'gm_validated' => 1, 'implicit' => false);
                    } else {
                        $members[$member_id] = $member_id;
                    }
                }
            }
            ldap_free_result($results);
        }
    } else {
        if ($cn !== null) {
            // Groups under member (Active Directory makes no distinction)
            $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . group_property() . '=' . cms_ldap_escape($cn) . '))', array('memberof')); // We do ldap_search as Active Directory can be fussy when looking at large sets, like all members
            $entries = ldap_get_entries($LDAP_CONNECTION, $results);
            if ((array_key_exists(0, $entries)) && (array_key_exists('memberof', $entries[0]))) { // Might not exist in LDAP
                foreach ($entries[0]['memberof'] as $key => $member) {
                    if (!is_numeric($key)) {
                        continue;
                    }

                    $member_id = cns_member_ldapcn_to_cnsid(ldap_unescape(cns_long_cn_to_short_cn($member, member_property())));
                    if ($member_id !== null) {
                        if ((($include_primaries) && ($include_secondaries)) ||
                            (($include_primaries) && (!$include_secondaries) && (cns_ldap_get_member_primary_group($member_id) == $gid)) ||
                            ((!$include_primaries) && ($include_secondaries) && (cns_ldap_get_member_primary_group($member_id) != $gid))
                        ) {
                            if ($non_validated) {
                                $members[$member_id] = array('gm_member_id' => $member_id, 'gm_validated' => 1, 'm_username' => ldap_unescape(cns_long_cn_to_short_cn($member, member_property())), 'implicit' => false);
                            } else {
                                $members[$member_id] = $member_id;
                            }
                        }
                    }
                }
                ldap_free_result($results);
            }
        }
    }
}

/**
 * (LDAP helper for cns_get_members_groups) Get a list of the usergroups a member is in (keys say the usergroups, values are irrelevant).
 *
 * @param  ?MEMBER $member_id The member to find the usergroups of (null: current member).
 * @return array Flipped list (e.g. array(1=>true,2=>true,3=>true) for someone in (1,2,3)).
 */
function cns_get_members_groups_ldap($member_id)
{
    $groups = array();

    global $LDAP_CONNECTION;

    $cn = cns_member_cnsid_to_ldapcn($member_id);

    if (get_option('ldap_is_windows') == '0') {
        // Members under group (secondary)
        $results = ldap_search($LDAP_CONNECTION, group_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(memberuid=' . cms_ldap_escape($cn) . '))', array(group_property()), 1);
        $entries = ldap_get_entries($LDAP_CONNECTION, $results);
        foreach ($entries as $key => $entry) {
            if ($key === 'dn') { // May come out as 'dn'
                $group_cn = cns_long_cn_to_short_cn(ldap_unescape($entry['dn']), 'dn');
                $group_id = cns_group_ldapcn_to_cnsid($group_cn);
                if ($group_id !== null) {
                    $groups[$group_id] = true;
                }
            }

            if (!is_numeric($key)) {
                continue;
            }
            if (!array_key_exists(group_property(), $entry)) {
                continue;
            }
            if (!array_key_exists(0, $entry[group_property()])) {
                continue;
            }

            $group_cn = cns_long_cn_to_short_cn(ldap_unescape($entry[group_property()][0]), group_property());
            $group_id = cns_group_ldapcn_to_cnsid($group_cn);
            if ($group_id !== null) {
                $groups[$group_id] = true;
            }
        }
        ldap_free_result($results);

        // Groups under member (primary)
        $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))', array('gidnumber'));
        $entries = ldap_get_entries($LDAP_CONNECTION, $results);
        $group_id_use = null;
        foreach ($entries as $key => $group) { // There will only be one, but I wrote a loop so lets use a loop
            if (!is_numeric($key)) {
                continue;
            }

            $group_id = cns_group_ldapgid_to_cnsid($group['gidnumber'][0]);
            if ($group_id !== null) {
                $group_id_use = $group_id;
            }
        }
        ldap_free_result($results);
        if ($group_id_use === null) {
            $group_id_use = get_first_default_group();
        }
        $groups[$group_id_use] = true;
    } else {
        // Groups under member (Active Directory makes no distinction)
        $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape($cn) . '))', array('memberof'));
        $entries = ldap_get_entries($LDAP_CONNECTION, $results);
        $group_id_use = null;
        if ((array_key_exists(0, $entries)) && (array_key_exists('memberof', $entries[0]))) { // Might not exist in LDAP
            foreach ($entries[0]['memberof'] as $key => $group) { // There will only be one, but I wrote a loop so lets use a loop
                if (!is_numeric($key)) {
                    continue;
                }

                $group_id = cns_group_ldapcn_to_cnsid(cns_long_cn_to_short_cn($group, group_property()));
                if ($group_id !== null) {
                    $groups[$group_id] = true;
                }
            }
        }
        ldap_free_result($results);
        if (count($groups) == 0) {
            $groups = array_flip(cns_get_all_default_groups(true));
        }
    }
    return $groups;
}

/**
 * Get the primary usergroup of a member in LDAP.
 *
 * @param  MEMBER $member_id The member.
 * @return GROUP The.
 */
function cns_ldap_get_member_primary_group($member_id)
{
    global $PRIMARY_GROUP_MEMBERS_CACHE;

    global $LDAP_CONNECTION;

    if (get_option('ldap_is_windows') == '0') {
        $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape(cns_member_cnsid_to_ldapcn($member_id)) . '))', array('gidnumber'));
        $entries = ldap_get_entries($LDAP_CONNECTION, $results);
        $gid = array_key_exists(0, $entries) ? $entries[0]['gidnumber'][0] : null;
        ldap_free_result($results);

        if ($gid !== null) {
            $gid = cns_group_ldapgid_to_cnsid($gid);
        }
        if ($gid === null) {
            $gid = get_first_default_group();
        }
    } else {
        // While Windows has primaryGroupID, it has an ID that refers outside of LDAP, so is of no use to us. We use the last a member is in as the primary
        $results = ldap_search($LDAP_CONNECTION, member_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_member_class() . ')(' . member_property() . '=' . cms_ldap_escape(cns_member_cnsid_to_ldapcn($member_id)) . '))', array('memberof'));
        $entries = ldap_get_entries($LDAP_CONNECTION, $results);
        if ((array_key_exists(0, $entries)) && (array_key_exists('memberof', $entries[0]))) { // Might not exist in LDAP
            $group = $entries[0]['memberof'][count($entries[0]['memberof']) - 2]; // Last is -2 due to count index
            $cn = cns_long_cn_to_short_cn($group, group_property());
            $gid = cns_group_ldapcn_to_cnsid($cn);
            if ($gid === null) {
                $gid = get_first_default_group();
            }
        } else {
            $gid = get_first_default_group();
        }
        ldap_free_result($results);
    }

    $PRIMARY_GROUP_MEMBERS_CACHE[$member_id] = $gid;

    return $gid;
}

/**
 * Find the Composr-ID for an LDAP usergroup-ID. POSIX Only.
 *
 * @param  integer $gid The LDAP ID.
 * @return ?GROUP The Composr-ID (null: could not find).
 */
function cns_group_ldapgid_to_cnsid($gid)
{
    global $LDAP_CONNECTION;
    $results = ldap_search($LDAP_CONNECTION, group_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_group_class() . ')(gidnumber=' . cms_ldap_escape(strval($gid)) . '))', array(group_property()), 1);
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);
    if (!array_key_exists(0, $entries)) {
        return null;
    }
    if (array_key_exists(0, $entries[0][group_property()])) {
        $long_cn = $entries[0][group_property()][0];
        $cn = cns_long_cn_to_short_cn($long_cn, group_property());
    } else {
        $long_cn = $entries[0]['dn']; // Might come out as DN
        $cn = cns_long_cn_to_short_cn($long_cn, 'dn');
    }
    return cns_group_ldapcn_to_cnsid($cn);
}

/**
 * Find the LDAP ID for a named LDAP usergroup. POSIX Only.
 *
 * @param  string $cn The named LDAP usergroup.
 * @return ?integer The LDAP usergroup ID (null: none).
 */
function cns_group_ldapcn_to_ldapgid($cn)
{
    global $LDAP_CONNECTION;
    $results = ldap_search($LDAP_CONNECTION, group_search_qualifier() . get_option('ldap_base_dn'), '(&(objectclass=' . get_group_class() . ')(' . group_property() . '=' . cms_ldap_escape($cn) . '))', array('gidnumber'));
    $entries = ldap_get_entries($LDAP_CONNECTION, $results);

    if (!array_key_exists(0, $entries)) {
        return null;
    }

    return $entries[0]['gidnumber'][0];
}

/**
 * Converts an active directory style long-CN to a short one.
 *
 * @param  string $long The long one.
 * @param  string $type The type (e.g. CN, DN).
 * @return string The short one.
 */
function cns_long_cn_to_short_cn($long, $type)
{
    $matches = array();
    if (preg_match('#^(dn|cn|' . preg_quote($type, '#') . ')=([^,]+)(,.*)?$#i', $long, $matches) != 0) {
        return $matches[2];
    }
    return $long;
}
