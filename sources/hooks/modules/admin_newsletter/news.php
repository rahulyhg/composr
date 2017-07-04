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
 * @package    news
 */

/**
 * Hook class.
 */
class Hook_whatsnew_news
{
    /**
     * Find selectable (filterable) categories.
     *
     * @param  TIME $updated_since The time that there must be entries found newer than
     * @return ?array Tuple of result details: HTML list of all types that can be choosed, title for selection list (null: disabled)
     */
    public function choose_categories($updated_since)
    {
        if (!addon_installed('news')) {
            return null;
        }

        require_lang('news');

        require_code('news');
        $cats = create_selection_list_news_categories(null, false, false, true, null, false, $updated_since);
        return array($cats, do_lang('NEWS'));
    }

    /**
     * Run function for newsletter hooks.
     *
     * @param  TIME $cutoff_time The time that the entries found must be newer than
     * @param  LANGUAGE_NAME $lang The language the entries found must be in
     * @param  string $filter Category filter to apply
     * @param  BINARY $in_full Whether to use full article instead of summary
     * @return array Tuple of result details
     */
    public function run($cutoff_time, $lang, $filter, $in_full = 1)
    {
        if (!addon_installed('news')) {
            return array();
        }

        require_lang('news');

        $max = intval(get_option('max_newsletter_whatsnew'));

        $new = new Tempcode();

        require_code('selectcode');
        $or_list = selectcode_to_sqlfragment($filter, 'news_category');
        $or_list_2 = selectcode_to_sqlfragment($filter, 'news_entry_category');

        $extra_join = '';
        $extra_where = '';
        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            list($extra_join, $extra_where) = get_privacy_where_clause('news', 'r', $GLOBALS['FORUM_DRIVER']->get_guest_id());
        }

        if (get_option('filter_regions') == '1') {
            require_code('locations');
            $extra_where .= sql_region_filter('news', 'r.id');
        }

        $rows = $GLOBALS['SITE_DB']->query('SELECT title,news,news_article,id,date_and_time,submitter,news_image FROM ' . get_table_prefix() . 'news r LEFT JOIN ' . get_table_prefix() . 'news_category_entries ON news_entry=id' . $extra_join . ' WHERE validated=1 AND date_and_time>' . strval($cutoff_time) . ' AND ((' . $or_list . ') OR (' . $or_list_2 . '))' . $extra_where . ' ORDER BY date_and_time DESC', $max);

        if (count($rows) == $max) {
            return array();
        }

        $rows = remove_duplicate_rows($rows, 'id');
        foreach ($rows as $row) {
            $id = $row['id'];
            $_url = build_url(array('page' => 'news', 'type' => 'view', 'id' => $row['id']), get_module_zone('news'), array(), false, false, true);
            $url = $_url->evaluate();
            $name = get_translated_text($row['title'], null, $lang);
            $description = get_translated_text($row[($in_full == 1) ? 'news_article' : 'news'], null, $lang);
            if ($description == '') {
                $description = get_translated_text($row[($in_full == 1) ? 'news' : 'news_article'], null, $lang);
            }
            $member_id = (is_guest($row['submitter'])) ? null : strval($row['submitter']);
            $thumbnail = $row['news_image'];
            if ($thumbnail != '') {
                if (url_is_local($thumbnail)) {
                    $thumbnail = get_custom_base_url() . '/' . $thumbnail;
                }
            } else {
                $thumbnail = mixed();
            }
            $new->attach(do_template('NEWSLETTER_WHATSNEW_RESOURCE_FCOMCODE', array('_GUID' => '4eaf5ec00db1f0b89cef5120c2486521', 'MEMBER_ID' => $member_id, 'URL' => $url, 'NAME' => $name, 'DESCRIPTION' => $description, 'THUMBNAIL' => $thumbnail, 'CONTENT_TYPE' => 'news', 'CONTENT_ID' => strval($id)), null, false, null, '.txt', 'text'));

            handle_has_checked_recently($url); // We know it works, so mark it valid so as to not waste CPU checking within the generated Comcode
        }

        return array($new, do_lang('NEWS', '', '', '', $lang));
    }
}
