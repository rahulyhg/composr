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
 * Top level function to (re)generate a news Sitemap (xml file, Google-style).
 */
function build_news_sitemap()
{
    require_code('xml');

    $path = get_custom_file_base() . '/data_custom/sitemaps/news_sitemap.xml';
    if (!file_exists(dirname($path))) {
        require_code('files2');
        make_missing_directory(dirname($path));
    }

    cms_profile_start_for('build_news_sitemap');

    $sitemap_file = fopen($path, GOOGLE_APPENGINE ? 'wb' : 'ab');
    flock($sitemap_file, LOCK_EX);
    if (!GOOGLE_APPENGINE) {
        ftruncate($sitemap_file, 0);
    }

    fwrite($sitemap_file, '<' . '?xml version="1.0" encoding="' . escape_html(get_charset()) . '"?' . '>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    ');

    $zone = get_module_zone('news');

    $guest_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
    $has_guest_page_access = has_actual_page_access($guest_id, 'news', $zone);
    require_code('users2');
    $modal_member_id = get_modal_user();
    $has_member_page_access = true;
    if ($modal_member_id !== null) {
        $has_member_page_access = has_actual_page_access($modal_member_id, 'news', $zone);
    }

    $site_location = get_value('site_location');

    $max = 200;

    $start = 0;
    do {
        $rows = $GLOBALS['SITE_DB']->query_select('news', array('*'), array(), 'ORDER BY date_and_time DESC', $max, $start);

        foreach ($rows as $row) {
            $url = build_url(array('page' => 'news', 'type' => 'view', 'id' => $row['id']), $zone, array(), false, false, true);

            $is_blog = ($GLOBALS['SITE_DB']->query_select_value('news_categories', 'nc_owner', array('id' => $row['news_category'])) !== null);

            $has_guest_category_access = has_category_access($guest_id, 'news', strval($row['news_category']));
            $has_member_category_access = true;
            if ($modal_member_id !== null) {
                $has_member_category_access = has_category_access($modal_member_id, 'news', strval($row['news_category']));
            }

            $meta = seo_meta_get_for('news', strval($row['id']));

            fwrite($sitemap_file, '
                    <url>
                            <loc>' . xmlentities($url->evaluate()) . '</loc>
                            <news:news>
                                        <news:publication>
                                                        <news:name>' . xmlentities(get_site_name()) . '</news:name>
                                                        <news:language>' . xmlentities(strtolower(get_site_default_lang())) . '</news:language>
                                        </news:publication>
            ');
            if (!$has_guest_category_access || !$has_guest_page_access) {
                if (!$has_member_category_access || !$has_member_page_access) {
                    fwrite($sitemap_file, '
                                        <news:access>Subscription</news:access>
                            ');
                } else {
                    fwrite($sitemap_file, '
                                        <news:access>Registration</news:access>
                            ');
                }
            }
            $genres = array();
            if ($is_blog) {
                $genres[] = 'Blog';
            }
            $_categories = array_merge(array($row['news_category']), collapse_1d_complexity('news_entry_category', $GLOBALS['SITE_DB']->query_select('news_category_entries', array('news_entry_category'), array('news_entry' => $row['id']))));
            $categories = array();
            foreach ($_categories as $category) {
                $categories[] = str_replace(' ', '', get_translated_text($GLOBALS['SITE_DB']->query_select_value('news_categories', 'nc_title', array('id' => $category))));
            }
            foreach (array('PressRelease', 'Satire', 'OpEd', 'Opinion', 'UserGenerated') as $category) {
                if (in_array($category, $categories)) {
                    $genres[] = $category;
                }
            }
            fwrite($sitemap_file, '
                                        <news:genres>' . xmlentities(implode(', ', $genres)) . '</news:genres>
                                        <news:publication_date>' . xmlentities(date('Y-m-d', $row['date_and_time']) . 'T' . date('H:i:s+00:00', $row['date_and_time'])) . '</news:publication_date>
                                        <news:title>' . xmlentities(get_translated_text($row['title'])) . '</news:title>
                                        <news:keywords>' . xmlentities(((trim($meta[0], ' ,') == '') ? '' : preg_replace('#\s*,\s*#', ', ', $meta[0]))) . '</news:keywords>
            ');
            if ($site_location !== null) {
                fwrite($sitemap_file, '
                                        <news:geo_locations>' . xmlentities($site_location) . '</news:geo_locations>
                    ');
            }
            fwrite($sitemap_file, '
                            </news:news>
              </url>
            ');
        }

        $start += $max;
    } while ((count($rows) != 0) && ($start < 500/*Let's not go nuts - older news is still in the regular sitemap!*/));

    fwrite($sitemap_file, '
        </urlset>
');

    flock($sitemap_file, LOCK_UN);
    fclose($sitemap_file);
    require_code('files');
    sync_file($path);
    fix_permissions($path);

    $target_url = get_custom_base_url() . '/data_custom/sitemaps/news_sitemap.xml';

    if (function_exists('gzencode')) {
        require_code('files');
        cms_file_put_contents_safe($path . '.gz', gzencode(file_get_contents($path), -1), FILE_WRITE_FIX_PERMISSIONS | FILE_WRITE_SYNC_FILE);
        $path .= '.gz';
        $target_url .= '.gz';
    }

    require_code('sitemap_xml');
    ping_sitemap_xml($target_url);

    cms_profile_end_for('build_news_sitemap');
}
