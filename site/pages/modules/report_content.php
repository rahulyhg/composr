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
 * @package    tickets
 */

/**
 * Module page class.
 */
class Module_report_content
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham / Patrick Schmalstig';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 4;
        $info['update_require_upgrade'] = true;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('reported_content');

        delete_privilege('may_report_content');
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
            $GLOBALS['SITE_DB']->create_table('reported_content', array(
                'r_session_id' => '*ID_TEXT',
                'r_content_type' => '*ID_TEXT',
                'r_content_id' => '*ID_TEXT',
                'r_counts' => 'BINARY', // If the content is marked non-validated, r_counts is set to 0 for each row for it, so if it's revalidated the counts apply elsewhere
            ));
            $GLOBALS['SITE_DB']->create_index('reported_content', 'reported_already', array('r_content_type', 'r_content_id'));
        }

        if (($upgrade_from !== null) && ($upgrade_from < 3)) { // LEGACY
            $GLOBALS['SITE_DB']->alter_table_field('reported_content', 'r_session_id', 'ID_TEXT');
        }

        if (($upgrade_from === null) || ($upgrade_from < 4)) {
            add_privilege('GENERAL_SETTINGS', 'may_report_content', true);

            register_shutdown_function(function() { // Tickets module not installed yet, so we need to delay
                // Add ticket type
                require_lang('tickets');
                $map = array(
                    'guest_emails_mandatory' => 0,
                    'search_faq' => 0,
                    'cache_lead_time' => null,
                );
                $map += insert_lang('ticket_type_name', do_lang('TT_REPORTED_CONTENT'), 1);
                $ticket_type_id = $GLOBALS['SITE_DB']->query_insert('ticket_types', $map, true);
                require_code('permissions2');
                set_global_category_access('tickets', $ticket_type_id);
            });
        }
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        if (!addon_installed('tickets')) {
            return null;
        }

        return array();
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run()
    {
        i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

        $error_msg = new Tempcode();
        if (!addon_installed__autoinstall('tickets', $error_msg)) {
            return $error_msg;
        }

        $type = get_param_string('type', 'browse');

        // Bot (which runs as a dum guest) could conceivably try and index these things and we don't want that
        attach_to_screen_header('<meta name="robots" content="noindex" />'); // XHTMLXHTML

        require_lang('report_content');

        if ($type == 'browse') {
            $this->title = get_screen_title('REPORT_CONTENT');
        }

        if ($type == 'actual') {
            $this->title = get_screen_title('REPORT_CONTENT');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run()
    {
        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->form();
        }
        if ($type == 'actual') {
            return $this->actualiser();
        }

        return new Tempcode();
    }

    /**
     * The report UI.
     *
     * @return Tempcode The result of execution
     */
    public function form()
    {
        require_code('report_content');

        //$url = get_param_string('url', false, INPUT_FILTER_URL_INTERNAL); Not used, as the content type can find it
        $content_type = get_param_string('content_type');
        $content_id = get_param_string('content_id');

        return report_content_form($this->title, $content_type, $content_id);
    }

    /**
     * The report actualiser.
     *
     * @return Tempcode The result of execution
     */
    public function actualiser()
    {
        if (addon_installed('captcha')) {
            require_code('captcha');
            enforce_captcha();
        }

        require_code('report_content');

        $content_type = post_param_string('content_type');
        $content_id = post_param_string('content_id');
        $post = post_param_string('post');
        $anonymous = post_param_integer('anonymous', 0);

        $_url = report_content($content_type, $content_id, $post, $anonymous);

        $url = post_param_string('redirect', $_url->evaluate(), INPUT_FILTER_URL_INTERNAL);
        return redirect_screen($this->title, $url, do_lang_tempcode('CONTENT_REPORTED'));
    }
}
