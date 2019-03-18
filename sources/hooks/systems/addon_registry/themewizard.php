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
 * @package    themewizard
 */

/**
 * Hook class.
 */
class Hook_addon_registry_themewizard
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
        return 'Automatically generate your own colour schemes using the default theme as a base. Uses the sophisticated chromagraphic equations built into Composr.';
    }

    /**
     * Get a list of tutorials that apply to this addon
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
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/style/themes/themewizard.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/style/themes/logowizard.png',
            'themes/default/images/icons/24x24/menu/adminzone/style/themes/themewizard.png',
            'themes/default/images/icons/48x48/menu/adminzone/style/themes/logowizard.png',
            'themes/default/images/icons/48x48/menu/adminzone/style/themes/themewizard.png',
            'sources/hooks/systems/commandr_commands/themewizard_find_color.php',
            'sources/hooks/systems/commandr_commands/themewizard_compute_equation.php',
            'sources/hooks/modules/admin_themewizard/.htaccess',
            'sources_custom/hooks/modules/admin_themewizard/.htaccess',
            'sources/hooks/systems/snippets/themewizard_equation.php',
            'sources/hooks/modules/admin_themewizard/index.html',
            'sources_custom/hooks/modules/admin_themewizard/index.html',
            'sources/hooks/systems/addon_registry/themewizard.php',
            'sources/themewizard.php',
            'adminzone/pages/modules/admin_themewizard.php',
            'themes/default/templates/THEMEWIZARD_2_SCREEN.tpl',
            'themes/default/templates/THEMEWIZARD_2_PREVIEW.tpl',
            'adminzone/themewizard.php',
            'sources/hooks/systems/page_groupings/themewizard.php',
            'themes/default/templates/LOGOWIZARD_2.tpl',
            'adminzone/logowizard.php',
            'themes/default/images/logo/index.html',
            'themes/default/images/logo/default_logos/index.html',
            'themes/default/images/logo/default_logos/logo1.png',
            'themes/default/images/logo/default_logos/logo2.png',
            'themes/default/images/logo/default_logos/logo3.png',
            'themes/default/images/logo/default_logos/logo4.png',
            'themes/default/images/logo/default_logos/logo5.png',
            'themes/default/images/logo/default_logos/logo6.png',
            'themes/default/images/logo/default_logos/logo7.png',
            'themes/default/images/logo/default_logos/logo8.png',
            'themes/default/images/logo/default_logos/logo9.png',
            'themes/default/images/logo/default_logos/logo10.png',
            'themes/default/images/logo/default_logos/logo11.png',
            'themes/default/images/logo/default_logos/logo12.png',
            'themes/default/images/logo/default_backgrounds/index.html',
            'themes/default/images/logo/default_backgrounds/banner1.png',
            'themes/default/images/logo/default_backgrounds/banner2.png',
            'themes/default/images/logo/default_backgrounds/banner3A.png',
            'themes/default/images/logo/default_backgrounds/banner3B.png',
            'themes/default/images/logo/default_backgrounds/banner3C.png',
            'themes/default/images/logo/default_backgrounds/banner4.png',
            'themes/default/images/logo/default_backgrounds/banner5.png',
            'themes/default/images/logo/default_backgrounds/banner6.png',
            'themes/default/images/logo/default_backgrounds/banner7A.png',
            'themes/default/images/logo/default_backgrounds/banner7B.png',
            'themes/default/images/logo/default_backgrounds/banner8A.png',
            'themes/default/images/logo/default_backgrounds/banner8B.png',
            'themes/default/images/logo/default_backgrounds/banner9.png',
            'themes/default/images/logo/default_backgrounds/banner10.png',
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
            'templates/THEMEWIZARD_2_PREVIEW.tpl' => 'administrative__themewizard_2_preview',
            'templates/THEMEWIZARD_2_SCREEN.tpl' => 'administrative__themewizard_2_screen',
            'templates/LOGOWIZARD_2.tpl' => 'administrative__logowizard_2'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__themewizard_2_preview()
    {
        require_lang('themes');

        $content = do_lorem_template('THEMEWIZARD_2_PREVIEW');

        return array(
            lorem_globalise($content, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__themewizard_2_screen()
    {
        require_lang('themes');

        return array(
            lorem_globalise(do_lorem_template('THEMEWIZARD_2_SCREEN', array(
                'SOURCE_THEME' => 'default',
                'ALGORITHM' => 'equations',
                'RED' => placeholder_id(),
                'GREEN' => placeholder_id(),
                'BLUE' => placeholder_id(),
                'SEED' => lorem_word(),
                'DARK' => lorem_word_2(),
                'DOMINANT' => lorem_word(),
                'LD' => lorem_phrase(),
                'TITLE' => lorem_title(),
                'CHANGE_URL' => placeholder_url(),
                'STAGE3_URL' => placeholder_url(),
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
    public function tpl_preview__administrative__logowizard_2()
    {
        require_lang('themes');

        require_code('fonts');

        $preview = do_lorem_template('LOGOWIZARD_2', array(
            'NAME' => lorem_phrase(),
            'LOGO_THEME_IMAGE' => 'logo/default_logos/1',
            'BACKGROUND_THEME_IMAGE' => 'logo/default_backgrounds/1',
            'THEME' => lorem_phrase(),
            'FONT' => find_default_font(),
        ));

        return array(
            lorem_globalise(do_lorem_template('CONFIRM_SCREEN', array(
                'URL' => placeholder_url(),
                'BACK_URL' => placeholder_url(),
                'PREVIEW' => $preview,
                'FIELDS' => '',
                'TITLE' => lorem_title(),
            )), null, '', true)
        );
    }
}
