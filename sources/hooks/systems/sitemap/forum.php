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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_sitemap_forum extends Hook_sitemap_content
{
    protected $content_type = 'forum';
    protected $screen_type = 'browse';

    // If we have a different content type of entries, under this content type
    protected $entry_content_type = array('topic');
    protected $entry_sitetree_hook = array('topic');

    /**
     * Get the permission page that nodes matching $page_link in this hook are tied to.
     * The permission page is where privileges may be overridden against.
     *
     * @param  string $page_link The page-link
     * @return ?ID_TEXT The permission page (null: none)
     */
    public function get_privilege_page($page_link)
    {
        return 'topics';
    }

    /**
     * Find whether the hook is active.
     *
     * @return boolean Whether the hook is active
     */
    public function is_active()
    {
        return (get_forum_type() == 'cns');
    }

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
                if (preg_match('#^([^:]*):([^:]*):id=#', $page_link, $matches) != 0) {
                    return SITEMAP_NODE_HANDLED;
                }
            }
        }
        return SITEMAP_NODE_NOT_HANDLED;
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
        if (!addon_installed('cns_forum')) {
            return array();
        }

        $nodes = ($callback === null || $return_anyway) ? array() : null;

        if (($valid_node_types !== null) && (!in_array($this->content_type, $valid_node_types))) {
            return $nodes;
        }

        $page = $this->_make_zone_concrete($zone, $page_link);

        $parent = (($options & SITEMAP_GEN_KEEP_FULL_STRUCTURE) == 0) ? db_get_first_id() : null;

        if ($child_cutoff !== null) {
            $count = $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'COUNT(*)', array('f_parent_forum' => $parent));
            if ($count > $child_cutoff) {
                return $nodes;
            }
        }

        $start = 0;
        do {
            $rows = $GLOBALS['FORUM_DB']->query_select('f_forums', array('*'), array('f_parent_forum' => $parent), '', SITEMAP_MAX_ROWS_PER_LOOP, $start);
            foreach ($rows as $row) {
                $child_page_link = $zone . ':' . $page . ':' . $this->screen_type . ':' . strval($row['id']);
                $node = $this->get_node($child_page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
                if (($callback === null || $return_anyway) && ($node !== null)) {
                    $nodes[] = $node;
                }
            }

            $start += SITEMAP_MAX_ROWS_PER_LOOP;
        } while (count($rows) == SITEMAP_MAX_ROWS_PER_LOOP);

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
        if (!addon_installed('cns_forum')) {
            return null;
        }

        if (!$this->check_for_looping($page_link)) {
            return null;
        }

        $page_link = str_replace(':id=', ':browse:', $page_link);

        $_ = $this->_create_partial_node_structure($page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
        if ($_ === null) {
            return null;
        }
        list($content_id, $row, $partial_struct) = $_;

        $partial_struct['page_link'] = str_replace(':browse:', ':id=', $partial_struct['page_link']);

        // level 0 = root
        // level 1 = zone
        if ($content_id == strval(db_get_first_id())) {
            $sitemap_priority = SITEMAP_IMPORTANCE_ULTRA; // level 2
        } else {
            if ($recurse_level == 3) {
                $sitemap_priority = SITEMAP_IMPORTANCE_HIGH;
            } else {
                $sitemap_priority = SITEMAP_IMPORTANCE_MEDIUM;
            }
        }

        $struct = array(
            'sitemap_priority' => $sitemap_priority,
            'sitemap_refreshfreq' => 'monthly',

            'privilege_page' => $this->get_privilege_page($page_link),

            'edit_url' => build_url(array('page' => 'admin_cns_forums', 'type' => '_edit', 'id' => $content_id), get_module_zone('admin_cns_forums')),
        ) + $partial_struct;

        $struct['extra_meta'] = array(
            'image' => (($meta_gather & SITEMAP_GATHER_IMAGE) != 0) ? find_theme_image('icons/menu/social/forum/forums') : null,
        ) + $struct['extra_meta'];


        $struct['extra_meta']['is_a_category_tree_root'] = true;

        if (!$this->_check_node_permissions($struct)) {
            return null;
        }

        if ($callback !== null) {
            call_user_func($callback, $struct);
        }

        // Categories done after node callback, to ensure sensible ordering
        $sort = $row['f_order'];
        $explicit_order_by_entries = 't_cache_last_time DESC';
        if ($sort == 'first_post') {
            $explicit_order_by_entries = 't_cache_first_time DESC';
        } elseif ($sort == 'title') {
            $explicit_order_by_entries = 't_cache_first_title ASC';
        }
        $explicit_order_by_categories = null;
        $per_page = intval(get_option('forum_posts_per_page'));
        $backup_meta_gather = $meta_gather;
        $meta_gather |= SITEMAP_GATHER_DB_ROW;
        $children = $this->_get_children_nodes($content_id, $page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row, '', $explicit_order_by_entries, $explicit_order_by_categories);
        if ($children !== null) {
            $children2 = array();
            foreach ($children as $child) {
                $child_row = $child['extra_meta']['db_row'];
                if ($child['content_type'] == 'topic') {
                    if (($backup_meta_gather & SITEMAP_GATHER_DB_ROW) == 0) {
                        $child['extra_meta']['db_row'] = null;
                    }
                    $num_posts = $child_row['t_cache_num_posts'];
                    $children2[] = $child;
                    for ($i = $per_page; $i < $num_posts; $i += $per_page) {
                        $children2[] = array('page_link' => $child['page_link'] . ':start=' . strval($i)) + $child;
                    }
                } else {
                    $children2[] = $child;
                }
            }
            $struct['children'] = $children2;
        }

        return ($callback === null || $return_anyway) ? $struct : null;
    }
}
