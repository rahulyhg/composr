<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    points
 */

/**
 * Block class.
 */
class Block_main_leader_board
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 3;
        $info['locked'] = false;
        $info['parameters'] = array('zone');
        $info['update_require_upgrade'] = true;
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = 'array(array_key_exists(\'zone\',$map)?$map[\'zone\']:get_module_zone(\'leader_board\'))';
        $info['ttl'] = 60 * 15; // 15 minutes
        return $info;
    }

    /**
     * Install the block.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if ($upgrade_from === null) {
            $GLOBALS['SITE_DB']->create_table('leader_board', array(
                'lb_member' => '*MEMBER',
                'lb_points' => 'INTEGER',
                'date_and_time' => '*TIME'
            ));
        }
    }

    /**
     * Uninstall the block.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('leader_board');
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('leader_board');

        require_lang('leader_board');
        require_code('points');
        require_css('points');

        require_code('leader_board');
        $rows = calculate_latest_leader_board();

        $out = new Tempcode();
        $i = 0;

        // Are there any rank images going to display?
        $or_list = '1=1';
        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
        $moderator_groups = $GLOBALS['FORUM_DRIVER']->get_moderator_groups();
        foreach (array_merge($admin_groups, $moderator_groups) as $group_id) {
            $or_list .= ' AND id<>' . strval($group_id);
        }
        $has_rank_images = (get_forum_type() == 'cns') && ($GLOBALS['FORUM_DB']->query_value_if_there('SELECT COUNT(*) FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_groups WHERE ' . $or_list . ' AND ' . db_string_not_equal_to('g_rank_image', '')) != 0);

        foreach ($rows as $member => $points) {
            $points_url = build_url(array('page' => 'points', 'type' => 'member', 'id' => $member), get_module_zone('points'));

            $profile_url = $GLOBALS['FORUM_DRIVER']->member_profile_url($member, true);

            $username = $GLOBALS['FORUM_DRIVER']->get_username($member);
            if ($username === null) {
                continue; // Deleted member now
            }

            $out->attach(do_template('POINTS_LEADER_BOARD_ROW', array(
                '_GUID' => '68caa55091aade84bc7ca760e6655a45',
                'ID' => strval($member),
                'POINTS_URL' => $points_url,
                'PROFILE_URL' => $profile_url,
                'POINTS' => integer_format($points),
                'USERNAME' => $username,
                'HAS_RANK_IMAGES' => $has_rank_images,
            )));

            $i++;
        }

        $url = build_url(array('page' => 'leader_board'), $zone);

        return do_template('POINTS_LEADER_BOARD', array(
            '_GUID' => 'c875cce925e73f46408acc0a153a2902',
            'URL' => $url,
            'LIMIT' => integer_format(intval(get_option('leader_board_size'))),
            'ROWS' => $out,
        ));
    }
}
