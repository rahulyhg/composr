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
class Hook_content_meta_aware_catalogue_category
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect)
     * @return ?array Map of award content-type info (null: disabled)
     */
    public function info($zone = null)
    {
        return array(
            'support_custom_fields' => true,

            'content_type_label' => 'catalogues:CATALOGUE_CATEGORY',
            'content_type_universal_label' => 'Catalogue category',

            'db' => $GLOBALS['SITE_DB'],
            'table' => 'catalogue_categories',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => 'cc_parent_id',
            'parent_category_meta_aware_type' => 'catalogue_category',
            'is_category' => true,
            'is_entry' => false,
            'category_field' => array('c_name', 'id'), // For category permissions
            'category_type' => array('catalogues_catalogue', 'cc_parent_id'), // For category permissions
            'parent_spec__table_name' => 'catalogue_categories',
            'parent_spec__parent_name' => 'cc_parent_id',
            'parent_spec__field_name' => 'id',
            'category_is_string' => false,

            'title_field' => 'cc_title',
            'title_field_dereference' => true,
            'description_field' => 'cc_description',
            'description_field_dereference' => true,
            'thumb_field' => 'rep_image',
            'thumb_field_is_theme_image' => false,
            'alternate_icon_theme_image' => null,

            'view_page_link_pattern' => '_SEARCH:catalogues:category:_WILD',
            'edit_page_link_pattern' => '_SEARCH:cms_catalogues:_edit_category:_WILD',
            'view_category_page_link_pattern' => '_SEARCH:catalogues:category:_WILD',
            'add_url' => (function_exists('has_submit_permission') && has_submit_permission('mid', get_member(), get_ip_address(), 'cms_catalogues')) ? (get_module_zone('cms_catalogues') . ':cms_catalogues:add_category:catalogue_name=!') : null,
            'archive_url' => (($zone !== null) ? $zone : get_module_zone('catalogues')) . ':catalogues',

            'support_url_monikers' => true,

            'views_field' => null,
            'order_field' => 'cc_order',
            'submitter_field' => null,
            'author_field' => null,
            'add_time_field' => 'cc_add_date',
            'edit_time_field' => null,
            'date_field' => 'cc_add_date',
            'validated_field' => null,

            'seo_type_code' => 'catalogue_category',

            'feedback_type_code' => null,

            'permissions_type_code' => (get_value('disable_cat_cat_perms') === '1') ? null : 'catalogues_category', // null if has no permissions

            'search_hook' => 'catalogue_categories',
            'rss_hook' => null,
            'attachment_hook' => null,
            'unvalidated_hook' => null,
            'notification_hook' => null,
            'sitemap_hook' => 'catalogue_category',

            'addon_name' => 'catalogues',

            'cms_page' => 'cms_catalogues',
            'module' => 'catalogues',

            'commandr_filesystem_hook' => 'catalogues',
            'commandr_filesystem__is_folder' => true,

            'support_revisions' => false,

            'support_privacy' => false,

            'support_content_reviews' => true,

            'support_spam_heuristics' => null,

            'actionlog_regexp' => '\w+_CATALOGUE_CATEGORY',
        );
    }

    /**
     * Run function for content hooks. Renders a content box for an award/randomisation.
     *
     * @param  array $row The database row for the content
     * @param  ID_TEXT $zone The zone to display in
     * @param  boolean $give_context Whether to include context (i.e. say WHAT this is, not just show the actual content)
     * @param  boolean $include_breadcrumbs Whether to include breadcrumbs (if there are any)
     * @param  ?ID_TEXT $root Virtual root to use (null: none)
     * @param  boolean $attach_to_url_filter Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
     * @param  ID_TEXT $guid Overridden GUID to send to templates (blank: none)
     * @return Tempcode Results
     */
    public function run($row, $zone, $give_context = true, $include_breadcrumbs = true, $root = null, $attach_to_url_filter = false, $guid = '')
    {
        require_code('catalogues');

        return render_catalogue_category_box($row, $zone, $give_context, $include_breadcrumbs, ($root === null) ? null : intval($root), $attach_to_url_filter, $guid);
    }
}
