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
 * @package    cns_clubs
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_cms_cns_groups extends Standard_crud_module
{
    public $lang_type = 'CLUB';
    public $select_name = 'NAME';
    public $content_type = 'group';
    public $possibly_some_kind_of_upload = true;
    public $output_of_action_is_confirmation = true;
    public $menu_label = 'CLUBS';
    public $do_preview = null;
    public $view_entry_point = '_SEARCH:groups:view:_ID';
    public $orderer = 'g_name';

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
        return array(
            'browse' => array('MANAGE_CLUBS', 'menu/cms/clubs'),
        ) + parent::get_entry_points();
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @param  boolean $top_level Whether this is running at the top level, prior to having sub-objects called.
     * @param  ?ID_TEXT $type The screen type to consider for metadata purposes (null: read from environment).
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run($top_level = true, $type = null)
    {
        $type = get_param_string('type', 'browse');

        require_lang('cns');

        set_helper_panel_tutorial('tut_subcom');

        return parent::pre_run($top_level);
    }

    /**
     * Standard crud_module run_start.
     *
     * @param  ID_TEXT $type The type of module execution
     * @return Tempcode The output of the run
     */
    public function run_start($type)
    {
        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }
        require_code('cns_groups_action');
        require_code('cns_forums_action');
        require_code('cns_groups_action2');
        require_code('cns_forums_action2');

        $this->add_one_label = do_lang_tempcode('ADD_CLUB');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_CLUB');
        $this->edit_one_label = do_lang_tempcode('EDIT_CLUB');

        if ($type == 'browse') {
            return $this->browse();
        }
        return new Tempcode();
    }

    /**
     * The do-next manager for before content management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        require_code('templates_donext');
        return do_next_manager(
            get_screen_title('MANAGE_CLUBS'),
            comcode_lang_string('DOC_CLUBS'),
            array(
                array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_CLUB')),
                array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_CLUB')),
            ),
            do_lang('MANAGE_CLUBS')
        );
    }

    /**
     * Get Tempcode for a adding/editing form.
     *
     * @param  ?GROUP $id The usergroup being edited (null: adding, not editing, and let's choose the current member)
     * @param  SHORT_TEXT $name The usergroup name
     * @param  ?ID_TEXT $group_leader The username of the usergroup leader (null: none picked yet)
     * @param  BINARY $open_membership Whether members may join this usergroup without requiring any special permission
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($id = null, $name = '', $group_leader = null, $open_membership = 1)
    {
        if ($group_leader === null) {
            $group_leader = $GLOBALS['FORUM_DRIVER']->get_username(get_member());
        }

        $fields = new Tempcode();
        $fields->attach(form_input_line(do_lang_tempcode('NAME'), do_lang_tempcode('DESCRIPTION_USERGROUP_TITLE'), 'name', $name, true));
        $fields->attach(form_input_username(do_lang_tempcode('GROUP_LEADER'), do_lang_tempcode('DESCRIPTION_GROUP_LEADER'), 'group_leader', $group_leader, false));
        $fields->attach(form_input_tick(do_lang_tempcode('OPEN_MEMBERSHIP'), do_lang_tempcode('OPEN_MEMBERSHIP_DESCRIPTION'), 'open_membership', $open_membership == 1));

        return array($fields, new Tempcode());
    }

    /**
     * Standard crud_module table function.
     *
     * @param  array $url_map Details to go to build_url for link to the next screen.
     * @return array A quartet: The choose table, Whether re-ordering is supported from this screen, Search URL, Archive URL.
     */
    public function create_selection_list_choose_table($url_map)
    {
        require_code('templates_results_table');

        $default_order = 'g_name ASC';
        $current_ordering = get_param_string('sort', $default_order, INPUT_FILTER_GET_COMPLEX);
        $sortables = array(
            'g_name' => do_lang_tempcode('NAME'),
        );
        if (strpos($current_ordering, ' ') === false) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }

        $header_row = results_field_title(array(
            do_lang_tempcode('NAME'),
            do_lang_tempcode('OPEN_MEMBERSHIP'),
            do_lang_tempcode('ACTIONS'),
        ), $sortables, 'sort', $sortable . ' ' . $sort_order);

        $fields = new Tempcode();

        $count = $GLOBALS['FORUM_DB']->query_select_value('f_groups', 'COUNT(*)', array('g_is_private_club' => 1));
        list($rows, $max_rows) = $this->get_entry_rows(false, $current_ordering, ($count > 300 || (!has_privilege(get_member(), 'control_usergroups'))) ? array('g_group_leader' => get_member(), 'g_is_private_club' => 1) : array('g_is_private_club' => 1));
        foreach ($rows as $row) {
            $edit_url = build_url($url_map + array('id' => $row['id']), '_SELF');

            $fr = array(
                protect_from_escaping(cns_get_group_link($row['id'])),
                ($row['g_open_membership'] == 1) ? do_lang_tempcode('YES') : do_lang_tempcode('NO'),
            );

            $fr[] = protect_from_escaping(hyperlink($edit_url, do_lang_tempcode('EDIT'), false, true, do_lang('EDIT') . ' #' . strval($row['id'])));

            $fields->attach(results_entry($fr, true));
        }

        $search_url = build_url(array('page' => 'search', 'id' => 'cns_clubs'), get_module_zone('search'));
        $archive_url = build_url(array('page' => 'groups'), get_module_zone('groups'));

        return array(results_table(do_lang($this->menu_label), get_param_integer('start', 0), 'start', either_param_integer('max', 20), 'max', $max_rows, $header_row, $fields, $sortables, $sortable, $sort_order, 'sort'), false, $search_url, $archive_url);
    }

    /**
     * Standard crud_module list function.
     *
     * @return Tempcode The selection list
     */
    public function create_selection_list_entries()
    {
        $fields = new Tempcode();
        $count = $GLOBALS['FORUM_DB']->query_select_value('f_groups', 'COUNT(*)', array('g_is_private_club' => 1));
        if ($count < intval(get_option('general_safety_listing_limit'))) {
            $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name', 'g_promotion_target', 'g_is_super_admin', 'g_group_leader'), array('g_is_private_club' => 1), 'ORDER BY g_name');
        } else {
            $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('id', 'g_name', 'g_promotion_target', 'g_is_super_admin', 'g_group_leader'), array('g_group_leader' => get_member(), 'g_is_private_club' => 1), 'ORDER BY g_name');
            if (count($rows) == 0) {
                warn_exit(do_lang_tempcode('TOO_MANY_TO_CHOOSE_FROM'));
            }
        }
        foreach ($rows as $row) {
            $is_super_admin = $row['g_is_super_admin'];
            if ((!has_privilege(get_member(), 'control_usergroups')) || ($is_super_admin == 1)) {
                $leader = $row['g_group_leader'];
                if ($leader != get_member()) {
                    continue;
                }
            }
            $fields->attach(form_input_list_entry(strval($row['id']), false, get_translated_text($row['g_name'], $GLOBALS['FORUM_DB'])));
        }

        return $fields;
    }

    /**
     * Standard crud_module delete possibility checker.
     *
     * @param  ID_TEXT $id The entry being potentially deleted
     * @return boolean Whether it may be deleted
     */
    public function may_delete_this($id)
    {
        return ((intval($id) != db_get_first_id() + 0) && (intval($id) != db_get_first_id() + 1) && (intval($id) != db_get_first_id() + 8));
    }

    /**
     * Standard aed_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A pair: The input fields, Hidden fields
     */
    public function fill_in_edit_form($id)
    {
        $rows = $GLOBALS['FORUM_DB']->query_select('f_groups', array('*'), array('id' => intval($id), 'g_is_private_club' => 1));
        if (!array_key_exists(0, $rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'group'));
        }
        $myrow = $rows[0];

        $username = $GLOBALS['FORUM_DRIVER']->get_username($myrow['g_group_leader']);
        if ($username === null) {
            $username = '';
        }//do_lang('UNKNOWN');
        return $this->get_form_fields($id, get_translated_text($myrow['g_name'], $GLOBALS['FORUM_DB']), $username, $myrow['g_open_membership']);
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        require_code('cns_forums_action2');

        $_group_leader = post_param_string('group_leader');
        if ($_group_leader != '') {
            $group_leader = $GLOBALS['FORUM_DRIVER']->get_member_from_username($_group_leader);
            if ($group_leader === null) {
                warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST', escape_html($_group_leader)));
            }
        } else {
            $group_leader = null;
        }

        $name = post_param_string('name');
        $id = cns_make_group($name, 0, 0, 0, '', '', null, null, $group_leader, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1000, 0, post_param_integer('open_membership', 0), 1);

        // Create forum
        $mods = $GLOBALS['FORUM_DRIVER']->get_moderator_groups();
        $access_mapping = array();
        foreach ($mods as $m_id) {
            $access_mapping[$m_id] = 5;
        }
        $_cat = get_option('club_forum_parent_forum_grouping');
        if (is_numeric($_cat)) {
            $cat = intval($_cat);
        } else {
            $cat = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_forum_groupings', 'id', array('c_title' => $_cat));
            if ($cat === null) {
                $cat = $GLOBALS['FORUM_DB']->query_select_value('f_forum_groupings', 'MIN(id)');
            }
        }
        $_forum = get_option('club_forum_parent_forum');
        if (is_numeric($_forum)) {
            $forum = intval($_forum);
        } else {
            $forum = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums', 'id', array('f_name' => $_forum));
            if ($forum === null) {
                $forum = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'MIN(id)');
            }
        }
        $is_threaded = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums', 'f_is_threaded', array('id' => $forum));
        $allows_anonymous_posts = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums', 'f_allows_anonymous_posts', array('id' => $forum));
        $forum_id = cns_make_forum($name, do_lang('FORUM_FOR_CLUB', $name), $cat, $access_mapping, $forum, 1, 1, 0, '', '', '', 'last_post', $is_threaded, $allows_anonymous_posts);
        $this->_set_permissions($id, $forum_id);

        require_code('cns_groups_action2');
        cns_add_member_to_group(get_member(), $id);

        if (has_actual_page_access(get_modal_user(), 'groups')) {
            require_code('activities');
            syndicate_described_activity('cns:ACTIVITY_ADD_CLUB', $name, '', '', '_SEARCH:groups:view:' . strval($id), '', '', 'cns_clubs');
        }

        return strval($id);
    }

    /**
     * Fix club's permissons (in case e.g. forum was recreated).
     *
     * @param  AUTO_LINK $id Club (usergroup) ID
     * @param  AUTO_LINK $forum_id Forum ID
     */
    public function _set_permissions($id, $forum_id)
    {
        // Cleanup
        $GLOBALS['FORUM_DB']->query_delete('group_privileges', array('group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id)));
        $GLOBALS['FORUM_DB']->query_delete('group_category_access', array(
            'module_the_name' => 'forums',
            'category_name' => strval($forum_id),
            'group_id' => $id
        ));

        // Create permissions
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'submit_midrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'edit_own_midrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'bypass_validation_midrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'submit_lowrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'edit_own_lowrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'delete_own_lowrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_privileges', array('privilege' => 'bypass_validation_lowrange_content', 'group_id' => $id, 'the_page' => '', 'module_the_name' => 'forums', 'category_name' => strval($forum_id), 'the_value' => 1));
        $GLOBALS['FORUM_DB']->query_insert('group_category_access', array(
            'module_the_name' => 'forums',
            'category_name' => strval($forum_id),
            'group_id' => $id
        ));
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return ?Tempcode Confirm message (null: continue)
     */
    public function edit_actualisation($id)
    {
        $group_id = intval($id);
        require_code('cns_groups');
        $leader = cns_get_group_property($group_id, 'group_leader');
        $is_super_admin = cns_get_group_property($group_id, 'is_super_admin');
        if ((!has_privilege(get_member(), 'control_usergroups')) || ($is_super_admin == 1)) {
            if ($leader != get_member()) {
                access_denied('I_ERROR');
            }
        }

        $old_name = cns_get_group_name($group_id);

        $_group_leader = post_param_string('group_leader');
        if ($_group_leader != '') {
            $group_leader = $GLOBALS['FORUM_DRIVER']->get_member_from_username($_group_leader);
            if ($group_leader === null) {
                warn_exit(do_lang_tempcode('_MEMBER_NO_EXIST', escape_html($_group_leader)));
            }
        } else {
            $group_leader = null;
        }

        $name = post_param_string('name');

        cns_edit_group($group_id, $name, null, null, null, null, null, null, null, $group_leader, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, post_param_integer('open_membership', 0), 1);

        $forum_where = array('f_name' => $old_name, 'f_forum_grouping_id' => intval(get_option('club_forum_parent_forum_grouping')), 'f_parent_forum' => intval(get_option('club_forum_parent_forum')));
        $forum_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_forums', 'id', $forum_where);
        if ($forum_id !== null) {
            $this->_set_permissions(intval($id), $forum_id);
        }

        // Rename forum
        if ($name != $old_name) {
            $GLOBALS['FORUM_DB']->query_update('f_forums', array('f_name' => $name), $forum_where, 'ORDER BY id DESC', 1);
        }

        return null;
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @param  ID_TEXT $id The entry being deleted
     */
    public function delete_actualisation($id)
    {
        $group_id = intval($id);
        require_code('cns_groups');
        $leader = cns_get_group_property($group_id, 'group_leader');
        $is_super_admin = cns_get_group_property($group_id, 'is_super_admin');
        if ((!has_privilege(get_member(), 'control_usergroups')) || ($is_super_admin == 1)) {
            if ($leader != get_member()) {
                access_denied('I_ERROR');
            }
        }
        cns_delete_group($group_id);
    }
}
