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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_sitemap_catalogue extends Hook_sitemap_content
{
    protected $content_type = 'catalogue';
    protected $screen_type = 'index';

    // If we have a different content type of entries, under this content type
    protected $entry_content_type = array('catalogue_category');
    protected $entry_sitetree_hook = array('catalogue_category');

    /**
     * Find if a page-link will be covered by this node.
     *
     * @param  ID_TEXT $page_link The page-link
     * @return integer A SITEMAP_NODE_* constant
     */
    public function handles_page_link($page_link)
    {
        $matches = array();
        if (preg_match('#^([^:]*):([^:]*)#', $page_link, $matches) != 0) {
            $zone = $matches[1];
            $page = $matches[2];

            require_code('content');
            $cma_ob = get_content_object($this->content_type);
            $cma_info = $cma_ob->info();
            require_code('site');
            if (($cma_info['module'] == $page) && ($zone != '_SEARCH') && (_request_page($page, $zone) !== false)) { // Ensure the given page matches the content type, and it really does exist in the given zone
                if ($matches[0] == $page_link) {
                    return SITEMAP_NODE_HANDLED_VIRTUALLY; // No type/ID specified
                }
                if (preg_match('#^([^:]*):([^:]*):(index|atoz)(:|$)#', $page_link, $matches) != 0) {
                    return SITEMAP_NODE_HANDLED;
                }
            }
        }
        return SITEMAP_NODE_NOT_HANDLED;
    }

    /**
     * Get the permission page that nodes matching $page_link in this hook are tied to.
     * The permission page is where privileges may be overridden against.
     *
     * @param  string $page_link The page-link
     * @return ?ID_TEXT The permission page (null: none)
     */
    public function get_privilege_page($page_link)
    {
        return 'cms_catalogues';
    }

    /**
     * Find details of a virtual position in the sitemap. Virtual positions have no structure of their own, but can find child structures to be absorbed down the tree. We do this for modularity reasons.
     *
     * @param  ID_TEXT $page_link The page-link we are finding
     * @param  ?string $callback Callback function to send discovered page-links to (null: return)
     * @param  ?array $valid_node_types List of node types we will return/recurse-through (null: no limit)
     * @param  ?integer $child_cutoff Maximum number of children before we cut off all children (null: no limit)
     * @param  ?integer $max_recurse_depth How deep to go from the sitemap root (null: no limit)
     * @param  integer $recurse_level Our recursion depth (used to limit recursion, or to calculate importance of page-link, used for instance by XML Sitemap [deeper is typically less important])
     * @param  integer $options A bitmask of SITEMAP_GEN_* options
     * @param  ID_TEXT $zone The zone we will consider ourselves to be operating in (needed due to transparent redirects feature)
     * @param  integer $meta_gather A bitmask of SITEMAP_GATHER_* constants, of extra data to include
     * @param  boolean $return_anyway Whether to return the structure even if there was a callback. Do not pass this setting through via recursion due to memory concerns, it is used only to gather information to detect and prevent parent/child duplication of default entry points.
     * @return ?array List of node structures (null: working via callback)
     */
    public function get_virtual_nodes($page_link, $callback = null, $valid_node_types = null, $child_cutoff = null, $max_recurse_depth = null, $recurse_level = 0, $options = 0, $zone = '_SEARCH', $meta_gather = 0, $return_anyway = false)
    {
        if (!addon_installed('catalogues')) {
            return array();
        }

        $nodes = ($callback === null || $return_anyway) ? array() : null;

        if (($valid_node_types !== null) && (!in_array($this->content_type, $valid_node_types))) {
            return $nodes;
        }

        $page = $this->_make_zone_concrete($zone, $page_link);

        $map = array();
        if (get_forum_type() != 'cns' || !addon_installed('shopping')) {
            $map = array('c_ecommerce' => 0);
        }

        if ($child_cutoff !== null) {
            $count = $GLOBALS['SITE_DB']->query_select_value('catalogues', 'COUNT(*)', $map);
            if ($count > $child_cutoff) {
                return $nodes;
            }
        }

        $start = 0;
        do {
            $rows = $GLOBALS['SITE_DB']->query_select('catalogues', array('*'), $map, '', SITEMAP_MAX_ROWS_PER_LOOP, $start);
            foreach ($rows as $row) {
                if (substr($row['c_name'], 0, 1) != '_') {
                    // Index
                    $child_page_link = $zone . ':' . $page . ':index:' . $row['c_name'];
                    $node = $this->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
                    if (($callback === null || $return_anyway) && ($node !== null)) {
                        $nodes[] = $node;
                    }
                }
            }

            $start += SITEMAP_MAX_ROWS_PER_LOOP;
        } while (count($rows) == SITEMAP_MAX_ROWS_PER_LOOP);

        if (is_array($nodes)) {
            sort_maps_by($nodes, 'title', false, true);
        }

        return $nodes;
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
        if (!addon_installed('catalogues')) {
            return null;
        }

        $page_link_fudged = preg_replace('#:catalogue_name=#', ':', $page_link);
        $_ = $this->_create_partial_node_structure($page_link_fudged, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
        if ($_ === null) {
            return null;
        }
        list($content_id, $row, $partial_struct) = $_;

        $matches = array();
        preg_match('#^([^:]*):([^:]*)#', $page_link, $matches);
        $page = $matches[2];

        $this->_make_zone_concrete($zone, $page_link);

        $struct = array(
            'sitemap_priority' => SITEMAP_IMPORTANCE_MEDIUM,
            'sitemap_refreshfreq' => 'weekly',

            'edit_url' => build_url(array('page' => 'cms_catalogues', 'type' => '_edit_catalogue', 'id' => $content_id), get_module_zone('cms_catalogues')),
        ) + $partial_struct;

        if (strpos($page_link, ':index:') !== false) {
            $struct['extra_meta']['description'] = null;

            if (($meta_gather & SITEMAP_GATHER_IMAGE) != 0) {
                $test = find_theme_image('icons/menu/rich_content/catalogues/' . $content_id, true);
                if ($test == '') {
                    $test = find_theme_image('icons/menu/rich_content/catalogues/catalogues', true);
                }
                if ($test != '') {
                    $struct['extra_meta']['image'] = $test;
                }
            }

            if (($max_recurse_depth === null) || ($recurse_level < $max_recurse_depth)) {
                $children = array();

                // A-to-Z child
                if (($options & SITEMAP_GEN_REQUIRE_PERMISSION_SUPPORT) == 0) {
                    $child_page_link = $zone . ':' . $page . ':atoz:catalogue_name=' . $content_id;
                    $child_node = $this->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
                    if ($child_node !== null) {
                        $children[] = $child_node;
                    }
                }

                // Categories
                $count = $GLOBALS['SITE_DB']->query_select_value('catalogue_categories', 'COUNT(*)', array('c_name' => $content_id));
                $lots = ($count > 3000) || ($child_cutoff !== null) && ($count > $child_cutoff);
                if (!$lots) {
                    $child_hook_ob = $this->_get_sitemap_object('catalogue_category');

                    $children_entries = array();
                    $start = 0;
                    do {
                        $where = array('c_name' => $content_id, 'cc_parent_id' => null);
                        $rows = $GLOBALS['SITE_DB']->query_select('catalogue_categories', array('*'), $where, '', SITEMAP_MAX_ROWS_PER_LOOP, $start);
                        foreach ($rows as $child_row) {
                            $child_page_link = $zone . ':' . $page . ':category:' . strval($child_row['id']);
                            $child_node = $child_hook_ob->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level + 1, $options, $zone, $meta_gather, $child_row);
                            if ($child_node !== null) {
                                if (($meta_gather & SITEMAP_GATHER_IMAGE) != 0) {
                                    $test = find_theme_image('icons/admin/view_this_category', true);
                                    if ($test != '') {
                                        $child_node['extra_meta']['image'] = $test;
                                    }
                                }

                                $children_entries[] = $child_node;
                            }
                        }
                        $start += SITEMAP_MAX_ROWS_PER_LOOP;
                    } while (count($rows) == SITEMAP_MAX_ROWS_PER_LOOP);

                    sort_maps_by($children_entries, 'title', false, true);

                    $children = array_merge($children, $children_entries);
                }

                $struct['children'] = $children;
            }
        } elseif (strpos($page_link, ':atoz:') !== false) { // A-Z
            $struct['page_link'] = $page_link;

            $struct['extra_meta']['description'] = null;

            $struct['title'] = do_lang_tempcode('catalogues:ATOZ');

            if (($meta_gather & SITEMAP_GATHER_IMAGE) != 0) {
                $test = find_theme_image('icons/menu/rich_content/atoz', true);
                if ($test != '') {
                    $struct['extra_meta']['image'] = $test;
                }
            }
        } else {
            warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        }

        if (!$this->_check_node_permissions($struct)) {
            return null;
        }

        if ($callback !== null) {
            call_user_func($callback, $struct);
        }

        return ($callback === null || $return_anyway) ? $struct : null;
    }
}
