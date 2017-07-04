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
 * @package    commandr
 */

/**
 * Module page class.
 */
class Module_admin_commandr
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Philip Withnall';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 3;
        $info['update_require_upgrade'] = true;
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
        return array(
            '!' => array('COMMANDR', 'menu/adminzone/tools/commandr'),
        );
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('commandrchat');

        delete_value('last_commandr_command');

        $GLOBALS['SITE_DB']->query_delete('group_page_access', array('page_name' => 'admin_commandr'));
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if ($upgrade_from === null) {
            $usergroups = $GLOBALS['FORUM_DRIVER']->get_usergroup_list(false, true);
            foreach (array_keys($usergroups) as $id) {
                $GLOBALS['SITE_DB']->query_insert('group_page_access', array('page_name' => 'admin_commandr', 'zone_name' => 'adminzone', 'group_id' => $id)); // Commandr very dangerous
            }
        }

        if (($upgrade_from !== null) && ($upgrade_from < 3)) {
            $GLOBALS['SITE_DB']->rename_table('occlechat', 'commandrchat');
        }

        if (($upgrade_from !== null) && ($upgrade_from < 4)) {
            $GLOBALS['SITE_DB']->drop_table_if_exists('commandrchat');
        }
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        require_code('input_filter_2');
        modsecurity_workaround_enable();

        $type = get_param_string('type', 'browse');

        require_lang('commandr');

        set_helper_panel_tutorial('tut_commandr');
        set_helper_panel_text(comcode_lang_string('DOC_COMMANDR'));

        $this->title = get_screen_title('COMMANDR');

        load_csp(array('csp_allow_eval_js' => '1')); // We need to allow dynamic JavaScript commands to execute through the Commandr interface

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_code('commandr');
        require_javascript('commandr');
        require_css('commandr');

        return $this->main_gui();
    }

    /**
     * The main Commandr GUI.
     *
     * @return Tempcode The UI
     */
    public function main_gui()
    {
        if ($GLOBALS['CURRENT_SHARE_USER'] !== null) {
            warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));
        }

        $command = post_param_string('commandr_command', '');
        if ($command != '') {
            //We've had a normal form submission
            $temp = new Virtual_shell($command);
            $commands = $temp->output_html();
        } else {
            $commands = new Tempcode();
        }

        $content = do_template('COMMANDR_MAIN', array(
            '_GUID' => '05c1e7efacc3839babfe58fe624caa61',
            'SUBMIT_URL' => build_url(array('page' => '_SELF'), '_SELF'),
            'PROMPT' => do_lang_tempcode('COMMAND_PROMPT', escape_html($GLOBALS['FORUM_DRIVER']->get_username(get_member()))),
            'COMMANDS' => $commands,
        ));

        return do_template('COMMANDR_MAIN_SCREEN', array(
            '_GUID' => 'd71ef9fa2cdaf419fee64cf3d7555225',
            'TITLE' => $this->title,
            'CONTENT' => $content,
        ));
    }
}
