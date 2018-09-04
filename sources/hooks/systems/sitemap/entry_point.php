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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_sitemap_entry_point extends Hook_sitemap_base
{
    /**
     * Find if a page-link will be covered by this node.
     *
     * @param  ID_TEXT $page_link The page-link
     * @param  integer $options A bitmask of SITEMAP_GEN_* options
     * @return integer A SITEMAP_NODE_* constant
     */
    public function handles_page_link($page_link, $options)
    {
        if (preg_match('#^cms:cms_catalogues:add_catalogue:#', $page_link)) {
            return SITEMAP_NODE_HANDLED;
        }

        $matches = array();
        if (preg_match('#^([^:]*):([^:]*):([^:]*)$#', $page_link, $matches) != 0) {
            $zone = $matches[1];
            $page = $matches[2];
            $type = $matches[3];

            $details = $this->_request_page_details($page, $zone);

            if ($details !== false) {
                $path = end($details);
                if ($details[0] == 'MODULES' || $details[0] == 'MODULES_CUSTOM') {
                    $functions = extract_module_functions(get_file_base() . '/' . $path, array('get_entry_points', 'get_wrapper_icon'), array(
                        false, // $check_perms
                        $this->get_member($options), // $member_id
                        true, // $support_crosslinks
                        true, // $be_deferential
                    ));
                    if ($functions[0] !== null) {
                        $entry_points = is_array($functions[0]) ? call_user_func_array($functions[0][0], $functions[0][1]) : cms_eval($functions[0], get_file_base() . '/' . $path);

                        if ($entry_points !== null) {
                            if (isset($entry_points['browse'])) {
                                unset($entry_points['browse']);
                            } else {
                                array_shift($entry_points);
                            }
                        }

                        if (isset($entry_points[$type])) {
                            return SITEMAP_NODE_HANDLED;
                        }
                    }
                }
            }
        }
        return SITEMAP_NODE_NOT_HANDLED;
    }

    /**
     * Find details of a position in the Sitemap.
     *
     * @param  ID_TEXT $page_link The page-link we are finding
     * @param  ?string $callback Callback function to send discovered page-links to (null: return)
     * @param  ?array $valid_node_types List of node types we will return/recurse-through (null: no limit)
     * @param  ?integer $child_cutoff Maximum number of children before we cut off all children (null: no limit)
     * @param  ?integer $max_recurse_depth How deep to go from the Sitemap root (null: no limit)
     * @param  integer $recurse_level Our recursion depth (used to limit recursion, or to calculate importance of page-link, used for instance by XML Sitemap [deeper is typically less important])
     * @param  integer $options A bitmask of SITEMAP_GEN_* options
     * @param  ID_TEXT $zone The zone we will consider ourselves to be operating in (needed due to transparent redirects feature)
     * @param  integer $meta_gather A bitmask of SITEMAP_GATHER_* constants, of extra data to include
     * @param  ?array $row Database row (null: lookup)
     * @param  boolean $return_anyway Whether to return the structure even if there was a callback. Do not pass this setting through via recursion due to memory concerns, it is used only to gather information to detect and prevent parent/child duplication of default entry points.
     * @return ?array Node structure (null: working via callback / error)
     */
    public function get_node($page_link, $callback = null, $valid_node_types = null, $child_cutoff = null, $max_recurse_depth = null, $recurse_level = 0, $options = 0, $zone = '_SEARCH', $meta_gather = 0, $row = null, $return_anyway = false)
    {
        $matches = array();
        preg_match('#^([^:]*):([^:]*)(:([^:]*)(:.*|$))?#', $page_link, $matches);
        $page = $matches[2];
        if (!isset($matches[3])) {
            $matches[3] = '';
        }
        if (!isset($matches[4])) {
            $matches[4] = '';
        }
        if (!isset($matches[5])) {
            $matches[5] = '';
        }
        $type = $matches[4];
        if ($type == '') {
            $type = 'browse';
        }
        $id = null;
        if ($matches[5] != '') {
            $_id = substr($matches[5], 1);
            if (strpos($_id, '=') === false) {
                $id = $_id;
            }
        }

        require_all_lang();

        $orig_page_link = $page_link;
        $this->_make_zone_concrete($zone, $page_link);

        $details = $this->_request_page_details($page, $zone);
        if ($details === false) {
            return null;
        }

        $path = end($details);

        if (($type == 'add_catalogue') && ($matches[5] != '') && ($matches[5][1] == '_')) {
            // Needs to be remapped to custom field kind of language
            require_code('fields');
            $content_type = preg_replace('#:.*$#', '', substr($matches[5], 2));
            $entry_points = manage_custom_fields_entry_points($content_type);
            $entry_point = $entry_points['_SEARCH:cms_catalogues:add_catalogue:_' . $content_type];
        } else {
            if ($row === null) {
                $functions = extract_module_functions(get_file_base() . '/' . $path, array('get_entry_points', 'get_wrapper_icon'), array(
                    true, // $check_perms
                    $this->get_member($options), // $member_id
                    false, //$support_crosslinks   Must be false so that things known to be cross-linked from elsewhere are not skipped
                    false, //$be_deferential

                ));

                $entry_points = is_array($functions[0]) ? call_user_func_array($functions[0][0], $functions[0][1]) : cms_eval($functions[0], get_file_base() . '/' . $path);

                if ((($matches[5] == '') || ($page == 'cms_catalogues' && $matches[5] != ''/*masquerades as direct content types but fulfilled as normal entry points*/)) && (isset($entry_points[$type]))) {
                    $entry_point = $entry_points[$type];
                } elseif (($matches[5] == '') && ((isset($entry_points['!'])) && ($type == 'browse'))) {
                    $entry_point = $entry_points['!'];
                } else {
                    if (isset($entry_points[$orig_page_link])) {
                        $entry_point = $entry_points[$orig_page_link];
                    } else {
                        $entry_point = array(null, null);

                        // Not actually an entry-point, so maybe something else handles it directly?
                        // Technically this would be better code to have in page_grouping.php, but we don't want to do a scan for entry-points that are easy to find.
                        $hooks = find_all_hook_obs('systems', 'sitemap', 'Hook_sitemap_');
                        foreach ($hooks as $ob) {
                            if ($ob->is_active()) {
                                $is_handled = $ob->handles_page_link($page_link, $options);
                                if ($is_handled == SITEMAP_NODE_HANDLED) {
                                    return $ob->get_node($page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, null, $return_anyway);
                                }
                            }
                        }
                    }
                }
            } else {
                $entry_point = $row;
            }
        }

        $icon = null;
        $_title = $entry_point[0];
        $icon = $entry_point[1];
        if ($_title === null) {
            if ($row !== null) {
                $title = $row[0];
            } else {
                $title = new Tempcode();
            }
        } elseif (is_object($_title)) {
            $title = $_title;
        } else {
            $title = (preg_match('#^[A-Z_]+$#', $_title) == 0) ? make_string_tempcode($_title) : do_lang_tempcode($_title);
        }

        if ($icon === null) {
            if ($row !== null) {
                $icon = $row[1];
            }
        }

        $struct = array(
            'title' => $title,
            'content_type' => 'page',
            'content_id' => $zone,
            'modifiers' => array(),
            'only_on_page' => '',
            'page_link' => $page_link,
            'url' => null,
            'extra_meta' => array(
                'description' => null,
                'image' => ($icon === null) ? null : find_theme_image('icons/' . $icon),
                'add_time' => (($meta_gather & SITEMAP_GATHER_TIMES) != 0) ? filectime(get_file_base() . '/' . $path) : null,
                'edit_time' => (($meta_gather & SITEMAP_GATHER_TIMES) != 0) ? filemtime(get_file_base() . '/' . $path) : null,
                'submitter' => null,
                'views' => null,
                'rating' => null,
                'meta_keywords' => null,
                'meta_description' => null,
                'categories' => null,
                'validated' => null,
                'db_row' => null,
            ),
            'permissions' => array(
                array(
                    'type' => 'zone',
                    'zone_name' => $zone,
                    'is_owned_at_this_level' => false,
                ),
                array(
                    'type' => 'page',
                    'zone_name' => $zone,
                    'page_name' => $page,
                    'is_owned_at_this_level' => false,
                ),
            ),
            'children' => null,
            'has_possible_children' => false,

            // These are likely to be changed in individual hooks
            'sitemap_priority' => SITEMAP_IMPORTANCE_MEDIUM,
            'sitemap_refreshfreq' => 'monthly',

            'privilege_page' => null,
        );

        if (($options & SITEMAP_GEN_LABEL_CONTENT_TYPES) != 0) {
            $struct['title'] = make_string_tempcode(do_lang('ENTRY_POINT') . ': ' . $title->evaluate());
        }

        $row_x = $this->_load_row_from_page_groupings(null, $zone, $page, $type, $id);
        if ($row_x != array()) {
            if ($_title !== null) {
                $row_x[0] = null; // We have a better title
            }
            if ($icon !== null) {
                $row_x[1] = null; // We have a better icon
            }
            $this->_ameliorate_with_row($options, $struct, $row_x, $meta_gather);
        }

        if (!$this->_check_node_permissions($struct, $options)) {
            return null;
        }

        // Look for virtual nodes to put under this
        if ($type != 'browse') {
            $hooks = find_all_hook_obs('systems', 'sitemap', 'Hook_sitemap_');
            foreach ($hooks as $ob) {
                if ($ob->is_active()) {
                    $is_handled = $ob->handles_page_link($page_link, $options);
                    if ($is_handled == SITEMAP_NODE_HANDLED_VIRTUALLY) {
                        $struct['has_possible_children'] = true;

                        if (($max_recurse_depth === null) || ($recurse_level < $max_recurse_depth)) {
                            $children = array();

                            $virtual_child_nodes = $ob->get_virtual_nodes($page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level + 1, $options, $zone, $meta_gather, true);
                            if ($virtual_child_nodes === null) {
                                $virtual_child_nodes = array();
                            }
                            foreach ($virtual_child_nodes as $child_node) {
                                if ($callback === null) {
                                    $children[$child_node['page_link']] = $child_node;
                                }
                            }

                            $struct['children'] = $children;
                        }
                    }
                }
            }
        }

        if ($callback !== null) {
            call_user_func($callback, $struct);
        }

        return ($callback === null || $return_anyway) ? $struct : null;
    }
}
