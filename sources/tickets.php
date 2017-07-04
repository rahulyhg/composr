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
 * @package    tickets
 */

/**
 * Find the active support user. Supports the "support_operator" option, for anonymising support.
 *
 * @return MEMBER Member ID
 */
function get_active_support_user()
{
    $member_id = get_member();

    if (has_privilege($member_id, 'support_operator')) {
        $support_operator = get_option('support_operator');
        if (!empty($support_operator)) {
            $_member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($support_operator);
            if ($_member_id !== null) {
                $member_id = $_member_id;
            }
        }
    }

    return $member_id;
}

/**
 * Checks the ticket ID is valid, and there is access for the current member to view it. Bombs out if there's a problem.
 *
 * @param  string $ticket_id The ticket ID to check
 * @return MEMBER The ticket owner
 */
function check_ticket_access($ticket_id)
{
    // Never for a guest
    if (is_guest()) {
        access_denied('NOT_AS_GUEST');
    }

    // Check we are allowed using normal checks
    $_temp = explode('_', $ticket_id, 2);
    $ticket_owner = intval($_temp[0]);
    if (has_privilege(get_member(), 'view_others_tickets')) {
        return $ticket_owner;
    }
    if ($ticket_owner == get_member()) {
        return $ticket_owner;
    }

    // Check we're allowed using extra access
    $test = $GLOBALS['SITE_DB']->query_select_value_if_there('ticket_extra_access', 'ticket_id', array('ticket_id' => $ticket_id, 'member_id' => get_member()));
    if ($test !== null) {
        return $ticket_owner;
    }

    // No access :(
    if (is_guest(intval($_temp[0]))) {
        access_denied(do_lang('TICKET_OTHERS_HACK'));
    }
    log_hack_attack_and_exit('TICKET_OTHERS_HACK');

    return $ticket_owner; // Will never get here
}

/**
 * Get a support ticket URL.
 *
 * @param  ID_TEXT $ticket_id The support ticket ID
 * @return URLPATH The ticket URL
 */
function ticket_url($ticket_id)
{
    $_ticket_url = build_url(array('page' => 'tickets', 'type' => 'ticket', 'id' => $ticket_id), get_module_zone('tickets'), array(), false, true, true);
    $ticket_url = $_ticket_url->evaluate();
    return $ticket_url;
}

/**
 * Get the forum ID for a given ticket type and member, taking the ticket_type_forums option into account.
 *
 * @param  ?integer $ticket_type_id The ticket type (null: all ticket types)
 * @param  boolean $create Create the forum if it's missing
 * @param  boolean $silent_error_handling Whether to skip showing errors, returning null instead
 * @return ?AUTO_LINK Forum ID (null: not found)
 */
function get_ticket_forum_id($ticket_type_id = null, $create = false, $silent_error_handling = false)
{
    static $fid_cache = array();
    if (isset($fid_cache[$ticket_type_id])) {
        return $fid_cache[$ticket_type_id];
    }

    $root_forum = get_option('ticket_forum_name');

    // Check the root ticket forum is valid
    $fid = $GLOBALS['FORUM_DRIVER']->forum_id_from_name($root_forum);
    if ($fid === null) {
        if ($silent_error_handling) {
            return null;
        }
        warn_exit(do_lang_tempcode('NO_FORUM'), false, true);
    }

    // Only the root ticket forum is supported for non-Conversr installations
    if (get_forum_type() != 'cns') {
        return $fid;
    }

    if (($ticket_type_id !== null) && (get_option('ticket_type_forums') === '1')) {
        $_ticket_type_name = $GLOBALS['SITE_DB']->query_select_value_if_there('ticket_types', 'ticket_type_name', array('id' => $ticket_type_id));
        if ($_ticket_type_name !== null) {
            $ticket_type_name = get_translated_text($_ticket_type_name);
            $rows = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id'), array('f_parent_forum' => $fid, 'f_name' => $ticket_type_name), '', 1);
            if (count($rows) == 0) {
                require_code('cns_forums_action');
                require_code('cns_forums_action2');

                $category_id = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'f_forum_grouping_id', array('id' => $fid));

                $fid = cns_make_forum($ticket_type_name, do_lang('SUPPORT_TICKETS_FOR_TYPE', $ticket_type_name), $category_id, null, $fid);
            } else {
                $fid = $rows[0]['id'];
            }
        }
    }

    $fid_cache[$ticket_type_id] = $fid;

    return $fid;
}

/**
 * Returns whether the given forum ID is for a ticket forum (subforum of the root ticket forum).
 *
 * @param  ?AUTO_LINK $forum_id The forum ID (null: private topics forum)
 * @return boolean Whether the given forum is a ticket forum
 */
