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
 * @package    news
 */

/**
 * Hook class.
 */
class Hook_realtime_rain_news
{
    /**
     * Run function for realtime-rain hooks.
     *
     * @param  TIME $from Start of time range.
     * @param  TIME $to End of time range.
     * @return array A list of template parameter sets for rendering a 'drop'.
     */
    public function run($from, $to)
    {
        $drops = array();

        if (has_actual_page_access(get_member(), 'news')) {
            require_code('news');

            $rows = $GLOBALS['SITE_DB']->query('SELECT title,n.id,nc_img,submitter AS member_id,date_and_time AS timestamp,news_category FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news n LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news_categories c ON c.id=n.news_category WHERE date_and_time BETWEEN ' . strval($from) . ' AND ' . strval($to));

            foreach ($rows as $row) {
                if (!has_category_access(get_member(), 'news', $row['news_category'])) {
                    continue;
                }

                $timestamp = $row['timestamp'];
                $member_id = $row['member_id'];

                $image = get_news_category_image_url($row['nc_img']);

                $ticker_text = strip_comcode(get_translated_text($row['title']));

                $drops[] = rain_get_special_icons(null, $timestamp, null, $ticker_text) + array(
                        'TYPE' => 'news',
                        'FROM_MEMBER_ID' => strval($member_id),
                        'TO_MEMBER_ID' => null,
                        'TITLE' => rain_truncate_for_title(get_translated_text($row['title'])),
                        'IMAGE' => $image,
                        'TIMESTAMP' => strval($timestamp),
                        'RELATIVE_TIMESTAMP' => strval($timestamp - $from),
                        'TICKER_TEXT' => $ticker_text,
                        'URL' => build_url(array('page' => 'news', 'type' => 'view', 'id' => $row['id']), get_module_zone('news')),
                        'IS_POSITIVE' => false,
                        'IS_NEGATIVE' => false,

                        // These are for showing connections between drops. They are not discriminated, it's just three slots to give an ID code that may be seen as a commonality with other drops.
                        'FROM_ID' => 'member_' . strval($member_id),
                        'TO_ID' => null,
                        'GROUP_ID' => 'news_' . strval($row['id']),
                    );
            }
        }

        return $drops;
    }
}
