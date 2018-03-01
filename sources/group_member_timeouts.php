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
 * @package    core
 */

/**
 * Put a member into a usergroup temporarily / extend such a temporary usergroup membership.
 *
 * @param  MEMBER $member_id The member going in the usergroup
 * @param  GROUP $group_id The usergroup
 * @param  integer $num_minutes The number of minutes (may be negative to take time away)
 * @param  boolean $prefer_for_primary_group Whether to put the member into as a primary group if this is a new temporary membership (it is recommended to NOT use this, since we don't track the source group and hence on expiry the member is put back to the first default group - but also generally you probably don't want to box yourself in with moving people's primary group, it ties your future flexibility down a lot)
 */
function bump_member_group_timeout($member_id, $group_id, $num_minutes, $prefer_for_primary_group = false)
{
    $db = get_db_for('f_group_member_timeouts');

    // Extend or add, depending on whether they're in it yet
    $existing_timeout = $db->query_select_value_if_there('f_group_member_timeouts', 'timeout', array('member_id' => $member_id, 'group_id' => $group_id));
    if ($existing_timeout === null) {
        $timestamp = time() + 60 * $num_minutes;
    } else {
        $timestamp = $existing_timeout + 60 * $num_minutes;
    }

    set_member_group_timeout($member_id, $group_id, $timestamp, $prefer_for_primary_group);
}

/**
 * Put a member into a usergroup temporarily. Note that if people are subsequently removed from the usergroup they won't be put back in; this allows the admin to essentially cancel the subscription - however, if it is then extended, they do keep the time they had before too.
 *
 * @param  MEMBER $member_id The member going in the usergroup
 * @param  GROUP $group_id The usergroup
 * @param  TIME $timestamp The expiry timestamp
 * @param  boolean $prefer_for_primary_group Whether to put the member into as a primary group if this is a new temporary membership (it is recommended to NOT use this, since we don't track the source group and hence on expiry the member is put back to the first default group - but also generally you probably don't want to box yourself in with moving people's primary group, it ties your future flexibility down a lot)
 */
function set_member_group_timeout($member_id, $group_id, $timestamp, $prefer_for_primary_group = false)
{
    // We don't want guests here!
    if (is_guest($member_id)) {
        fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
    }

    $db = get_db_for('f_group_member_timeouts');

    require_code('cns_groups_action');
    require_code('cns_groups_action2');
    require_code('cns_members');

    // Add to group if not already there
    $test = in_array($group_id, $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
    if (!$test) {
        // Add them to the group
        if ((method_exists($GLOBALS['FORUM_DRIVER'], 'add_member_to_group')) && (get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
            $GLOBALS['FORUM_DRIVER']->add_member_to_group($member_id, $group_id);
        } else {
            if ($prefer_for_primary_group) {
                $db->query_update('f_members', array('m_primary_group' => $group_id), array('id' => $member_id), '', 1);
                $GLOBALS['FORUM_DRIVER']->MEMBER_ROWS_CACHED = array();

                $GLOBALS['FORUM_DB']->query_insert('f_group_join_log', array(
                    'member_id' => $member_id,
                    'usergroup_id' => $group_id,
                    'join_time' => time(),
                ));
            } else {
                cns_add_member_to_group($member_id, $group_id);
            }
        }
    }

    // Set
    $db->query_delete('f_group_member_timeouts', array(
        'member_id' => $member_id,
        'group_id' => $group_id,
    ), '', 1);
    $db->query_insert('f_group_member_timeouts', array(
        'member_id' => $member_id,
        'group_id' => $group_id,
        'timeout' => $timestamp,
    ));

    global $USERS_GROUPS_CACHE, $GROUP_MEMBERS_CACHE;
    $USERS_GROUPS_CACHE = array();
    $GROUP_MEMBERS_CACHE = array();
}

/**
 * Handle auto-removal of timed-out members.
 */
function cleanup_member_timeouts()
{
    if (php_function_allowed('set_time_limit')) {
        @set_time_limit(0);
    }

    $db = get_db_for('f_group_member_timeouts');

    require_code('cns_groups_action');
    require_code('cns_groups_action2');
    require_code('cns_members');

    $db = get_db_for('f_group_member_timeouts');
    $start = 0;
    $time_now = time();
    do {
        $timeouts = $db->query('SELECT member_id,group_id FROM ' . $db->get_table_prefix() . 'f_group_member_timeouts WHERE timeout<' . strval($time_now), 100, $start);
        foreach ($timeouts as $timeout) {
            $member_id = $timeout['member_id'];
            $group_id = $timeout['group_id'];

            $test = in_array($group_id, $GLOBALS['FORUM_DRIVER']->get_members_groups($member_id));
            if ($test) { // If they're still in it
                if ((method_exists($GLOBALS['FORUM_DRIVER'], 'remove_member_from_group')) && (get_value('unofficial_ecommerce') === '1') && (get_forum_type() != 'cns')) {
                    $GLOBALS['FORUM_DRIVER']->remove_member_from_group($member_id, $group_id);
                } else {
                    if ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id, 'm_primary_group') == $group_id) {
                        $db->query_update('f_members', array('m_primary_group' => get_first_default_group()), array('id' => $member_id), '', 1);
                        $GLOBALS['FORUM_DRIVER']->MEMBER_ROWS_CACHED = array();
                    }
                    $db->query_delete('f_group_members', array('gm_group_id' => $group_id, 'gm_member_id' => $member_id), '', 1);
                }

                global $USERS_GROUPS_CACHE, $GROUP_MEMBERS_CACHE;
                $USERS_GROUPS_CACHE = array();
                $GROUP_MEMBERS_CACHE = array();
            }
        }
        $start += 100;
    } while (count($timeouts) == 100);

    if (!$db->table_is_locked('f_group_member_timeouts')) {
        $timeouts = $db->query('DELETE FROM ' . $db->get_table_prefix() . 'f_group_member_timeouts WHERE timeout<' . strval($time_now));
    }
}
