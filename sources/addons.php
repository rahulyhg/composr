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
 * @package    core_addon_management
 */

/**
 * Find detail addon details.
 *
 * @return array Map of default addon details
 */
function get_default_addon_details()
{
    return array(
        'name' => '',
        'author' => '',
        'organisation' => '',
        'version' => '1.0',
        'category' => 'Uncategorised/Alpha',
        'copyright_attribution' => array(),
        'licence' => '(Unstated)',
        'description' => '',
        'install_time' => time(),
        'files' => array(),
        'dependencies' => array(),
    );
}

/**
 * Get info about an addon, simulating an extended version of the traditional Composr-addon database row.
 *
 * @param  string $addon The name of the addon
 * @param  boolean $get_dependencies_on_this Whether to search for dependencies on this
 * @param  ?array $row Database row (null: lookup via a new query)
 * @param  ?array $ini_info .ini-format info (needs processing) (null: unknown / N/A)
 * @return array The map of details
 */
function read_addon_info($addon, $get_dependencies_on_this = false, $row = null, $ini_info = null)
{
    // Hook file has highest priority...

    $is_orig = false;
    $path = get_file_base() . '/sources_custom/hooks/systems/addon_registry/' . filter_naughty_harsh($addon) . '.php';
    if (!file_exists($path)) {
        $is_orig = true;
        $path = get_file_base() . '/sources/hooks/systems/addon_registry/' . filter_naughty_harsh($addon) . '.php';
    }

    if (file_exists($path)) {
        $_hook_bits = extract_module_functions($path, array('get_dependencies', 'get_version', 'get_category', 'get_copyright_attribution', 'get_licence', 'get_description', 'get_author', 'get_organisation', 'get_file_list', 'get_default_icon'));
        if ($_hook_bits[0] !== null) {
            $dep = is_array($_hook_bits[0]) ? call_user_func_array($_hook_bits[0][0], $_hook_bits[0][1]) : @eval($_hook_bits[0]);
        } else {
            $dep = array();
        }
        $defaults = get_default_addon_details();
        if ($_hook_bits[1] !== null) {
            $version = is_array($_hook_bits[1]) ? call_user_func_array($_hook_bits[1][0], $_hook_bits[1][1]) : @eval($_hook_bits[1]);
        } else {
            $version = $defaults['version'];
        }
        if ($_hook_bits[2] !== null) {
            $category = is_array($_hook_bits[2]) ? call_user_func_array($_hook_bits[2][0], $_hook_bits[2][1]) : @eval($_hook_bits[2]);
        } else {
            $category = $defaults['category'];
        }
        if ($_hook_bits[3] !== null) {
            $copyright_attribution = is_array($_hook_bits[3]) ? call_user_func_array($_hook_bits[3][0], $_hook_bits[3][1]) : @eval($_hook_bits[3]);
        } else {
            $copyright_attribution = $defaults['copyright_attribution'];
        }
        if ($_hook_bits[4] !== null) {
            $licence = is_array($_hook_bits[4]) ? call_user_func_array($_hook_bits[4][0], $_hook_bits[4][1]) : @eval($_hook_bits[4]);
        } else {
            $licence = $defaults['licence'];
        }
        $description = is_array($_hook_bits[5]) ? call_user_func_array($_hook_bits[5][0], $_hook_bits[5][1]) : @eval($_hook_bits[5]);
        if ($_hook_bits[6] !== null) {
            $author = is_array($_hook_bits[6]) ? call_user_func_array($_hook_bits[6][0], $_hook_bits[6][1]) : @eval($_hook_bits[6]);
        } else {
            $author = $is_orig ? 'Core Team' : $defaults['author'];
        }
        if ($_hook_bits[7] !== null) {
            $organisation = is_array($_hook_bits[7]) ? call_user_func_array($_hook_bits[7][0], $_hook_bits[7][1]) : @eval($_hook_bits[7]);
        } else {
            $organisation = $is_orig ? 'ocProducts' : $defaults['organisation'];
        }
        if ($_hook_bits[8] !== null) {
            $file_list = is_array($_hook_bits[8]) ? call_user_func_array($_hook_bits[8][0], $_hook_bits[8][1]) : @eval($_hook_bits[8]);
        } else {
            $file_list = array();
        }
        if ($_hook_bits[9] !== null) {
            $default_icon = is_array($_hook_bits[9]) ? call_user_func_array($_hook_bits[9][0], $_hook_bits[9][1]) : @eval($_hook_bits[9]);
        } else {
            $default_icon = mixed();
        }

        $addon_info = array(
            'name' => $addon,
            'author' => $author,
            'organisation' => $organisation,
            'version' => float_to_raw_string($version, 2, true),
            'category' => $category,
            'copyright_attribution' => $copyright_attribution,
            'licence' => $licence,
            'description' => $description,
            'install_time' => filemtime($path),
            'files' => $file_list,
            'dependencies' => $dep['requires'],
            'incompatibilities' => $dep['conflicts_with'],
            'default_icon' => $default_icon,
        );
        if ($get_dependencies_on_this) {
            $addon_info['dependencies_on_this'] = find_addon_dependencies_on($addon);
        }

        return $addon_info;
    }

    // Next try .ini file

    if ($ini_info !== null) {
        $version = $ini_info['version'];
        if ($version == '(version-synched)') {
            $version = float_to_raw_string(cms_version_number(), 2, true);
        }

        $dependencies = array_key_exists('dependencies', $ini_info) ? explode(',', $ini_info['dependencies']) : array();
        $incompatibilities = array_key_exists('incompatibilities', $ini_info) ? explode(',', $ini_info['incompatibilities']) : array();

        $addon_info = array(
            'name' => $ini_info['name'],
            'author' => $ini_info['author'],
            'organisation' => $ini_info['organisation'],
            'version' => $version,
            'category' => $ini_info['category'],
            'copyright_attribution' => explode("\n", $ini_info['copyright_attribution']),
            'licence' => $ini_info['licence'],
            'description' => $ini_info['description'],
            'install_time' => time(),
            'files' => $ini_info['files'],
            'dependencies' => $dependencies,
            'incompatibilities' => $incompatibilities,
            'default_icon' => null,
        );
        if ($get_dependencies_on_this) {
            $addon_info['dependencies_on_this'] = find_addon_dependencies_on($addon);
        }

        return $addon_info;
    }

    // Next try what is in the database...

    if ($row === null) {
        $addon_rows = $GLOBALS['SITE_DB']->query_select('addons', array('*'), array('addon_name' => $addon), '', 1);
        if (array_key_exists(0, $addon_rows)) {
            $row = $addon_rows[0];
        }
    }

    if ($row !== null) {
        $addon_info = array(
            'name' => $row['addon_name'],
            'author' => $row['addon_author'],
            'organisation' => $row['addon_organisation'],
            'version' => $row['addon_version'],
            'category' => $row['addon_category'],
            'copyright_attribution' => explode("\n", $row['addon_copyright_attribution']),
            'licence' => $row['addon_licence'],
            'description' => $row['addon_description'],
            'install_time' => $row['addon_install_time'],
            'default_icon' => null,
        );

        $addon_info['files'] = array_unique(collapse_1d_complexity('filename', $GLOBALS['SITE_DB']->query_select('addons_files', array('filename'), array('addon_name' => $addon))));
        $addon_info['dependencies'] = collapse_1d_complexity('addon_name_dependant_upon', $GLOBALS['SITE_DB']->query_select('addons_dependencies', array('addon_name_dependant_upon'), array('addon_name' => $addon, 'addon_name_incompatibility' => 0)));
        $addon_info['incompatibilities'] = collapse_1d_complexity('addon_name_dependant_upon', $GLOBALS['SITE_DB']->query_select('addons_dependencies', array('addon_name_dependant_upon'), array('addon_name' => $addon, 'addon_name_incompatibility' => 1)));
        if ($get_dependencies_on_this) {
            $addon_info['dependencies_on_this'] = find_addon_dependencies_on($addon);
        }
        return $addon_info;
    }

    warn_exit(do_lang_tempcode('MISSING_RESOURCE', do_lang_tempcode('ADDON')));
}

