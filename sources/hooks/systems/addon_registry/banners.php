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
 * @package    banners
 */

/**
 * Hook class.
 */
class Hook_addon_registry_banners
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
        return 'An advanced banner system, with support for multiple banner rotations, commercial banner campaigns, and webring-style systems. Support for graphical, text, and HTML banners. Hotword activation support.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_banners',
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
        return 'themes/default/images/icons/menu/cms/banners.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/cms/banners.svg',
            'themes/default/images/icons_monochrome/menu/cms/banners.svg',
            'sources/hooks/systems/config/enable_edit_banner_buttons.php',
            'themes/default/css/banners.css',
            'sources/hooks/systems/snippets/exists_banner.php',
            'sources/hooks/systems/snippets/exists_banner_type.php',
            'sources/hooks/systems/config/admin_banners.php',
            'sources/hooks/systems/config/banner_autosize.php',
            'sources/hooks/systems/config/points_ADD_BANNER.php',
            'sources/hooks/systems/config/use_banner_permissions.php',
            'sources/hooks/systems/realtime_rain/banners.php',
            'adminzone/pages/modules/admin_banners.php',
            'uploads/banners/.htaccess',
            'themes/default/templates/BANNER_PREVIEW.tpl',
            'themes/default/templates/BANNERS_NONE.tpl',
            'sources/hooks/systems/preview/banner.php',
            'sources/hooks/modules/admin_import_types/banners.php',
            'sources/hooks/systems/addon_registry/banners.php',
            'themes/default/templates/BANNER_TEXT.tpl',
            'themes/default/templates/BANNER_VIEW_SCREEN.tpl',
            'themes/default/templates/BANNER_IFRAME.tpl',
            'themes/default/templates/BANNER_IMAGE.tpl',
            'themes/default/templates/BANNER_SHOW_CODE.tpl',
            'themes/default/templates/BANNER_ADDED_SCREEN.tpl',
            'themes/default/templates/BLOCK_MAIN_TOP_SITES.tpl',
            'themes/default/templates/BLOCK_MAIN_BANNER_WAVE.tpl',
            'themes/default/templates/BLOCK_MAIN_BANNER_WAVE_BWRAP.tpl',
            'sources/hooks/systems/sitemap/banner.php',
            'banner.php',
            'uploads/banners/index.html',
            'cms/pages/modules/cms_banners.php',
            'lang/EN/banners.ini',
            'site/pages/modules/banners.php',
            'sources/banners.php',
            'sources/banners2.php',
            'sources/blocks/main_top_sites.php',
            'sources/blocks/main_banner_wave.php',
            'sources/hooks/modules/admin_setupwizard/banners.php',
            'sources/hooks/modules/admin_unvalidated/banners.php',
            'sources/hooks/systems/ecommerce/banners.php',
            'sources/hooks/systems/page_groupings/banners.php',
            'sources/hooks/systems/content_meta_aware/banner.php',
            'sources/hooks/systems/content_meta_aware/banner_type.php',
            'sources/hooks/systems/commandr_fs/banners.php',
            'data/images/advertise_here.png',
            'data/images/donate.png',
            'data/images/placeholder_leaderboard.jpg',
            'sources/hooks/systems/block_ui_renderers/banners.php',
            'themes/default/javascript/banners.js',
            'sources/hooks/systems/reorganise_uploads/banners.php',
            'sources/hooks/systems/actionlog/banners.php',
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
            'templates/BANNER_PREVIEW.tpl' => 'banner_preview',
            'templates/BANNER_SHOW_CODE.tpl' => 'banner_show_code',
            'templates/BANNER_ADDED_SCREEN.tpl' => 'administrative__banner_added_screen',
            'templates/BLOCK_MAIN_TOP_SITES.tpl' => 'block_main_top_sites',
            'templates/BLOCK_MAIN_BANNER_WAVE_BWRAP.tpl' => 'block_main_banner_wave',
            'templates/BLOCK_MAIN_BANNER_WAVE.tpl' => 'block_main_banner_wave',
            'templates/BANNERS_NONE.tpl' => 'banners_none',
            'templates/BANNER_IMAGE.tpl' => 'banner_image',
            'templates/BANNER_IFRAME.tpl' => 'banner_iframe',
            'templates/BANNER_TEXT.tpl' => 'banner_text',
            'templates/BANNER_VIEW_SCREEN.tpl' => 'administrative__banner_view_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banner_preview()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_PREVIEW', array(
                'PREVIEW' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banner_show_code()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_SHOW_CODE', array(
                'NAME' => placeholder_random_id(),
                'WIDTH' => placeholder_number(),
                'HEIGHT' => placeholder_number(),
                'TYPE' => lorem_word(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__banner_added_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_ADDED_SCREEN', array(
                'TITLE' => lorem_title(),
                'TEXT' => lorem_sentence_html(),
                'BANNER_CODE' => lorem_phrase(),
                'STATS_URL' => placeholder_url(),
                'DO_NEXT' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_main_top_sites()
    {
        return array(
            lorem_globalise(do_lorem_template('BLOCK_MAIN_TOP_SITES', array(
                'BLOCK_ID' => lorem_word(),
                'TYPE' => lorem_phrase(),
                'BANNERS' => placeholder_array(),
                'SUBMIT_URL' => placeholder_url(),
                'DESCRIPTION' => lorem_word(),
                'BANNER' => lorem_word_2(),
                'HITS_FROM' => placeholder_number(),
                'HITS_TO' => placeholder_number(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_main_banner_wave()
    {
        $banners = new Tempcode();
        $banners->attach(do_lorem_template('BANNER_IMAGE', array(
            'URL' => placeholder_url(),
            'B_TYPE' => lorem_phrase(),
            'WIDTH' => placeholder_number(),
            'HEIGHT' => placeholder_number(),
            'SOURCE' => lorem_phrase(),
            'DEST' => lorem_phrase(),
            'CAPTION' => lorem_phrase(),
            'IMG' => placeholder_image_url(),
        )));
        $banners->attach(do_lorem_template('BANNER_IFRAME', array(
            'B_TYPE' => lorem_phrase(),
            'IMG' => placeholder_image_url(),
            'WIDTH' => placeholder_number(),
            'HEIGHT' => placeholder_number(),
        )));

        $assemble = do_lorem_template('BLOCK_MAIN_BANNER_WAVE_BWRAP', array(
            'EXTRA' => lorem_phrase(),
            'TYPE' => lorem_phrase(),
            'BANNER' => $banners,
            'MORE_COMING' => lorem_phrase(),
            'MAX' => placeholder_number(),
        ));

        return array(
            lorem_globalise(do_lorem_template('BLOCK_MAIN_BANNER_WAVE', array(
                'BLOCK_ID' => lorem_word(),
                'EXTRA' => lorem_phrase(),
                'TYPE' => lorem_phrase(),
                'ASSEMBLE' => $assemble,
                'MAX' => placeholder_number(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banners_none()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNERS_NONE', array(
                'ADD_BANNER_URL' => placeholder_url(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banner_image()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_IMAGE', array(
                'URL' => placeholder_url(),
                'B_TYPE' => lorem_phrase(),
                'WIDTH' => placeholder_number(),
                'HEIGHT' => placeholder_number(),
                'SOURCE' => lorem_phrase(),
                'DEST' => lorem_phrase(),
                'CAPTION' => lorem_phrase(),
                'IMG' => placeholder_image_url(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banner_iframe()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_IFRAME', array(
                'B_TYPE' => lorem_phrase(),
                'IMG' => placeholder_image_url(),
                'WIDTH' => placeholder_number(),
                'HEIGHT' => placeholder_number(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__banner_text()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_TEXT', array(
                'B_TYPE' => lorem_phrase(),
                'TITLE_TEXT' => lorem_phrase(),
                'CAPTION' => lorem_phrase(),
                'SOURCE' => lorem_phrase(),
                'DEST' => lorem_phrase(),
                'URL' => placeholder_url(),
                'FILTERED_URL' => placeholder_url(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declarative.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__banner_view_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('BANNER_VIEW_SCREEN', array(
                'TITLE' => lorem_title(),
                'EDIT_URL' => placeholder_url(),
                'MAP_TABLE' => lorem_phrase(),
                'BANNER' => lorem_phrase(),
                'NAME' => placeholder_id(),
                'RESULTS_TABLE' => placeholder_table(),
                'RESET_URL' => placeholder_url(),
            )), null, '', true)
        );
    }
}
