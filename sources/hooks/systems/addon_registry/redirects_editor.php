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
 * @package    redirects_editor
 */

/**
 * Hook class.
 */
class Hook_addon_registry_redirects_editor
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
        return 'Manage redirects between pages.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_subcom',
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
            'previously_in_addon' => array('core_redirects_editor'),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/structure/redirects.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/structure/redirects.png',
            'themes/default/images/icons/48x48/menu/adminzone/structure/redirects.png',
            'sources/hooks/systems/addon_registry/redirects_editor.php',
            'sources/hooks/systems/commandr_fs_extended_config/redirects.php',
            'themes/default/templates/REDIRECTE_TABLE_SCREEN.tpl',
            'themes/default/templates/REDIRECTE_TABLE_REDIRECT.tpl',
            'adminzone/pages/modules/admin_redirects.php',
            'lang/EN/redirects.ini',
            'themes/default/css/redirects_editor.css',
            'sources/hooks/systems/actionlog/redirects_editor.php',
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
            'templates/REDIRECTE_TABLE_REDIRECT.tpl' => 'administrative__redirecte_table_screen',
            'templates/REDIRECTE_TABLE_SCREEN.tpl' => 'administrative__redirecte_table_screen'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__redirecte_table_screen()
    {
        require_javascript('ajax');

        $fields = new Tempcode();
        foreach (placeholder_array() as $i => $row) {
            $fields->attach(do_lorem_template('REDIRECTE_TABLE_REDIRECT', array(
                'I' => strval($i),
                'TO_ZONES' => placeholder_options(),
                'FROM_ZONES' => placeholder_options(),
                'FROM_PAGE' => lorem_word(),
                'TO_PAGE' => lorem_word_2(),
                'TICKED' => true,
                'NAME' => 'is_transparent_' . strval($i),
            )));
        }

        $new = do_lorem_template('REDIRECTE_TABLE_REDIRECT', array(
            'I' => 'new',
            'TO_ZONES' => placeholder_options(),
            'FROM_ZONES' => placeholder_options(),
            'FROM_PAGE' => '',
            'TO_PAGE' => '',
            'TICKED' => false,
            'NAME' => 'is_transparent_new',
        ));

        $out = do_lorem_template('REDIRECTE_TABLE_SCREEN', array(
            'NOTES' => '',
            'PING_URL' => placeholder_url(),
            'WARNING_DETAILS' => '',
            'TITLE' => lorem_title(),
            'FIELDS' => $fields,
            'NEW' => $new,
            'URL' => placeholder_url(),
        ));

        return array(
            lorem_globalise($out, null, '', true)
        );
    }
}