/**
 * Find the icon for an addon.
 *
 * @param  ID_TEXT $addon_name Addon name
 * @param  boolean $pick_default Whether to use a default icon if not found
 * @param  ?PATH $tar_path Path to TAR file (null: don't look inside a TAR / it's installed already)
 * @return ?string Theme image URL (may be a "data:" URL rather than a normal URLPATH) (null: not found)
 */
function find_addon_icon($addon_name, $pick_default = true, $tar_path = null)
{
    $matches = array();

    if ($tar_path !== null) {
        require_code('tar');
        $tar_file = tar_open($tar_path, 'rb');
        $directory = tar_get_directory($tar_file, true);
        if ($directory !== null) {
            // Is there an explicitly defined addon?
            $_data = tar_get_file($tar_file, 'sources_custom/hooks/systems/addon_registry/' . $addon_name . '.php', true);
            if ($_data === null) {
                $_data = tar_get_file($tar_file, 'sources/hooks/systems/addon_registry/' . $addon_name . '.php', true);
            }
            if ($_data !== null) {
                $data = str_replace('<' . '?php', '', $_data['data']);
                @eval($data);
                $ob = object_factory('Hook_addon_registry_' . $addon_name, true);
                if (($ob !== null) && (method_exists($ob, 'get_default_icon'))) {
                    $file = $ob->get_default_icon();
                    if (file_exists(get_file_base() . '/' . $file)) {
                        return get_base_url() . '/' . str_replace('%2F', '/', urlencode($file));
                    } else {
                        require_code('mime_types');
                        $file = $ob->get_default_icon();
                        $image_data = tar_get_file($tar_file, $file);
                        if ($image_data === null) {
                            return $pick_default ? find_theme_image('icons/48x48/menu/_generic_admin/component') : null;
                        }
                        return 'data:' . get_mime_type(get_file_extension($file), true) . ';base64,' . base64_encode($image_data['data']);
                    }
                }
            }

            // Search through for an icon
            foreach ($directory as $d) {
                $file = $d['path'];
                if (preg_match('#^themes/default/(images|images_custom)/icons/48x48/(.*)\.(png|jpg|jpeg|gif)$#', $file, $matches) != 0) {
                    require_code('mime_types');
                    $data = tar_get_file($tar_file, $file);
                    return 'data:' . get_mime_type(get_file_extension($file), true) . ';base64,' . base64_encode($data['data']);
                }
            }
        }
    } else {
        $addon_info = read_addon_info($addon_name);

        // Is there an explicitly defined addon?
        if ($addon_info['default_icon'] !== null) {
            return get_base_url() . '/' . str_replace('%2F', '/', urlencode($addon_info['default_icon']));
        }

        // Search through for an icon
        $addon_files = $addon_info['files'];
        foreach ($addon_files as $file) {
            if (preg_match('#^themes/default/(images|images_custom)/icons/48x48/(.*)\.(png|jpg|jpeg|gif)$#', $file, $matches) != 0) {
                return get_base_url() . '/' . str_replace('%2F', '/', urlencode($file));
            }
        }
    }

    // Default, as not found
    return $pick_default ? find_theme_image('icons/48x48/menu/_generic_admin/component') : null;
}
