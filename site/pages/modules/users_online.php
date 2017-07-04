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
 * @package    core_cns
 */

/**
 * Module page class.
 */
class Module_users_online
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            '!' => array('USERS_ONLINE', 'menu/social/users_online'),
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('cns');

        $this->title = get_screen_title('USERS_ONLINE');

        attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }

        $count = 0;
        require_code('users2');
        $members = get_users_online(has_privilege(get_member(), 'show_user_browsing'), null, $count);
        if (($members === null) && (has_privilege(get_member(), 'show_user_browsing'))) {
            $members = get_users_online(false, null, $count);
        }
        if ($members === null) {
            warn_exit(do_lang_tempcode('TOO_MANY_USERS_ONLINE'));
        }

        $rows = array();
        sort_maps_by($members, 'last_activity');
        $members = array_reverse($members);
        foreach ($members as $row) {
            $last_activity = $row['last_activity'];
            $member_id = $row['member_id'];
            //$username = $row['cache_username'];
            $location = $row['the_title'];
            if (($location == '') && ($row['the_type'] == 'rss')) {
                $location = 'RSS';
                $at_url = make_string_tempcode(find_script('backend'));
            } elseif (($location == '') && ($row['the_page'] == '')) {
                $at_url = new Tempcode();
            } else {
                $map = array('page' => $row['the_page']);
                if ($row['the_type'] != '') {
                    $map['type'] = $row['the_type'];
                }
                if ($row['the_id'] != '') {
                    $map['id'] = $row['the_id'];
                }
                $at_url = build_url($map, $row['the_zone']);
            }
            $ip = $row['ip'];
            if (substr($ip, -1) == '*') { // sessions IPs are not full so try and resolve to full
                if (is_guest($member_id)) {
                    if (addon_installed('stats')) {
                        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('stats', 'ip', array('session_id' => $row['the_session']));
                        if (($test !== null) && ($test != '')) {
                            $ip = $test;
                        } else {
                            $test = $GLOBALS['SITE_DB']->query_value_if_there('SELECT ip FROM ' . get_table_prefix() . 'stats WHERE ip LIKE \'' . db_encode_like(str_replace('*', '%', $ip)) . '\' AND date_and_time>=' . strval(time() - intval(60.0 * 60.0 * floatval(get_option('session_expiry_time')))) . ' ORDER BY date_and_time DESC');
                            if (($test !== null) && ($test != '')) {
                                $ip = $test;
                            }
                        }
                    }
                } else {
                    $test = $GLOBALS['FORUM_DRIVER']->get_member_ip($member_id);
                    if (($test !== null) && ($test != '')) {
                        $ip = $test;
                    }
                }
            }

            $link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($member_id);

            if ($ip != '') { // CRON?
                $rows[] = array('IP' => $ip, 'AT_URL' => $at_url, 'LOCATION' => $location, 'MEMBER' => $link, 'TIME' => integer_format(intval((time() - $last_activity) / 60)));
            }
        }

        if ($rows === array()) {
            warn_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        return do_template('CNS_USERS_ONLINE_SCREEN', array('_GUID' => '2f63e2926c5a4690d905f97661afe6cc', 'TITLE' => $this->title, 'ROWS' => $rows));
    }
}
