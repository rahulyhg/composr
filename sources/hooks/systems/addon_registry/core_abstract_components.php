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
 * @package    core_abstract_components
 */

/**
 * Hook class.
 */
class Hook_addon_registry_core_abstract_components
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
        return 'Core rendering functionality.';
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
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/addon_registry/core_abstract_components.php',
            'themes/default/templates/CROP_TEXT_MOUSE_OVER.tpl',
            'themes/default/templates/CROP_TEXT_MOUSE_OVER_INLINE.tpl',
            'themes/default/templates/IMG_THUMB.tpl',
            'themes/default/templates/POST.tpl',
            'themes/default/templates/POST_CHILD_LOAD_LINK.tpl',
            'themes/default/templates/BUTTON_SCREEN.tpl',
            'themes/default/templates/BUTTON_SCREEN_ITEM.tpl',
            'themes/default/templates/STANDARDBOX_default.tpl',
            'themes/default/templates/STANDARDBOX_accordion.tpl',
            'themes/default/templates/HANDLE_CONFLICT_RESOLUTION.tpl',
            'themes/default/templates/FRACTIONAL_EDIT.tpl',
            'themes/default/javascript/fractional_edit.js',
            'data/fractional_edit.php',
            'data/edit_ping.php',
            'data/change_detection.php',
            'themes/default/templates/STAFF_ACTIONS.tpl',
            'sources/hooks/systems/change_detection/.htaccess',
            'sources_custom/hooks/systems/change_detection/.htaccess',
            'sources/hooks/systems/change_detection/index.html',
            'sources_custom/hooks/systems/change_detection/index.html',
            'themes/default/javascript/core_abstract_components.js',
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
            'templates/BUTTON_SCREEN_ITEM.tpl' => 'button_screen_item',
            'templates/FRACTIONAL_EDIT.tpl' => 'administrative__fractional_edit',
            'templates/CROP_TEXT_MOUSE_OVER_INLINE.tpl' => 'crop_text_mouse_over_inline',
            'templates/IMG_THUMB.tpl' => 'img_thumb',
            'templates/CROP_TEXT_MOUSE_OVER.tpl' => 'crop_text_mouse_over',
            'templates/BUTTON_SCREEN.tpl' => 'button_screen',
            'templates/STANDARDBOX_default.tpl' => 'standardbox_default',
            'templates/STANDARDBOX_accordion.tpl' => 'standardbox_accordion',
            'templates/HANDLE_CONFLICT_RESOLUTION.tpl' => 'administrative__handle_conflict_resolution',
            'templates/STAFF_ACTIONS.tpl' => 'staff_actions',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__staff_actions()
    {
        return array(
            lorem_globalise(do_lorem_template('STAFF_ACTIONS', array(
                '1_TITLE' => lorem_phrase(),
                '1_URL' => placeholder_url(),
                '2_TITLE' => lorem_phrase(),
                '2_URL' => placeholder_url(),
                '3_TITLE' => lorem_phrase(),
                '3_URL' => placeholder_url(),
                '4_TITLE' => lorem_phrase(),
                '4_URL' => placeholder_url(),
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
    public function tpl_preview__button_screen_item()
    {
        return array(
            lorem_globalise(do_lorem_template('BUTTON_SCREEN_ITEM', array(
                'REL' => lorem_phrase(),
                'IMMEDIATE' => lorem_phrase(),
                'URL' => placeholder_url(),
                'TITLE' => lorem_word(),
                'FULL_TITLE' => lorem_phrase(),
                'IMG' => 'buttons__edit',
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
    public function tpl_preview__administrative__fractional_edit()
    {
        return array(
            lorem_globalise(do_lorem_template('FRACTIONAL_EDIT', array(
                'VALUE' => lorem_phrase(),
                'URL' => placeholder_url(),
                'EDIT_TEXT' => lorem_sentence_html(),
                'EDIT_PARAM_NAME' => lorem_word_html(),
                'EXPLICIT_EDITING_LINKS' => true,
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
    public function tpl_preview__crop_text_mouse_over_inline()
    {
        return array(
            lorem_globalise(do_lorem_template('CROP_TEXT_MOUSE_OVER_INLINE', array(
                'TEXT_SMALL' => lorem_sentence_html(),
                'TEXT_LARGE' => lorem_sentence_html(),
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
    public function tpl_preview__img_thumb()
    {
        return array(
            lorem_globalise(do_lorem_template('IMG_THUMB', array(
                'JS_TOOLTIP' => lorem_phrase(),
                'CAPTION' => lorem_phrase(),
                'URL' => placeholder_image_url(),
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
    public function tpl_preview__crop_text_mouse_over()
    {
        return array(
            lorem_globalise(do_lorem_template('CROP_TEXT_MOUSE_OVER', array(
                'TEXT_LARGE' => lorem_phrase(),
                'TEXT_SMALL' => lorem_phrase(),
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
    public function tpl_preview__button_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('BUTTON_SCREEN', array(
                'IMMEDIATE' => true,
                'URL' => placeholder_url(),
                'TITLE' => lorem_word(),
                'IMG' => 'buttons__proceed',
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
    public function tpl_preview__standardbox_default()
    {
        return $this->_tpl_preview__standardbox('default');
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__standardbox_accordion()
    {
        return $this->_tpl_preview__standardbox('accordion');
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @param  string $type View type.
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function _tpl_preview__standardbox($type)
    {
        $links = array();
        foreach (placeholder_array() as $k => $v) {
            $links[] = placeholder_link();
        }

        $meta = array();
        foreach (placeholder_array() as $k => $v) {
            $meta[] = array(
                'KEY' => strval($k),
                'VALUE' => $v,
            );
        }

        $boxes = new Tempcode();
        $box = do_lorem_template('STANDARDBOX_' . $type, array(
            'CONTENT' => lorem_sentence(),
            'LINKS' => $links,
            'META' => $meta,
            'OPTIONS' => placeholder_array(),
            'TITLE' => lorem_phrase(),
            'TOP_LINKS' => placeholder_link(),
            'WIDTH' => '',
        ));
        $boxes->attach($box);
        $box = do_lorem_template('STANDARDBOX_' . $type, array(
            'CONTENT' => lorem_sentence(),
            'LINKS' => $links,
            'META' => $meta,
            'OPTIONS' => placeholder_array(),
            'TITLE' => '',
            'TOP_LINKS' => placeholder_link(),
            'WIDTH' => '',
        ));
        $boxes->attach($box);

        return array(
            lorem_globalise($boxes, null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__handle_conflict_resolution()
    {
        return array(
            lorem_globalise(do_lorem_template('HANDLE_CONFLICT_RESOLUTION', array()))
        );
    }
}
