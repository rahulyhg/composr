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
 * @package    news
 */

/**
 * Hook class.
 */
class Hook_sitemap_news extends Hook_sitemap_content
{
    protected $content_type = 'news';
    protected $screen_type = 'view';

    // If we have a different content type of entries, under this content type
    protected $entry_content_type = null;
    protected $entry_sitetree_hook = null;

    /**
     * Get the permission page that nodes matching $page_link in this hook are tied to.
     * The permission page is where privileges may be overridden against.
     *
     * @param  string $page_link The page-link
     * @return ?ID_TEXT The permission page (null: none)
     */
    public function get_privilege_page($page_link)
    {
        return 'cms_news';
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
        if (!addon_installed('news')) {
            return null;
        }

        $_ = $this->_create_partial_node_structure($page_link, $callback, $valid_node_types, $child_cutoff, $max_recurse_depth, $recurse_level, $options, $zone, $meta_gather, $row);
        if ($_ === null) {
            return null;
        }
        list($content_id, $row, $partial_struct) = $_;

        $struct = array(
            'sitemap_priority' => SITEMAP_IMPORTANCE_HIGH,
            'sitemap_refreshfreq' => 'monthly',

            'privilege_page' => $this->get_privilege_page($page_link),

            'edit_url' => build_url(array('page' => 'cms_news', 'type' => '_edit', 'id' => $content_id), get_module_zone('cms_news')),
        ) + $partial_struct;

        if (($meta_gather & SITEMAP_GATHER_CATEGORIES) != 0) {
            $categories = array();

            $categories[] = get_translated_text($GLOBALS['SITE_DB']->query_select_value('news_categories', 'nc_title', array('id' => $row['news_category'])));

            $table = 'news_category_entries e JOIN ' . get_table_prefix() . 'news_categories c ON c.id=e.news_entry_category';
            $_categories = $GLOBALS['SITE_DB']->query_select($table, array('nc_title'), array('e.news_entry' => intval($content_id)));
            foreach ($_categories as $_category) {
                $categories[] = get_translated_text($_category['nc_title']);
            }

            $struct['extra_meta']['categories'] = array_unique($categories);
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
