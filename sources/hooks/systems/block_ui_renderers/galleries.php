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
 * @package    galleries
 */

/**
 * Hook class.
 */
class Hook_block_ui_renderers_galleries
{
    /**
     * See if a particular block parameter's UI input can be rendered by this.
     *
     * @param  ID_TEXT $block The block
     * @param  ID_TEXT $parameter The parameter of the block
     * @param  boolean $has_default Whether there is a default value for the field, due to this being an edit
     * @param  string $default Default value for field
     * @param  Tempcode $description Field description
     * @return ?Tempcode Rendered field (null: not handled)
     */
    public function render_block_ui($block, $parameter, $has_default, $default, $description)
    {
        if (!addon_installed('galleries')) {
            return null;
        }

        if ((($default == '') || (preg_match('#^[' . URL_CONTENT_REGEXP . ']+$#', $default) != 0)) && ($parameter == 'param') && (in_array($block, array('side_galleries', 'main_gallery_embed', 'main_image_fader')))) { // gallery list
            require_code('galleries');
            $list = create_selection_list_gallery_tree($default);
            return form_input_list(titleify($parameter), escape_html($description), $parameter, $list, null, false, false);
        }
        return null;
    }
}
