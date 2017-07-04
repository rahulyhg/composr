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
 * @package    core_themeing
 */

/*EXTRA FUNCTIONS: symlink*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__themes_meta_tree()
{
    if (!defined('TEMPLATE_TREE_NODE__UNKNOWN')) {
        define('TEMPLATE_TREE_NODE__UNKNOWN', 0);
        define('TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE', 1);
        define('TEMPLATE_TREE_NODE__TEMPLATE_PARAMETER', 2);
        define('TEMPLATE_TREE_NODE__TEMPLATE_GUID', 3); // The GUID for a template call. Not quite a part of the tree, but it fits in nicely as it is a template parameter.
        define('TEMPLATE_TREE_NODE__SET', 4);
        define('TEMPLATE_TREE_NODE__BLOCK', 5);
        define('TEMPLATE_TREE_NODE__TRIM', 6);
        define('TEMPLATE_TREE_NODE__PANEL', 7);
        define('TEMPLATE_TREE_NODE__PAGE', 8);
        define('TEMPLATE_TREE_NODE__JS_TEMPCODE', 9);
        define('TEMPLATE_TREE_NODE__CSS_TEMPCODE', 10);
        define('TEMPLATE_TREE_NODE__INCLUDE', 11);
        define('TEMPLATE_TREE_NODE__ATTACHED', 12);
    }
}

/**
 * Save template relationships into the database.
 *
 * @ignore
 */
function _record_templates_used()
{
    global $RECORDED_TEMPLATES_USED;
    $templates_used = array_keys($RECORDED_TEMPLATES_USED);
    sort($templates_used);

    foreach ($templates_used as $i => $rel_a) {
        $has_currently = collapse_1d_complexity('rel_b', $GLOBALS['SITE_DB']->query_select('theme_template_relations', array('rel_b'), array(
            'rel_a' => $rel_a,
        )));
        sort($has_currently);
        $has_currently_flipped = array_flip($has_currently);

        $templates_used_copy = $templates_used;
        unset($templates_used_copy[$i]);

        if ($has_currently != $templates_used_copy) {
            foreach ($templates_used_copy as $rel_b) {
                if (!isset($has_currently_flipped[$rel_b])) {
                    $insert_map = array(
                        'rel_a' => $rel_a,
                        'rel_b' => $rel_b
                    );
                    $GLOBALS['SITE_DB']->query_insert('theme_template_relations', $insert_map, false, true);
                }
            }

            $meta_tree_builder = new Meta_tree_builder();
            $meta_tree_builder->refresh(dirname($rel_a) . '-related', basename($rel_a));
        }
    }
}

/**
 * Save screen template tree into the database.
 *
 * @param  Tempcode $out Tempcode structure
 */
function record_template_tree_used($out)
{
    if (!isset($out->metadata)) {
        return;
    }

    $tree = convert_template_tree_metadata_to_screen_tree($out->metadata);

    $page_link = get_zone_name() . ':' . get_page_name();
    $type = get_param_string('type', null);
    if ($type !== null) {
        $page_link .= ':' . $type;
    }

    $current_json_tree = $GLOBALS['SITE_DB']->query_select_value_if_there('theme_screen_tree', 'json_tree', array(
        'page_link' => $page_link,
    ));

    $new_json_tree = json_encode($tree);

    if ($current_json_tree !== $new_json_tree) {
        if ($current_json_tree !== null) {
            $GLOBALS['SITE_DB']->query_delete('theme_screen_tree', array(
                'page_link' => $page_link,
            ), '', 1);
        }

        if ($tree !== null) {
            $GLOBALS['SITE_DB']->query_insert('theme_screen_tree', array(
                'page_link' => $page_link,
                'json_tree' => $new_json_tree,
            ));

            $meta_tree_builder = new Meta_tree_builder();
            $meta_tree_builder->refresh('screens', $page_link);
        }
    }
}

