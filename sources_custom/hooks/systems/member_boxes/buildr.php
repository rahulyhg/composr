<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    buildr
 */

/**
 * Hook class.
 */
class Hook_member_boxes_buildr
{
    /**
     * Find member box details.
     *
     * @param  MEMBER $member_id The ID of the member we are getting extra details for
     * @return array Map of extra box details
     */
    public function run($member_id)
    {
        if (!$GLOBALS['SITE_DB']->table_exists('w_realms')) {
            return null;
        }

        $zone = get_page_zone('buildr', false);
        if ($zone === null) {
            return array();
        }
        if (!has_zone_access(get_member(), $zone)) {
            return array();
        }

        $rows = $GLOBALS['SITE_DB']->query_select('w_members m JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'w_realms r ON m.location_realm=r.id JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'w_rooms rm ON r.location_x=rm.location_x AND r.location_y=rm.location_y AND r.location_realm=rm.location_realm', array('m.*', 'r.*', 'rm.name AS room_name'), array('m.id' => $member_id), '', 1, 0, true);
        if (($rows === null) || (!array_key_exists(0, $rows))) {
            return array();
        }

        $row = $rows[0];

        require_lang('buildr');

        return array(
            do_lang('_W_ROOM') => do_lang('W_ROOM_COORD', escape_html($row['room_name']), strval($row['location_realm']), array(strval($row['location_x']), strval($row['location_y']))),
            do_lang('_W_REALM') => $row['name'],
        );
    }
}
