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
 * @package    securitylogging
 */

/**
 * Hook class.
 */
class Hook_addon_registry_securitylogging
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Log/display security alerts.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_security',
            'tut_trace',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
            'previously_in_addon' => array('core_securitylogging'),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/audit/security_log.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/audit/security_log.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/security_log.png',
            'themes/default/images/icons/24x24/menu/adminzone/tools/users/investigate_user.png',
            'themes/default/images/icons/48x48/menu/adminzone/tools/users/investigate_user.png',
            'themes/default/images/icons/24x24/menu/adminzone/security/ip_ban.png',
            'themes/default/images/icons/48x48/menu/adminzone/security/ip_ban.png',
            'sources/hooks/systems/realtime_rain/security.php',
            'sources/hooks/systems/addon_registry/securitylogging.php',
            'themes/default/templates/SECURITY_SCREEN.tpl',
            'themes/default/templates/SECURITY_ALERT_SCREEN.tpl',
            'adminzone/pages/modules/admin_security.php',
            'themes/default/text/HACK_ATTEMPT_MAIL.txt',
            'adminzone/pages/modules/admin_ip_ban.php',
            'lang/EN/lookup.ini',
            'lang/EN/security.ini',
            'lang/EN/submitban.ini',
            'adminzone/pages/modules/admin_lookup.php',
            'sources/lookup.php',
            'sources/hooks/systems/commandr_fs_extended_member/banned_from_submitting.php',
            'sources/hooks/systems/commandr_fs_extended_config/ip_banned.php',
            'sources/hooks/systems/commandr_fs_extended_config/ip_unbannable.php',
            'sources/hooks/systems/actionlog/securitylogging.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/SECURITY_SCREEN.tpl' => 'administrative__security_screen',
            'templates/SECURITY_ALERT_SCREEN.tpl' => 'administrative__security_alert_screen',
            'text/HACK_ATTEMPT_MAIL.txt' => 'administrative__hack_attempt_mail',
            'templates/IP_BAN_SCREEN.tpl' => 'ip_ban_screen',
            'templates/LOOKUP_IP_LIST_ENTRY.tpl' => 'administrative__lookup_screen',
            'templates/LOOKUP_IP_LIST_GROUP.tpl' => 'administrative__lookup_screen',
            'templates/LOOKUP_SCREEN.tpl' => 'administrative__lookup_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ip_ban_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('IP_BAN_SCREEN', array(
                'PING_URL' => placeholder_url(),
                'WARNING_DETAILS' => '',
                'TITLE' => lorem_title(),
                'BANS' => placeholder_ip(),
                'UNBANNABLE' => placeholder_ip(),
                'LOCKED_BANS' => placeholder_ip(),
                'URL' => placeholder_url(),
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
    public function tpl_preview__administrative__hack_attempt_mail()
    {
        return array(
            lorem_globalise(do_lorem_template('HACK_ATTEMPT_MAIL', array(
                'STACK_TRACE' => lorem_phrase(),
                'USER_AGENT' => lorem_phrase(),
                'REFERER' => lorem_phrase(),
                'USER_OS' => lorem_phrase(),
                'REASON' => lorem_phrase(),
                'IP' => placeholder_ip(),
                'ID' => placeholder_id(),
                'USERNAME' => lorem_word_html(),
                'TIME_RAW' => placeholder_date_raw(),
                'TIME' => placeholder_date(),
                'URL' => placeholder_url(),
                'POST' => lorem_phrase(),
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
    public function tpl_preview__administrative__security_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('SECURITY_SCREEN', array(
                'TITLE' => lorem_title(),
                'FAILED_LOGINS' => placeholder_table(),
                'NUM_FAILED_LOGINS' => placeholder_number(),
                'ALERTS' => lorem_phrase(),
                'NUM_ALERTS' => placeholder_number(),
                'URL' => placeholder_url(),
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
    public function tpl_preview__administrative__security_alert_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('SECURITY_ALERT_SCREEN', array(
                'TITLE' => lorem_title(),
                'USER_AGENT' => lorem_phrase(),
                'REFERER' => lorem_phrase(),
                'USER_OS' => lorem_phrase(),
                'REASON' => lorem_phrase(),
                'IP' => lorem_phrase(),
                'USERNAME' => lorem_word_html(),
                'POST' => lorem_phrase(),
                'URL' => placeholder_url(),
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
    public function tpl_preview__administrative__lookup_screen()
    {
        $inner_ip_list = new Tempcode();
        foreach (placeholder_array() as $value) {
            $inner_ip_list->attach(do_lorem_template('LOOKUP_IP_LIST_ENTRY', array(
                'LOOKUP_URL' => placeholder_url(),
                'DATE' => placeholder_date(),
                '_DATE' => placeholder_date(),
                'IP' => placeholder_ip(),
                'BANNED' => do_lang_tempcode('YES'),
            )));
        }

        $group = do_lorem_template('LOOKUP_IP_LIST_GROUP', array(
            'BANNED' => do_lang_tempcode('YES'),
            'MASK' => placeholder_ip(),
            'GROUP' => $inner_ip_list,
            'OPEN_DEFAULT' => true,
        ));
        return array(
            lorem_globalise(do_lorem_template('LOOKUP_SCREEN', array(
                'TITLE' => lorem_title(),
                'ALERTS' => lorem_phrase(),
                'STATS' => lorem_phrase(),
                'IP_LIST' => $group,
                'IP_BANNED' => lorem_phrase(),
                'SUBMITTER_BANNED' => lorem_phrase(),
                'MEMBER_BANNED' => lorem_phrase(),
                'ID' => placeholder_id(),
                'IP' => placeholder_ip(),
                'NAME' => lorem_word(),
                'SEARCH_URL' => placeholder_url(),
                'AUTHOR_URL' => placeholder_url(),
                'POINTS_URL' => placeholder_url(),
                'PROFILE_URL' => placeholder_url(),
                'ACTIONLOG_URL' => placeholder_url(),
            )), null, '', true)
        );
    }
}