/**
 * Convert the metadata template tree gathered in the Tempcode engine, to a simplified screen tree.
 *
 * @param  array $metadata Metadata structure (with 'type', 'identifier', and 'children')
 * @return ?array Screen tree structure (null: omitted node)
 */
function convert_template_tree_metadata_to_screen_tree($metadata)
{
    if (!in_array($metadata['type'], array(TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE, TEMPLATE_TREE_NODE__PANEL, TEMPLATE_TREE_NODE__PAGE))) {
        if (count($metadata['children']) == 0) {
            return null;
        }
    }

    // Search for GUIDs and parameter name
    $parameter_as = null;
    $guid_used = null;
    foreach ($metadata['children'] as $_child) {
        if ($_child['type'] == TEMPLATE_TREE_NODE__TEMPLATE_PARAMETER) {
            $parameter_as = $_child['identifier'];
        }
        if ($_child['type'] == TEMPLATE_TREE_NODE__TEMPLATE_GUID) {
            $guid_used = $_child['identifier'];
        }
    }
    $instance_calls = array();
    if ($parameter_as !== null || $guid_used !== null) {
        $instance_calls[] = array(
            'parameter_as' => $parameter_as,
            'guid_used' => $guid_used,
        ); // More will be added to array when equivalent child nodes are merged together
    }

    // Basic settings
    $tree = array(
        'type' => '_other',
        'subdir' => '',
        'name' => '_container',
        'instance_calls' => $instance_calls,
        'children' => array(),
    );

    // Accurate settings
    switch ($metadata['type']) {
        case TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE:
            $tree['type'] = 'template';
            $tree['subdir'] = dirname($metadata['identifier']);
            $tree['name'] = basename($metadata['identifier']);
            break;

        case TEMPLATE_TREE_NODE__PANEL:
        case TEMPLATE_TREE_NODE__PAGE:
            $tree['type'] = 'comcode_page';
            $tree['name'] = $metadata['identifier'];
            break;
    }

    // Children
    $children = array();
    foreach ($metadata['children'] as $_child) {
        $child = convert_template_tree_metadata_to_screen_tree($_child);
        if ($child !== null) {
            if ($child['type'] == '_other') { // We don't actually want these '_other' nodes, so we'll skip to the node's children
                $to_merge = $child['children'];
            } else {
                $to_merge = array($child);
            }

            foreach ($to_merge as $__child) {
                $sz = serialize(array($__child['type'], $__child['subdir'], $__child['name']));
                if (isset($children[$sz])) {
                    $children[$sz]['instance_calls'] = array_unique(array_merge($children[$sz]['instance_calls'], $__child['instance_calls']));
                } else {
                    $children[$sz] = $__child;
                }
            }
        }
    }
    $tree['children'] = array_values($children);

    return $tree;
}

/**
 * Prepare template tree metadata.
 *
 * @param  integer $type Tree node type (a TEMPLATE_TREE_NODE__* constant)
 * @param  mixed $identifier Identifier (Tempcode or string)
 * @param  mixed $children Child nodes (array) or Tempcode node to get children from (Tempcode)
 * @return array Metadata structure
 */
function create_template_tree_metadata($type = 0, $identifier = '', $children = array())
{
    if (is_object($identifier)) {
        $identifier = $identifier->evaluate();
    }

    if (is_object($children)) {
        if (isset($children->metadata)) {
            $children->handle_symbol_preprocessing();

            $children = array($children->metadata);
        } else {
            $children = array();
        }
    }

    return array(
        'type' => $type,
        'identifier' => $identifier,
        'children' => $children,
    );
}

/**
 * Convert a template tree structure into a HTML representation.
 *
 * @param  array $metadata Tempcode metadata node
 * @param  array $collected_templates A map of templates detected will be saved into here
 * @return string HTML representation
 */
