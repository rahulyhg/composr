<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_primary_layout
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_primary_layout
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
        return 'Core rendering functionality.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_themes',
            'tut_designer_themes',
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
        return 'themes/default/images/icons/admin/component.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/addon_registry/core_primary_layout.php',
            'themes/default/templates/MESSAGE.tpl',
            'themes/default/css/helper_panel.css',
            'themes/default/css/messages.css',
            'themes/default/templates/GLOBAL_HTML_WRAP.tpl',
            'themes/default/templates/GLOBAL_HELPER_PANEL.tpl',
            'themes/default/templates/CLOSED_SITE.tpl',
            'themes/default/templates/SCREEN_TITLE.tpl',
            'themes/default/templates/MAIL.tpl',
            'themes/default/text/MAIL.txt',
            'themes/default/text/MAIL_SUBJECT.txt',
            'themes/default/templates/BREADCRUMB_SEPARATOR.tpl',
            'themes/default/templates/BREADCRUMB_LONE_WRAP.tpl',
            'themes/default/templates/BREADCRUMB_LINK_WRAP.tpl',
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
            'templates/BREADCRUMB_SEPARATOR.tpl' => 'breadcrumb',
            'templates/BREADCRUMB_LONE_WRAP.tpl' => 'breadcrumb',
            'templates/BREADCRUMB_LINK_WRAP.tpl' => 'breadcrumb',
            'templates/CLOSED_SITE.tpl' => 'closed_site',
            'templates/MESSAGE.tpl' => 'message',
            'text/MAIL_SUBJECT.txt' => 'mail_subject',
            'templates/MAIL.tpl' => 'mail',
            'templates/GLOBAL_HTML_WRAP.tpl' => 'global_html_wrap',
            'templates/GLOBAL_HELPER_PANEL.tpl' => 'global_html_wrap',
            'templates/SCREEN_TITLE.tpl' => 'screen_title',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__breadcrumb()
    {
        $out = new Tempcode();
        $out->attach(do_lorem_template('BREADCRUMB_LINK_WRAP', array('URL' => placeholder_url(), 'TOOLTIP' => '', 'LABEL' => lorem_phrase())));
        $out->attach(do_lorem_template('BREADCRUMB_SEPARATOR', array()));
        $out->attach(do_lorem_template('BREADCRUMB_LONE_WRAP', array('LABEL' => lorem_phrase())));
        return array(
            lorem_globalise($out, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__closed_site()
    {
        return array(
            lorem_globalise(do_lorem_template('CLOSED_SITE', array(
                'CLOSED' => lorem_phrase(),
                'LOGIN_URL' => placeholder_url(),
                'JOIN_URL' => placeholder_url(),
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
    public function tpl_preview__message()
    {
        return array(
            lorem_globalise(do_lorem_template('MESSAGE', array(
                'TYPE' => placeholder_img_code('icons/status'),
                'MESSAGE' => lorem_phrase(),
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
    public function tpl_preview__mail_subject()
    {
        return array(
            lorem_globalise(do_lorem_template('MAIL_SUBJECT', array(
                'SUBJECT_LINE' => lorem_word(),
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
    public function tpl_preview__mail()
    {
        return array(
            lorem_globalise(do_lorem_template('MAIL', array(
                'CSS' => '',
                'LOGOURL' => placeholder_image_url(),
                'LANG' => fallback_lang(),
                'TITLE' => lorem_phrase(),
                'CONTENT' => lorem_paragraph(),
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
    public function tpl_preview__global_html_wrap()
    {
        $out = do_lorem_template('GLOBAL_HTML_WRAP', array(
            'MIDDLE' => lorem_paragraph_html(),
        ));

        return array(
            $out
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__screen_title()
    {
        $awards = null;
        if (addon_installed('awards')) {
            require_lang('awards');
            $awards = array(
                array(
                    'AWARD_TYPE' => lorem_title(),
                    'AWARD_TIMESTAMP' => placeholder_date(),
                )
            );
        }
        return array(
            lorem_globalise(do_lorem_template('SCREEN_TITLE', array(
                'TITLE' => lorem_phrase(),
                'AWARDS' => $awards,
            )), null, '', true)
        );
    }
}
