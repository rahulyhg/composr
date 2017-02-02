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
 * @package    awards
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_admin_awards extends Standard_crud_module
{
    public $lang_type = 'AWARD_TYPE';
    public $select_name = 'TITLE';
    public $archive_entry_point = '_SEARCH:awards';
    public $archive_label = 'VIEW_PAST_WINNERS';
    public $permission_module = 'award';
    public $menu_label = 'AWARDS';
    public $table = 'award_types';
    public $orderer = 'a_title';
    public $title_is_multi_lang = true;
    public $donext_entry_content_type = 'award_type';
    public $donext_category_content_type = null;

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
        $info['update_require_upgrade'] = true;
        $info['version'] = 4;
        $info['locked'] = true;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('award_archive');
        $GLOBALS['SITE_DB']->drop_table_if_exists('award_types');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        require_code('lang3');

        if ($upgrade_from === null) {
            $GLOBALS['SITE_DB']->create_table('award_archive', array(
                'a_type_id' => '*AUTO_LINK',
                'date_and_time' => '*TIME',
                'content_id' => 'ID_TEXT',
                'member_id' => 'MEMBER'
            ));

            $GLOBALS['SITE_DB']->create_index('award_archive', 'awardquicksearch', array('content_id'));

            $GLOBALS['SITE_DB']->create_table('award_types', array(
                'id' => '*AUTO',
                'a_title' => 'SHORT_TRANS',
                'a_description' => 'LONG_TRANS__COMCODE',
                'a_points' => 'INTEGER',
                'a_content_type' => 'ID_TEXT', // uses same naming convention as cms_merge importer
                'a_hide_awardee' => 'BINARY',
                'a_update_time_hours' => 'INTEGER',
            ));

            require_lang('awards');
            $map = array(
                'a_points' => 0,
                'a_content_type' => 'download',
                'a_hide_awardee' => 1,
                'a_update_time_hours' => 168
            );
            $map += lang_code_to_default_content('a_title', 'DOTW');
            $map += lang_code_to_default_content('a_description', 'DESCRIPTION_DOTW', true);
            $GLOBALS['SITE_DB']->query_insert('award_types', $map);
        }
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
        return array(
            'browse' => array('MANAGE_AWARDS', 'menu/adminzone/setup/awards'),
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

        require_lang('awards');

        set_helper_panel_tutorial('tut_featured');

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
        require_code('awards');
        require_code('awards2');

        $this->add_text = do_lang_tempcode('AWARD_ALLOCATEHELP');

        $this->add_one_label = do_lang_tempcode('ADD_AWARD_TYPE');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_AWARD_TYPE');
        $this->edit_one_label = do_lang_tempcode('EDIT_AWARD_TYPE');

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
            get_screen_title('MANAGE_AWARDS'),
            comcode_lang_string('DOC_AWARDS'),
            array(
                array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_AWARD_TYPE')),
                array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_AWARD_TYPE')),
            ),
            do_lang('MANAGE_AWARDS')
        );
    }

    /**
     * Standard crud_module table function.
     *
     * @param  array $url_map Details to go to build_url for link to the next screen.
     * @return array A pair: The choose table, Whether re-ordering is supported from this screen.
     */
    public function create_selection_list_choose_table($url_map)
    {
        require_code('templates_results_table');

        $hr = array();
        $hr[] = do_lang_tempcode('TITLE');
        if (addon_installed('points')) {
            $hr[] = do_lang_tempcode('POINTS');
        }
        $hr[] = do_lang_tempcode('CONTENT_TYPE');
        $hr[] = do_lang_tempcode('USED_PREVIOUSLY');
        $hr[] = do_lang_tempcode('ACTIONS');

        $current_ordering = get_param_string('sort', 'a_title ASC', INPUT_FILTER_GET_COMPLEX);
        if (strpos($current_ordering, ' ') === false) {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }
        list($sortable, $sort_order) = explode(' ', $current_ordering, 2);
        $sortables = array(
            'a_title' => do_lang_tempcode('TITLE'),
            'a_content_type' => do_lang_tempcode('CONTENT_TYPE'),
        );
        if (addon_installed('points')) {
            $sortables['a_points'] = do_lang_tempcode('POINTS');
        }
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }

        $header_row = results_field_title($hr, $sortables, 'sort', $sortable . ' ' . $sort_order);

        $fields = new Tempcode();

        require_code('form_templates');
        list($rows, $max_rows) = $this->get_entry_rows(false, $current_ordering);
        foreach ($rows as $row) {
            $edit_url = build_url($url_map + array('id' => $row['id']), '_SELF');

            $fr = array();
            $fr[] = protect_from_escaping(hyperlink(build_url(array('page' => 'awards', 'type' => 'award', 'id' => $row['id']), get_module_zone('awards')), get_translated_text($row['a_title']), false, true));
            if (addon_installed('points')) {
                $fr[] = integer_format($row['a_points']);
            }
            $hooks = find_all_hooks('systems', 'content_meta_aware');
            $hook_title = do_lang('UNKNOWN');
            foreach (array_keys($hooks) as $hook) {
                if ($hook == $row['a_content_type']) {
                    require_code('content');
                    $hook_object = get_content_object($hook);
                    if ($hook_object === null) {
                        continue;
                    }
                    $hook_info = $hook_object->info();
                    if ($hook_info !== null) {
                        $hook_title = do_lang($hook_info['content_type_label']);
                    }
                }
            }
            $fr[] = $hook_title;
            $fr[] = integer_format($GLOBALS['SITE_DB']->query_select_value('award_archive', 'COUNT(*)', array('a_type_id' => $row['id'])));
            $fr[] = protect_from_escaping(hyperlink($edit_url, do_lang_tempcode('EDIT'), false, false, do_lang('EDIT') . ' #' . strval($row['id'])));

            $fields->attach(results_entry($fr, true));
        }

        return array(results_table(do_lang($this->menu_label), get_param_integer('start', 0), 'start', either_param_integer('max', 20), 'max', $max_rows, $header_row, $fields, $sortables, $sortable, $sort_order), false);
    }

    /**
     * Get Tempcode for adding/editing form.
     *
     * @param  ?AUTO_LINK $id The ID of the award (null: not added yet)
     * @param  SHORT_TEXT $title The title
     * @param  LONG_TEXT $description The description
     * @param  integer $points How many points are given to the awardee
     * @param  ID_TEXT $content_type The content type the award type is for
     * @param  ?BINARY $hide_awardee Whether to not show the awardee when displaying this award (null: statistical default)
     * @param  integer $update_time_hours The approximate time in hours between awards (e.g. 168 for a week)
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($id = null, $title = '', $description = '', $points = 0, $content_type = 'download', $hide_awardee = null, $update_time_hours = 168)
    {
        if ($hide_awardee === null) {
            $val = $GLOBALS['SITE_DB']->query_select_value('award_types', 'AVG(a_hide_awardee)');
            $hide_awardee = ($val === null) ? 1 : intval(round($val));
        }

        $fields = new Tempcode();
        $fields->attach(form_input_line(do_lang_tempcode('TITLE'), do_lang_tempcode('DESCRIPTION_TITLE'), 'title', $title, true));
        $fields->attach(form_input_text_comcode(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode('DESCRIPTION_DESCRIPTION'), 'description', $description, true));
        if (addon_installed('points')) {
            $fields->attach(form_input_integer(do_lang_tempcode('POINTS'), do_lang_tempcode('DESCRIPTION_AWARD_POINTS'), 'points', $points, true));
        }
        $list = new Tempcode();
        $_hooks = array();
        $hooks = find_all_hooks('systems', 'content_meta_aware');
        foreach (array_keys($hooks) as $hook) {
            require_code('content');
            $hook_object = get_content_object($hook);
            if ($hook_object === null) {
                continue;
            }
            $hook_info = $hook_object->info();
            if ($hook_info !== null) {
                $_hooks[$hook] = do_lang($hook_info['content_type_label']);
            }
        }
        asort($_hooks);
        foreach ($_hooks as $hook => $hook_title) {
            $list->attach(form_input_list_entry($hook, $hook == $content_type, protect_from_escaping($hook_title)));
        }
        if ($list->is_empty()) {
            inform_exit(do_lang_tempcode('NO_CATEGORIES'));
        }
        $fields->attach(form_input_list(do_lang_tempcode('CONTENT_TYPE'), do_lang_tempcode('DESCRIPTION_CONTENT_TYPE'), 'content_type', $list));
        $fields->attach(form_input_tick(do_lang_tempcode('HIDE_AWARDEE'), do_lang_tempcode('DESCRIPTION_HIDE_AWARDEE'), 'hide_awardee', $hide_awardee == 1));
        $fields->attach(form_input_integer(do_lang_tempcode('AWARD_UPDATE_TIME_HOURS'), do_lang_tempcode('DESCRIPTION_AWARD_UPDATE_TIME_HOURS'), 'update_time_hours', $update_time_hours, true));

        // Permissions
        $fields->attach($this->get_permission_fields(($id === null) ? null : strval($id), do_lang_tempcode('AWARD_PERMISSION_HELP'), false/*We want permissions off by default so we do not say new category ($id === null)*/, do_lang_tempcode('GIVE_AWARD')));

        return array($fields, new Tempcode());
    }

    /**
     * Standard crud_module list function.
     *
     * @return Tempcode The selection list
     */
    public function create_selection_list_entries()
    {
        $_m = $GLOBALS['SITE_DB']->query_select('award_types', array('id', 'a_title'));
        $entries = new Tempcode();
        foreach ($_m as $m) {
            $entries->attach(form_input_list_entry(strval($m['id']), false, get_translated_text($m['a_title'])));
        }

        return $entries;
    }

    /**
     * Standard crud_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A pair: The input fields, Hidden fields
     */
    public function fill_in_edit_form($id)
    {
        $m = $GLOBALS['SITE_DB']->query_select('award_types', array('*'), array('id' => intval($id)), '', 1);
        if (!array_key_exists(0, $m)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'award_type'));
        }
        $r = $m[0];

        $fields = $this->get_form_fields(intval($id), get_translated_text($r['a_title']), get_translated_text($r['a_description']), $r['a_points'], $r['a_content_type'], $r['a_hide_awardee'], $r['a_update_time_hours']);

        return $fields;
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        $id = add_award_type(post_param_string('title'), post_param_string('description'), post_param_integer('points', 0), post_param_string('content_type'), post_param_integer('hide_awardee', 0), post_param_integer('update_time_hours'));

        $this->set_permissions(strval($id));

        return strval($id);
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     */
    public function edit_actualisation($id)
    {
        edit_award_type(intval($id), post_param_string('title'), post_param_string('description'), post_param_integer('points', 0), post_param_string('content_type'), post_param_integer('hide_awardee', 0), post_param_integer('update_time_hours'));

        $this->set_permissions($id);
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @param  ID_TEXT $id The entry being deleted
     */
    public function delete_actualisation($id)
    {
        delete_award_type(intval($id));
    }
}