function find_template_tree_nice($metadata, &$collected_templates)
{
    $identifier = $metadata['identifier'];
    $children = $metadata['children'];

    // Simplify?
    if (($metadata['type'] != TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE) && (count($metadata['children']) == 1)) {
        return find_template_tree_nice($metadata['children'][0], $collected_templates);
    }

    // Basic node rendering
    switch ($metadata['type']) {
        case TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE: // Full template editing link
            $file = $identifier;
            $codename = basename($identifier, '.tpl');

            $collected_templates[$file] = true;

            // Find GUID
            $guid = mixed();
            foreach ($children as $child) {
                if ($child['type'] == TEMPLATE_TREE_NODE__TEMPLATE_GUID) {
                    $guid = $child['identifier'];
                }
            }

            // Find edit URL
            $edit_url_map = array(
                'page' => 'admin_themes',
                'type' => 'edit_templates',
                'f0file' => $file,
                'f0guid' => $guid,
                'preview_url' => get_self_url(true, false, array('special_page_type' => null)),
                'theme' => $GLOBALS['FORUM_DRIVER']->get_theme(),
            );
            $edit_url = build_url($edit_url_map, 'adminzone');

            // Render
            $source = do_template('TEMPLATE_TREE_ITEM', array(
                '_GUID' => 'be8eb00699631677d459b0f7c5ba60c8',
                'FILE' => $file,
                'EDIT_URL' => $edit_url,
                'CODENAME' => $codename,
                'GUID' => $guid,
            ));
            $out = $source->evaluate();

            break;

        case TEMPLATE_TREE_NODE__SET:
            $out = 'set: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__TEMPLATE_PARAMETER:
            $out = 'parameter: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__BLOCK:
            $out = 'block: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__TRIM:
            $out = 'trim';
            break;

        case TEMPLATE_TREE_NODE__PANEL:
            $out = 'panel: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__PAGE:
            $out = 'page: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__JS_TEMPCODE:
            $out = 'js_files: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__CSS_TEMPCODE:
            $out = 'css_files';
            break;

        case TEMPLATE_TREE_NODE__INCLUDE:
            $out = 'include: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__ATTACHED:
            $out = 'attached';
            break;

        case TEMPLATE_TREE_NODE__TEMPLATE_GUID:
            $out = 'guid: ' . $identifier;
            break;

        case TEMPLATE_TREE_NODE__UNKNOWN:
        case TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE:
            $out = $identifier;
            if ($out == '') {
                $out = '(mixed)';
            }
            break;

        default:
            $out = '(mixed)';
            break;
    }

    // Now for the children...

    // Reduce down unnecessary children under here
    $_children = array();
    foreach ($children as $child) {
        if (
            (count($child['children']) > 0)
            ||
            ($child['type'] == TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE)
        ) {
            $_children[] = $child;
        }
    }
    $children = $_children;

    // Render
    $child_items = array();
    foreach ($children as $child) {
        $child_rendered = find_template_tree_nice($child, $collected_templates);
        if ($child_rendered != '') {
            $_middle = do_template('TEMPLATE_TREE_ITEM_WRAP', array('_GUID' => '59f003e298db3b621132649d2e315f9d', 'CONTENT' => $child_rendered));
            $child_items[$_middle->evaluate()] = true;
        }
    }
    if (($child_items == array()) && ($metadata['type'] != TEMPLATE_TREE_NODE__TEMPLATE_INSTANCE)) {
        return '';
    }
    if ($child_items != array()) {
        $_child_items = '';
        foreach (array_keys($child_items) as $_child_item) {
            $_child_items .= str_replace('__id__', strval(mt_rand(0, mt_getrandmax())), $_child_item);
        }
        $_out = do_template('TEMPLATE_TREE_NODE', array('_GUID' => 'ff937cbe28f1988af9fc7861ef01ffee', 'ITEMS' => $_child_items));
        $out .= $_out->evaluate();
    }

    // Done
    return $out;
}

