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
 * @package    cns_warnings
 */

/**
 * Hook class.
 */
class Hook_addon_registry_cns_warnings
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
        return 'Member warnings and punishment.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_censor',
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
        return 'themes/default/images/icons/menu/social/warnings.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/social/warnings.svg',
            'themes/default/images/icons/links/warning_add.svg',
            'themes/default/images/icons/admin/warn.svg',
            'sources/hooks/systems/addon_registry/cns_warnings.php',
            'site/pages/modules/warnings.php',
            'themes/default/templates/CNS_SAVED_WARNING.tpl',
            'themes/default/templates/CNS_WARNING_HISTORY_SCREEN.tpl',
            'lang/EN/cns_warnings.ini',
            'site/warnings_browse.php',
            'sources/hooks/systems/profiles_tabs/warnings.php',
            'themes/default/templates/CNS_MEMBER_PROFILE_WARNINGS.tpl',
            'themes/default/templates/CNS_WARN_SPAM_URLS.tpl',
            'sources/hooks/systems/commandr_fs_extended_member/warnings.php',
            'themes/default/javascript/cns_warnings.js',
            'sources/hooks/systems/actionlog/cns_warnings.php',
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
            'templates/CNS_SAVED_WARNING.tpl' => 'cns_saved_warning',
            'templates/CNS_WARNING_HISTORY_SCREEN.tpl' => 'administrative__cns_warning_history_screen',
            'templates/CNS_MEMBER_PROFILE_WARNINGS.tpl' => 'cns_member_profile_warnings',
            'templates/CNS_WARN_SPAM_URLS.tpl' => 'cns_warn_spam_urls',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__cns_member_profile_warnings()
    {
        $tab_content = do_lorem_template('CNS_MEMBER_PROFILE_WARNINGS', array(
            'MEMBER_ID' => placeholder_id(),
            'WARNINGS' => lorem_phrase(),
        ));
        return array(
            lorem_globalise($tab_content, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__cns_saved_warning()
    {
        require_css('cns');

        return array(
            lorem_globalise(do_lorem_template('CNS_SAVED_WARNING', array(
                'MESSAGE' => lorem_phrase(),
                'MESSAGE_HTML' => lorem_phrase(),
                'EXPLANATION' => lorem_phrase(),
                'TITLE' => lorem_word(),
                'DELETE_LINK' => placeholder_link(),
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
    public function tpl_preview__administrative__cns_warning_history_screen()
    {
        require_lang('cns');
        require_css('cns');

        return array(
            lorem_globalise(do_lorem_template('CNS_WARNING_HISTORY_SCREEN', array(
                'TITLE' => lorem_title(),
                'MEMBER_ID' => placeholder_id(),
                'EDIT_PROFILE_URL' => placeholder_url(),
                'VIEW_PROFILE_URL' => placeholder_url(),
                'ADD_WARNING_URL' => placeholder_url(),
                'RESULTS_TABLE' => placeholder_table(),
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
    public function tpl_preview__cns_warn_spam_urls()
    {
        $spam_urls = array(
            array(
                'DOMAIN' => 'example.com',
                'URLS' => array(
                    array('I' => 0, 'URL' => 'http://example.com/'),
                    array('I' => 1, 'URL' => 'http://example.com/test'),
                ),
                'POSTS' => array(
                    array('I' => 0, 'POST_TITLE' => lorem_phrase(), 'POST' => lorem_paragraph()),
                    array('I' => 1, 'POST_TITLE' => lorem_phrase(), 'POST' => lorem_paragraph()),
                ),
            ),
        );

        return array(
            lorem_globalise(do_lorem_template('CNS_WARN_SPAM_URLS', array(
                'USERNAME' => lorem_phrase(),
                'SPAM_URLS' => $spam_urls,
            )), null, '', true)
        );
    }
}