function is_ticket_forum($forum_id)
{
    static $cache = array();
    if (isset($cache[$forum_id])) {
        return $cache[$forum_id];
    }

    if ($forum_id === null) {
        $cache[$forum_id] = false;
        return false;
    }

    $root_ticket_forum_id = get_ticket_forum_id(null, false, true);
    if ($root_ticket_forum_id === null) {
        $cache[$forum_id] = false;
        return false;
    }
    if (($root_ticket_forum_id == db_get_first_id()) && ($forum_id != db_get_first_id())) {
        $cache[$forum_id] = false;
        return false; // If ticket forum (oddly) set as root, don't cascade it through all!
    }
    if ($forum_id === $root_ticket_forum_id) {
        $cache[$forum_id] = true;
        return true;
    }

    $query = 'SELECT COUNT(*) AS cnt FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_forums WHERE id=' . strval($forum_id) . ' AND f_parent_forum IN (SELECT id FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_forums WHERE id=' . strval($root_ticket_forum_id) . ' OR f_parent_forum IN (SELECT id FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_forums WHERE id=' . strval($root_ticket_forum_id) . '))';

    $rows = $GLOBALS['FORUM_DB']->query($query);
    $ret = ($rows[0]['cnt'] != 0);
    $cache[$forum_id] = $ret;
    return $ret;
}

/**
 * Get an array of tickets for the given member and ticket type. If the member has permission to see others' tickets, it will be a list of all tickets
 * in the system, restricted by ticket type as appropriate. Otherwise, it will be a list of that member's tickets, as restricted by ticket type.
 *
 * @param  array $filters A map of filters; supports: ticket_type_id (AUTO_LINK), only_owner_id (MEMBER), only_assigned_id (MEMBER), only_open (boolean)
 * @param  boolean $include_first_posts Whether to include first posts
 * @param  boolean $silent_error_handling Whether to skip showing errors, returning null instead
 * @return array Array of tickets, empty on failure
 */
function get_tickets($filters = array(), $include_first_posts = false, $silent_error_handling = false)
{
    $ticket_type_id = array_key_exists('ticket_type_id', $filters) ? $filters['ticket_type_id'] : null;
    if (($ticket_type_id !== null) && (!has_category_access(get_member(), 'tickets', strval($ticket_type_id)))) {
        return array();
    }

    $only_owner_id = array_key_exists('only_owner_id', $filters) ? $filters['only_owner_id'] : get_member();
    if (!has_privilege(get_member(), 'view_others_tickets')) {
        $only_owner_id = get_member();
    }

    $only_assigned_id = array_key_exists('only_assigned_id', $filters) ? $filters['only_assigned_id'] : null; // TODO #2330

    $only_open = array_key_exists('only_open', $filters) ? $filters['only_open'] : false;

    // --

    if ($ticket_type_id !== null) {
        $ticket_type_name = $GLOBALS['SITE_DB']->query_select_value('ticket_types', 'ticket_type_name', array('id' => $ticket_type_id));
    }

    // Forum query
    if ($only_owner_id !== null) {
        $restrict = strval($only_owner_id) . '\_%';
        $restrict_description = do_lang('SUPPORT_TICKET') . ': #' . $restrict;
    } else {
        $restrict = '';
        $restrict_description = '';
    }

    // What forums to read from
    if ((get_option('ticket_type_forums') === '1') && (get_forum_type() == 'cns')) {
        $fid = get_ticket_forum_id(null, false, $silent_error_handling);
        if ($fid === null) {
            return array();
        }

        if ($ticket_type_id === null) {
            require_code('cns_forums');
            $forums = cns_get_all_subordinate_forums($fid, null, null, true);
        } else {
            $query = 'SELECT id FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_forums WHERE ' . db_string_equal_to('f_name', get_translated_text($ticket_type_name)) . ' AND f_parent_forum=' . strval($fid);

            $rows = $GLOBALS['FORUM_DB']->query($query, null, 0, false, true);
            $forums = collapse_2d_complexity('id', 'id', $rows);
        }
    } else {
        $forums = array(get_ticket_forum_id($ticket_type_id, false, $silent_error_handling));
    }

    if ((count($forums) == 1) && (array_key_exists(0, $forums)) && ($forums[0] === null)) {
        // Could not find ticket forum
        return array();
    }

    // Load tickets
    $max_rows = 0;
    $topics = $GLOBALS['FORUM_DRIVER']->show_forum_topics(array_flip($forums), 1000, 0, $max_rows, $restrict, true, 'lasttime', false, $restrict_description, $only_open);
    if ($topics === null) {
        return array();
    }

    // Filter tickets
    $topics_copy = $topics;
    $topics = array();
    foreach ($topics_copy as $topic) {
        $fp = $topic['firstpost'];

        // To stop Tempcode randomly making serialization sometimes change such that the refresh_if_changed is triggered
        if (!$include_first_posts) {
            unset($topic['firstpost']);
        }

        // Filter by ticket type
        if (($ticket_type_id !== null) && (strpos($fp->evaluate(), do_lang('TICKET_TYPE') . ': ' . get_translated_text($ticket_type_name)) === false)) {
            continue;
        }

        // Passed filters
        $topics[] = $topic;
    }

    // Done
    return $topics;
}