/**
 * Sitemap node type base class.
 *
 * @package        core_themeing
 */
class Meta_tree_builder
{
    private $addons;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->addons = array_keys(find_all_hooks('systems', 'addon_registry'));

        require_code('files');
        require_code('themes2');

        push_query_limiting(false);
    }

    /**
     * Build out meta-directories. (Sym-links etc).
     *
     * @param  ?string $filter_level_a The first level of filter (null: no filter, full rebuild)
     * @param  ?string $filter_level_b The second level of filter (null: no filter)
     */
    public function refresh($filter_level_a = null, $filter_level_b = null)
    {
        require_code('themes2');
        $themes = array_keys(find_all_themes());
        foreach ($themes as $theme) {
            $this->refresh_for_theme($theme, $filter_level_a, $filter_level_b);
        }
    }

    /**
     * Build out meta-directories for a theme.
     *
     * @param  ID_TEXT $theme The theme
     * @param  ?string $filter_level_a The first level of filter (null: no filter, full rebuild)
     * @param  ?string $filter_level_b The second level of filter (null: no filter)
     */
    public function refresh_for_theme($theme, $filter_level_a = null, $filter_level_b = null)
    {
        $full_rebuild = ($filter_level_a === null);

        $theme_dir = get_custom_file_base() . '/themes/' . $theme;
        if (!file_exists($theme_dir)) {
            mkdir($theme_dir, 0777);
            fix_permissions($theme_dir);
        }
        $meta_dir = $theme_dir . '/_meta_tree';

        if (is_dir($meta_dir)) {
            if ($full_rebuild) {
                deldir_contents($meta_dir);
            }
        } else {
            @mkdir($meta_dir, 0777);
            if (!is_dir($meta_dir)) {
                return; // Can't do it on this server
            }
            fix_permissions($meta_dir);

            $this->put_in_standard_dir_files($meta_dir);
        }

        if ($filter_level_a === null) {
            $meta_dirs_to_build = array(
                'screens',

                'templates',
                'css',
                'javascript',
                'xml',
                'text',

                'templates-related',
                'css-related',
                'javascript-related',
                'xml-related',
                'text-related',
            );
        } else {
            $meta_dirs_to_build = array(
                $filter_level_a,
            );
        }
        foreach ($meta_dirs_to_build as $meta_dir_to_build) {
            $_path = $meta_dir . '/' . $meta_dir_to_build;
            if (!is_dir($_path)) {
                mkdir($_path, 0777);
                fix_permissions($_path);
            }

            switch ($meta_dir_to_build) {
                case 'screens':
                    $this->put_in_screens($_path, $theme, $filter_level_b);
                    break;

                case 'templates':
                case 'css':
                case 'javascript':
                case 'xml':
                case 'text':
                    $this->put_in_addon_tree($_path, $meta_dir_to_build, $theme, $filter_level_b);
                    break;

                case 'templates-related':
                case 'css-related':
                case 'javascript-related':
                case 'xml-related':
                case 'text-related':
                    $this->put_in_addon_tree($_path, preg_replace('#-related$#', '', $meta_dir_to_build), $theme, $filter_level_b, true);
                    break;
            }
        }
    }

    /**
     * Put in an screen template/page hierarchy under a path.
     *
     * @param  PATH $path The path
     * @param  ID_TEXT $theme The theme
     * @param  ?string $filter_level_b The second level of filter (null: no filter)
     */
    private function put_in_screens($path, $theme, $filter_level_b = null)
    {
        if ($filter_level_b === null) {
            $where = array();
        } else {
            $where = array('page_link' => $filter_level_b);
        }

        $screens = $GLOBALS['SITE_DB']->query_select('theme_screen_tree', array('page_link', 'json_tree'), $where);
        foreach ($screens as $screen) {
            $page_link = $screen['page_link'];
            $page_link_parts = explode(':', $page_link);
            $tree = json_decode($screen['json_tree'], true);

            $zone = $page_link_parts[0];
            if (!is_dir(get_custom_file_base() . '/' . $zone) && !is_dir(get_file_base() . '/' . $zone)) {
                continue; // No zone
            }

            if (!empty($page_link_parts[1])) {
                require_code('site');
                $found = _request_page($page_link_parts[1], $zone);
                if ($found === false) {
                    continue; // No page
                }
            }

            // Turn page-link into deep subtree
            $_path = $path;
            foreach ($page_link_parts as $i => $part) {
                if ($part == '') {
                    switch ($i) {
                        case 0:
                            $part = 'root';
                            break;

                        default:
                            $part = 'default';
                            break;
                    }
                }

                $_path .= '/' . urlencode($part);

                if (is_dir($_path)) {
                   deldir_contents($_path);
                   rmdir($_path);
                }
                mkdir($_path, 0777);
                fix_permissions($_path);
            }

            // Create our screen tree under the page-link subtree
            $this->put_in_screen($_path, $tree, $theme);
        }
    }

    /**
     * Put in an screen template/page hierarchy under a path.
     *
     * @param  PATH $path The path
     * @param  array $node The tree node
     * @param  ID_TEXT $theme The theme
     */
    private function put_in_screen($path, $node, $theme)
    {
        // Create directory for this level
        $_path = $path . '/' . urlencode($node['name']);
        mkdir($_path, 0777);
        fix_permissions($_path);

        // Create _self symlink
        switch ($node['type']) {
            case 'comcode_page':
                list($zone, $page) = explode(':', $node['name'], 2);
                list(, , $page_path) = find_comcode_page(get_site_default_lang(), $page, $zone);
                if ($page_path == '') {
                    $page_path = get_file_base() . '/pages/comcode/EN/404.txt';
                }
                $new_symlink = $_path . '/_self.txt';
                @symlink($page_path, $new_symlink);
                fix_permissions($new_symlink);
                break;

            case 'template':
                $template_path = find_template_path($node['name'], $node['subdir'], $theme);
                if ($template_path !== null) {
                    $new_symlink = $_path . '/_self.' . get_file_extension($node['name']);
                    @symlink($template_path, $new_symlink);
                    fix_permissions($new_symlink);
                }
                break;

            default:
                // Nothing to do
        }

        // Create _instance_calls.txt
        $instance_calls = '';
        foreach ($node['instance_calls'] as $instance_call) {
            $instance_call_parts = array();
            if ($instance_call['parameter_as'] !== null) {
                $instance_call_parts[] = '{' . $instance_call['parameter_as'] . '}';
            }
            if ($instance_call['guid_used'] !== null) {
                $instance_call_parts[] = 'GUID=' . $instance_call['guid_used'];
            }
            $instance_calls .= implode(', ', $instance_call_parts) . "\n";
        }
        if ($instance_calls != '') {
            require_code('files');
            cms_file_put_contents_safe($_path . '/_instance_calls.txt', $instance_calls);
            fix_permissions($_path . '/_instance_calls.txt');
        }

        // Process children
        foreach ($node['children'] as $child) {
            $this->put_in_screen($_path, $child, $theme);
        }
    }

    /**
     * Put in an addon hierarchy under a path.
     *
     * @param  PATH $path The path
     * @param  ID_TEXT $subdir The theme subdirectory we're working against
     * @param  ID_TEXT $theme The theme
     * @param  ?string $filter_level_b The second level of filter (null: no filter)
     * @param  boolean $relationships_mode Whether we have an extra level, the relationships mode
     */
    private function put_in_addon_tree($path, $subdir, $theme, $filter_level_b = null, $relationships_mode = false)
    {
        $_all_path = $path . '/_all';
        if (is_dir($_all_path)) {
            if ($filter_level_b === null) {
                deldir_contents($_all_path);
            }
        } else {
            mkdir($_all_path, 0777);
            fix_permissions($_all_path);
        }

        $addons = $this->addons;
        foreach ($addons as $addon) {
            $files = $this->find_theme_files_from_addon($addon, $subdir, $theme);

            if ($filter_level_b !== null) {
                if (!isset($files[$subdir . '/' . $filter_level_b])) {
                    continue;
                }
            }

            $_path = $path . '/' . $addon;
            if (is_dir($_path)) {
                deldir_contents($_path);
                rmdir($_path);
            }

            if (count($files) > 0) {
                mkdir($_path, 0777);
                fix_permissions($_path);
            }

            foreach ($files as $file) {
                $places_for_referencing = array(
                    $_path . '/' . $file['filename'],
                    $_all_path . '/' . $file['filename'],
                );

                if ($relationships_mode) {
                    $_relationships = $GLOBALS['SITE_DB']->query_select('theme_template_relations', array('rel_b'), array('rel_a' => $file['mini_path']));
                    $relationships = collapse_1d_complexity('rel_b', $_relationships);

                    if (count($relationships) > 0) {
                        foreach ($places_for_referencing as $place) {
                            if (!is_dir($place)) {
                                mkdir($place, 0777);
                                fix_permissions($place);
                            }

                            foreach ($relationships as $relationship) {
                                $relations_template_path = find_template_path(basename($relationship), dirname($relationship), $theme);
                                if ($relations_template_path !== null) {
                                    $new_symlink = $place . '/' . basename($relationship);
                                    @symlink($relations_template_path, $new_symlink);
                                    fix_permissions($new_symlink);
                                }
                            }

                            $new_symlink = $place . '/' . $file['filename'];
                            @symlink($file['full_path'], $new_symlink);
                            fix_permissions($new_symlink);
                        }
                    }
                } else {
                    foreach ($places_for_referencing as $place) {
                        $new_symlink = $place;
                        @symlink($file['full_path'], $new_symlink);
                        fix_permissions($new_symlink);
                    }
                }
            }
        }
    }

    /**
     * Find all of a particular kind of file in an addon.
     *
     * @param  ID_TEXT $addon The addon
     * @param  ID_TEXT $subdir The theme subdirectory we're working against
     * @param  ID_TEXT $theme The theme
     * @return array The files
     */
    private function find_theme_files_from_addon($addon, $subdir, $theme)
    {
        static $cache = array();
        if (isset($cache[$addon][$subdir])) {
            return $cache[$addon][$subdir];
        }

        $files = array();

        require_code('hooks/systems/addon_registry/' . $addon);
        $ob = object_factory('Hook_addon_registry_' . $addon);
        $_files = $ob->get_file_list();
        $test_for = 'themes/default/' . $subdir . '/';
        $test_for_2 = 'themes/default/' . $subdir . '_custom/';
        foreach ($_files as $file_path) {
            if (substr($file_path, 0, strlen($test_for)) == $test_for || substr($file_path, 0, strlen($test_for_2)) == $test_for_2) {
                $file = basename($file_path);

                if (($file != 'index.html') && ($file != '.htaccess')) {
                    $template_path = find_template_path($file, $subdir, $theme);

                    $mini_path = substr($file_path, strlen('themes/default/'));

                    $files[$mini_path] = array(
                        'full_path' => $template_path,
                        'mini_path' => $mini_path,
                        'filename' => $file,
                    );
                }
            }
        }

        $cache[$addon][$subdir] = $files;

        return $files;
    }

    /**
     * Put standard directory files (security files) into a directory.
     *
     * @param  PATH $path The path
     */
    private function put_in_standard_dir_files($path)
    {
        copy(get_custom_file_base() . '/themes/default/templates/index.html', $path . '/index.html');
        fix_permissions($path . '/index.html');

        copy(get_custom_file_base() . '/themes/default/templates/.htaccess', $path . '/.htaccess');
        fix_permissions($path . '/.htaccess');
    }
}
