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
 * @package    points
 */

/**
 * Hook class.
 */
class Hook_rss_points
{
    /**
     * Run function for RSS hooks.
     *
     * @param  string $_filters A list of categories we accept from
     * @param  TIME $cutoff Cutoff time, before which we do not show results from
     * @param  string $prefix Prefix that represents the template set we use
     * @set    RSS_ ATOM_
     * @param  string $date_string The standard format of date to use for the syndication type represented in the prefix
     * @param  integer $max The maximum number of entries to return, ordering by date
     * @return ?array A pair: The main syndication section, and a title (null: error)
     */
    public function run($_filters, $cutoff, $prefix, $date_string, $max)
    {
        if (!addon_installed('points')) {
            return null;
        }

        if (!has_actual_page_access(get_member(), 'points')) {
            return null;
        }

        $filters = selectcode_to_sqlfragment($_filters, 'gift_to', 'f_members', null, 'gift_to', 'id', true, true); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)

        require_lang('points');

        $content = new Tempcode();
        $rows = $GLOBALS['SITE_DB']->query('SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'gifts WHERE ' . $filters . ' AND date_and_time>' . strval($cutoff) . ' ORDER BY date_and_time DESC', $max);
        foreach ($rows as $row) {
            $id = strval($row['id']);

            $author = '';
            if ($row['anonymous'] == 0) {
                $author = $GLOBALS['FORUM_DRIVER']->get_username($row['gift_from'], false, USERNAME_DEFAULT_BLANK);
            }

            $news_date = date($date_string, $row['date_and_time']);
            $edit_date = '';

            $to = $GLOBALS['FORUM_DRIVER']->get_username($row['gift_to']);
            $news_title = xmlentities(do_lang('POINTS_RSS_LINE', $to, integer_format($row['amount'])));
            $summary = xmlentities(get_translated_text($row['reason']));
            $news = '';

            $category = '';
            $category_raw = '';

            $view_url = build_url(array('page' => 'points', 'type' => 'member', 'id' => $row['gift_to']), get_module_zone('points'), array(), false, false, true);

            $if_comments = new Tempcode();

            $content->attach(do_template($prefix . 'ENTRY', array('VIEW_URL' => $view_url, 'SUMMARY' => $summary, 'EDIT_DATE' => $edit_date, 'IF_COMMENTS' => $if_comments, 'TITLE' => $news_title, 'CATEGORY_RAW' => $category_raw, 'CATEGORY' => $category, 'AUTHOR' => $author, 'ID' => $id, 'NEWS' => $news, 'DATE' => $news_date), null, false, null, '.xml', 'xml'));
        }

        require_lang('points');
        return array($content, do_lang('POINTS'));
    }
}
