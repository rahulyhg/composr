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
 * @package    cns_post_templates
 */

/**
 * Hook class.
 */
class Hook_addon_registry_cns_post_templates
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
        return 'Post templates for the Conversr forum.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_support_desk',
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
            'requires' => array(
                'cns_forum',
            ),
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
        return 'themes/default/images/icons/menu/adminzone/structure/forum/post_templates.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/menu/adminzone/structure/forum/post_templates.svg',
            'sources/hooks/systems/resource_meta_aware/post_template.php',
            'sources/hooks/systems/commandr_fs/post_templates.php',
            'sources/hooks/systems/addon_registry/cns_post_templates.php',
            'themes/default/templates/CNS_POST_TEMPLATE_SELECT.tpl',
            'adminzone/pages/modules/admin_cns_post_templates.php',
            'lang/EN/cns_post_templates.ini',
            'themes/default/javascript/cns_post_templates.js',
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
            'templates/CNS_POST_TEMPLATE_SELECT.tpl' => 'cns_post_template_select',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__cns_post_template_select()
    {
        require_lang('cns');
        require_css('cns');

        $list = new Tempcode();
        foreach (placeholder_array() as $key => $value) {
            $list->attach(do_lorem_template('FORM_SCREEN_INPUT_LIST_ENTRY', array(
                'SELECTED' => false,
                'DISABLED' => false,
                'CLASS' => '',
                'NAME' => placeholder_random_id(),
                'TEXT' => lorem_phrase(),
            )));
        }

        $input = do_lorem_template('CNS_POST_TEMPLATE_SELECT', array(
            'TABINDEX' => placeholder_number(),
            'LIST' => $list,
        ));

        $fields = new Tempcode();
        $fields->attach(do_lorem_template('FORM_SCREEN_FIELD', array(
            'REQUIRED' => true,
            'SKIP_LABEL' => false,
            'PRETTY_NAME' => lorem_word(),
            'NAME' => 'post_template',
            'DESCRIPTION' => lorem_sentence_html(),
            'DESCRIPTION_SIDE' => '',
            'INPUT' => $input,
            'COMCODE' => '',
        )));

        return array(
            lorem_globalise(do_lorem_template('FORM_SCREEN', array(
                'SKIP_WEBSTANDARDS' => true,
                'HIDDEN' => '',
                'TITLE' => lorem_title(),
                'URL' => placeholder_url(),
                'FIELDS' => $fields,
                'SUBMIT_ICON' => 'buttons--proceed',
                'SUBMIT_NAME' => lorem_phrase(),
                'TEXT' => lorem_sentence_html(),
            )), null, '', true)
        );
    }

    /**
     * Uninstall default content.
     */
    public function uninstall_test_content()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        require_code('cns_general_action2');

        $to_delete = $GLOBALS['FORUM_DB']->query_select('f_post_templates', array('id'), array('t_title' => lorem_phrase()));
        foreach ($to_delete as $record) {
            cns_delete_post_template($record['id']);
        }
    }

    /**
     * Install default content.
     */
    public function install_test_content()
    {
        if (get_forum_type() != 'cns') {
            return;
        }

        require_code('cns_general_action');

        cns_make_post_template(lorem_phrase(), lorem_chunk(), '*', 0);
    }
}
