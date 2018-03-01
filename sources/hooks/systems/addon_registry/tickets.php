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
 * Hook class.
 */
class Hook_addon_registry_tickets
{
    /**
     * Get a list of file permissions to set.
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for.
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon.
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'A support ticket system. Also provides an integrated standalone contact block, and integrated content reporting functionality.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_feedback',
            'tut_support_desk',
            'tut_staff',
        );
    }

    /**
     * Get a mapping of dependency types.
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used.
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/menu/site_meta/tickets.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/site_meta/tickets.svg',
            'themes/default/images/icons/buttons/add_ticket.svg',
            'themes/default/images/icons/buttons/new_reply_staff_only.svg',
            'sources/hooks/systems/resource_meta_aware/ticket_type.php',
            'sources/hooks/systems/commandr_fs/ticket_types.php',
            'sources/hooks/systems/addon_registry/tickets.php',
            'sources/hooks/modules/admin_import_types/tickets.php',
            'themes/default/templates/SUPPORT_TICKET_TYPE_SCREEN.tpl',
            'themes/default/templates/SUPPORT_TICKET_SCREEN.tpl',
            'themes/default/templates/SUPPORT_TICKETS_SCREEN.tpl',
            'themes/default/templates/SUPPORT_TICKET_LINK.tpl',
            'themes/default/templates/SUPPORT_TICKETS_SEARCH_SCREEN.tpl',
            'adminzone/pages/modules/admin_tickets.php',
            'themes/default/css/tickets.css',
            'lang/EN/tickets.ini',
            'site/pages/modules/tickets.php',
            'sources/hooks/systems/change_detection/tickets.php',
            'sources/hooks/systems/page_groupings/tickets.php',
            'sources/hooks/systems/module_permissions/tickets.php',
            'sources/hooks/systems/rss/tickets.php',
            'sources/hooks/systems/cron/ticket_type_lead_times.php',
            'sources/tickets.php',
            'sources/tickets2.php',
            'sources/hooks/systems/preview/ticket.php',
            'sources/hooks/blocks/main_staff_checklist/tickets.php',
            'sources/hooks/systems/notifications/ticket_reply.php',
            'sources/hooks/systems/notifications/ticket_new_staff.php',
            'sources/hooks/systems/notifications/ticket_reply_staff.php',
            'sources/hooks/systems/notifications/ticket_assigned_staff.php',
            'sources/tickets_email_integration.php',
            'sources/report_content.php',
            'sources/hooks/systems/cron/tickets_email_integration.php',
            'sources/hooks/systems/config/ticket_forum_name.php',
            'sources/hooks/systems/config/ticket_text.php',
            'sources/hooks/systems/config/ticket_type_forums.php',
            'sources/hooks/systems/config/ticket_mail_on.php',
            'sources/hooks/systems/config/ticket_email_from.php',
            'sources/hooks/systems/config/ticket_mail_server.php',
            'sources/hooks/systems/config/ticket_mail_server_port.php',
            'sources/hooks/systems/config/ticket_mail_server_type.php',
            'sources/hooks/systems/config/ticket_mail_username.php',
            'sources/hooks/systems/config/ticket_mail_password.php',
            'sources/hooks/systems/config/support_operator.php',
            'sources/hooks/systems/config/ticket_auto_assign.php',
            'data/incoming_ticket_email.php',
            'sources/hooks/systems/commandr_fs_extended_member/ticket_known_emailers.php',
            'lang/EN/report_content.ini',
            'site/pages/modules/report_content.php',
            'themes/default/text/CNS_REPORTED_POST_FCOMCODE.txt',
            'themes/default/text/REPORTED_CONTENT_FCOMCODE.txt',
            'sources/hooks/systems/config/reported_times.php',
            'themes/default/images/icons/menu/site_meta/contact_us.svg',
            'themes/default/templates/BLOCK_MAIN_CONTACT_US.tpl',
            'sources/blocks/main_contact_us.php',
            'themes/default/javascript/tickets.js',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them.
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/SUPPORT_TICKET_LINK.tpl' => 'support_tickets_screen',
            'templates/SUPPORT_TICKETS_SCREEN.tpl' => 'support_tickets_screen',
            'templates/SUPPORT_TICKET_SCREEN.tpl' => 'support_ticket_screen',
            'templates/SUPPORT_TICKETS_SEARCH_SCREEN.tpl' => 'support_tickets_search_screen',
            'templates/SUPPORT_TICKET_TYPE_SCREEN.tpl' => 'support_ticket_type_screen',
            'text/CNS_REPORTED_POST_FCOMCODE.txt' => 'cns_reported_post_fcomcode',
            'text/REPORTED_CONTENT_FCOMCODE.txt' => 'reported_content_fcomcode',
            'templates/BLOCK_MAIN_CONTACT_US.tpl' => 'block_main_contact_us',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__support_tickets_screen()
    {
        $links = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $links->attach(do_lorem_template('SUPPORT_TICKET_LINK', array(
                'NUM_POSTS' => placeholder_number(),
                'CLOSED' => lorem_phrase(),
                'URL' => placeholder_url(),
                'TITLE' => lorem_phrase(),
                'EXTRA_DETAILS' => '',
                'TICKET_TYPE_NAME' => lorem_phrase(),
                'TICKET_TYPE_ID' => placeholder_id(),
                'FIRST_DATE' => placeholder_date(),
                'FIRST_DATE_RAW' => placeholder_date_raw(),
                'FIRST_POSTER_PROFILE_URL' => placeholder_url(),
                'FIRST_POSTER' => lorem_phrase(),
                'FIRST_POSTER_ID' => placeholder_id(),
                'LAST_POSTER_PROFILE_URL' => placeholder_url(),
                'LAST_POSTER' => lorem_phrase(),
                'LAST_POSTER_ID' => placeholder_id(),
                'LAST_DATE' => placeholder_date(),
                'LAST_DATE_RAW' => placeholder_date_raw(),
                'ID' => placeholder_id(),
                'ASSIGNED' => array(),
            )));
        }

        return array(
            lorem_globalise(do_lorem_template('SUPPORT_TICKETS_SCREEN', array(
                'TITLE' => lorem_title(),
                'MESSAGE' => lorem_phrase(),
                'LINKS' => $links,
                'TICKET_TYPE_ID' => placeholder_id(),
                'NAME' => lorem_word_2(),
                'SELECTED' => true,
                'ADD_TICKET_URL' => placeholder_url(),
                'TYPES' => placeholder_array(),
                'LEAD_TIME' => placeholder_number(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__support_ticket_screen()
    {
        require_lang('cns');

        $comments = new Tempcode();

        $comment_form = do_lorem_template('COMMENTS_POSTING_FORM', array(
            'TITLE' => lorem_phrase(),
            'JOIN_BITS' => lorem_phrase_html(),
            'USE_CAPTCHA' => false,
            'GET_EMAIL' => true,
            'EMAIL_OPTIONAL' => true,
            'GET_TITLE' => true,
            'TITLE_OPTIONAL' => true,
            'DEFAULT_TITLE' => '',
            'POST_WARNING' => '',
            'RULES_TEXT' => '',
            'ATTACHMENTS' => null,
            'ATTACH_SIZE_FIELD' => null,
            'TRUE_ATTACHMENT_UI' => false,
            'EMOTICONS' => placeholder_emoticon_chooser(),
            'DISPLAY' => 'block',
            'FIRST_POST_URL' => '',
            'FIRST_POST' => '',
            'COMMENT_URL' => '',
        ));

        $other_tickets = new Tempcode();
        foreach (placeholder_array() as $k => $v) {
            $other_tickets->attach(do_lorem_template('SUPPORT_TICKET_LINK', array(
                'NUM_POSTS' => placeholder_number(),
                'CLOSED' => lorem_phrase(),
                'URL' => placeholder_url(),
                'TITLE' => lorem_phrase(),
                'EXTRA_DETAILS' => '',
                'TICKET_TYPE_NAME' => lorem_phrase(),
                'TICKET_TYPE_ID' => placeholder_id(),
                'DATE' => placeholder_date(),
                'DATE_RAW' => placeholder_date_raw(),
                'PROFILE_URL' => placeholder_url(),
                'FIRST_POSTER_PROFILE_URL' => placeholder_url(),
                'FIRST_POSTER' => lorem_phrase(),
                'FIRST_POSTER_ID' => placeholder_id(),
                'LAST_POSTER_PROFILE_URL' => placeholder_url(),
                'LAST_POSTER' => lorem_phrase(),
                'LAST_POSTER_ID' => placeholder_id(),
                'LAST_DATE' => placeholder_date(),
                'LAST_DATE_RAW' => placeholder_date_raw(),
                'UNCLOSED' => lorem_word(),
                'ID' => placeholder_id(),
                'ASSIGNED' => array(),
            )));
        }

        $whos_read = array();
        $whos_read[] = array(
            'USERNAME' => lorem_word(),
            'MEMBER_ID' => placeholder_id(),
            'MEMBER_URL' => placeholder_url(),
            'DATE' => lorem_word(),
        );

        return array(
            lorem_globalise(do_lorem_template('SUPPORT_TICKET_SCREEN', array(
                'ID' => placeholder_id(),
                'TOGGLE_TICKET_CLOSED_URL' => placeholder_url(),
                'CLOSED' => lorem_phrase(),
                'USERNAME' => lorem_word(),
                'PING_URL' => placeholder_url(),
                'WARNING_DETAILS' => '',
                'NEW' => lorem_phrase(),
                'TICKET_TYPE' => null,
                'TICKET_PAGE_TEXT' => lorem_sentence_html(),
                'POST_TEMPLATES' => '',
                'TYPES' => placeholder_array(),
                'STAFF_ONLY' => true,
                'POSTER' => lorem_phrase(),
                'TITLE' => lorem_title(),
                'COMMENTS' => $comments,
                'COMMENT_FORM' => $comment_form,
                'STAFF_DETAILS' => placeholder_url(),
                'URL' => placeholder_url(),
                'ADD_TICKET_URL' => placeholder_url(),
                'OTHER_TICKETS' => $other_tickets,
                'SET_TICKET_EXTRA_ACCESS_URL' => placeholder_url(),
                'ASSIGNED' => array(),
                'EXTRA_DETAILS' => lorem_phrase(),
                'WHOS_READ' => $whos_read,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__support_tickets_search_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('SUPPORT_TICKETS_SEARCH_SCREEN', array(
                'TITLE' => lorem_title(),
                'URL' => placeholder_url(),
                'POST_FIELDS' => '',
                'RESULTS' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__support_ticket_type_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('SUPPORT_TICKET_TYPE_SCREEN', array(
                'TITLE' => lorem_title(),
                'TPL' => placeholder_form(),
                'ADD_FORM' => placeholder_form(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__cns_reported_post_fcomcode()
    {
        require_lang('cns');
        require_css('cns');
        return array(
            lorem_globalise(do_lorem_template('CNS_REPORTED_POST_FCOMCODE', array(
                'POST_ID' => placeholder_id(),
                'POST_MEMBER_ID' => placeholder_id(),
                'POST_MEMBER' => lorem_phrase(),
                'TOPIC_TITLE' => lorem_phrase(),
                'POST' => lorem_phrase(),
                'REPORT_POST' => lorem_phrase(),
            ), null, false, null, '.txt', 'text'), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__reported_content_fcomcode()
    {
        return array(
            lorem_globalise(do_lorem_template('REPORTED_CONTENT_FCOMCODE', array(
                'CONTENT_URL' => placeholder_url(),
                'CONTENT_TYPE' => lorem_word(),
                'CONTENT_ID' => placeholder_id(),
                'CONTENT_MEMBER' => lorem_phrase(),
                'CONTENT_MEMBER_ID' => placeholder_id(),
                'CONTENT_TITLE' => lorem_phrase(),
                'CONTENT_RENDERED' => lorem_paragraph_html(),
                'REPORT_POST' => lorem_paragraph(),
            ), null, false, null, '.txt', 'text'), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_main_contact_us()
    {
        require_javascript('posting');

        $comment_details = do_lorem_template('COMMENTS_POSTING_FORM', array(
            'TITLE' => lorem_phrase(),
            'JOIN_BITS' => lorem_phrase_html(),
            'USE_CAPTCHA' => false,
            'GET_EMAIL' => true,
            'EMAIL_OPTIONAL' => true,
            'GET_TITLE' => true,
            'TITLE_OPTIONAL' => true,
            'DEFAULT_TITLE' => '',
            'POST_WARNING' => '',
            'RULES_TEXT' => '',
            'ATTACHMENTS' => null,
            'ATTACH_SIZE_FIELD' => null,
            'TRUE_ATTACHMENT_UI' => false,
            'EMOTICONS' => placeholder_emoticon_chooser(),
            'DISPLAY' => 'block',
            'FIRST_POST_URL' => '',
            'FIRST_POST' => '',
            'COMMENT_URL' => placeholder_url(),
        ));

        return array(
            lorem_globalise(do_lorem_template('BLOCK_MAIN_CONTACT_US', array(
                'BLOCK_ID' => lorem_word(),
                'COMMENT_DETAILS' => $comment_details,
                'TYPE' => placeholder_id(),
            )), null, '', true)
        );
    }

    /**
     * Uninstall default content.
     */
    public function uninstall_test_content()
    {
        require_code('tickets');
        require_code('tickets2');
        require_lang('tickets');

        $to_delete = $GLOBALS['SITE_DB']->query_select('ticket_types', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('ticket_type_name') => lorem_phrase()));
        foreach ($to_delete as $record) {
            delete_ticket_type($record['id']);
        }

        // Ticket deletion will be via different hook (as it's a topic); not so important to clean this up either
    }

    /**
     * Install default content.
     */
    public function install_test_content()
    {
        require_code('tickets');
        require_code('tickets2');
        require_lang('tickets');

        $ticket_type_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ticket_types', 'MIN(id)');
        if ($ticket_type_id === null) {
            $ticket_type_id = add_ticket_type(lorem_phrase());
        }

        set_mass_import_mode(false); // Needed for $update_caching

        $ticket_id = uniqid('', true);
        ticket_add_post($ticket_id, $ticket_type_id, lorem_phrase(), lorem_chunk(), false, get_member());

        set_mass_import_mode(true);
    }
}
