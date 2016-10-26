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
 * @package    quizzes
 */

/**
 * Hook class.
 */
class Hook_content_meta_aware_quiz
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT $zone The zone to link through to (null: autodetect).
     * @return ?array Map of award content-type info (null: disabled).
     */
    public function info($zone = null)
    {
        return array(
            'support_custom_fields' => true,

            'content_type_label' => 'quiz:QUIZ',
            'content_type_universal_label' => 'Quiz',

            'connection' => $GLOBALS['SITE_DB'],
            'table' => 'quizzes',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_meta_aware_type' => null,
            'is_category' => false,
            'is_entry' => true,
            'category_field' => 'q_type', // For category permissions
            'category_type' => 'quiz', // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_is_string' => true,

            'title_field' => 'q_name',
            'title_field_dereference' => true,
            'description_field' => 'q_start_text',
            'description_field_dereference' => true,
            'thumb_field' => null,
            'thumb_field_is_theme_image' => false,
            'alternate_icon_theme_image' => 'icons/48x48/menu/rich_content/quiz',

            'view_page_link_pattern' => '_SEARCH:quiz:do:_WILD',
            'edit_page_link_pattern' => '_SEARCH:cms_quiz:_edit:_WILD',
            'view_category_page_link_pattern' => null,
            'add_url' => (function_exists('has_submit_permission') && has_submit_permission('high', get_member(), get_ip_address(), 'cms_quiz')) ? (get_module_zone('cms_quiz') . ':cms_quiz:add') : null,
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('quiz')) . ':quiz',

            'support_url_monikers' => true,

            'views_field' => null,
            'order_field' => null,
            'submitter_field' => 'q_submitter',
            'author_field' => null,
            'add_time_field' => 'q_add_date',
            'edit_time_field' => null,
            'date_field' => 'q_add_date',
            'validated_field' => 'q_validated',

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'permissions_type_code' => null, // null if has no permissions

            'search_hook' => 'quiz',
            'rss_hook' => null,
            'attachment_hook' => null,
            'unvalidated_hook' => 'quiz',
            'notification_hook' => null,
            'sitemap_hook' => 'quiz',

            'addon_name' => 'quizzes',

            'cms_page' => 'cms_quiz',
            'module' => 'quiz',

            'commandr_filesystem_hook' => 'quizzes',
            'commandr_filesystem__is_folder' => false,

            'support_revisions' => false,

            'support_privacy' => false,

            'support_content_reviews' => true,

            'support_spam_heuristics' => null,

            'actionlog_regexp' => '\w+_QUIZ',
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
        require_code('quiz');

        return render_quiz_box($row, $zone, $give_context, $guid);
    }
}
