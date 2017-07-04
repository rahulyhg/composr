<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Try and make an action log entry into a proper link.
 *
 * @param  ID_TEXT $type Action type
 * @param  string $a First parameter
 * @param  string $b Second parameter
 * @param  Tempcode $_a First parameter (cropped)
 * @param  Tempcode $_b Second parameter (cropped)
 * @return ?array Pair: first parameter as possible link, second parameter as possible link (null: could not construct a nice link)
 */
function actionlog_linkage($type, $a, $b, $_a, $_b)
{
    $type_str = do_lang($type, $a, $b, null, null, false);
    if ($type_str === null) {
        $type_str = $type;
    }

    // TODO: This will be replaced later with a more thorough system #115 on tracker
    if (($type == 'EDIT_TEMPLATE') && (strpos($a, ',') === false)) {
        if ($b == '') {
            $b = 'default';
        }
        $tmp_url = build_url(array('page' => 'admin_themes', 'type' => 'edit_templates', 'theme' => $b, 'f0file' => $a), get_module_zone('admin_themes'));
        $a = basename($a, '.tpl');
        require_code('templates_interfaces');
        $_a = tpl_crop_text_mouse_over($a, 14);
        $_a = hyperlink($tmp_url, $_a, false, false, $type_str);
        return array($_a, $_b);
    }
    if ($type == 'COMCODE_PAGE_EDIT') {
        $tmp_url = build_url(array('page' => 'cms_comcode_pages', 'type' => '_edit', 'page_link' => $b . ':' . $a), get_module_zone('cms_comcode_pages'));
        $_a = hyperlink($tmp_url, $_a, false, false, $type_str);
        return array($_a, $_b);
    }
    if ($type == 'ADD_CATALOGUE_ENTRY' || $type == 'EDIT_CATALOGUE_ENTRY') {
        $tmp_url = build_url(array('page' => 'catalogues', 'type' => 'entry', 'id' => $a), get_module_zone('catalogues'));
        $_b = hyperlink($tmp_url, ($b == '') ? $_a : $_b, false, false, $type_str);
        return array($_a, $_b);
    }
    if (($type == 'ADD_CATALOGUE_CATEGORY' || $type == 'EDIT_CATALOGUE_CATEGORY') && ($b != '')) {
        $tmp_url = build_url(array('page' => 'catalogues', 'type' => 'category', 'id' => (!is_numeric($a)) ? $b : $a), get_module_zone('catalogues'));
        $_b = hyperlink($tmp_url, $_b, false, false, $type_str);
        return array($_a, $_b);
    }

    return null; // Could not get a match
}
