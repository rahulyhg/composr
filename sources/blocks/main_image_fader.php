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
 * @package    galleries
 */

/**
 * Block class.
 */
class Block_main_image_fader
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'time', 'zone', 'order', 'as_guest');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = 'array(array_key_exists(\'as_guest\',$map)?($map[\'as_guest\']==\'1\'):false,array_key_exists(\'order\',$map)?$map[\'order\']:\'\',array_key_exists(\'time\',$map)?intval($map[\'time\']):8000,array_key_exists(\'zone\',$map)?$map[\'zone\']:get_module_zone(\'galleries\'),array_key_exists(\'param\',$map)?$map[\'param\']:\'\')';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS;
        if (addon_installed('content_privacy')) {
            $info['special_cache_flags'] |= CACHE_AGAINST_MEMBER;
        }
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        require_css('galleries');
        require_lang('galleries');
        require_code('galleries');
        require_javascript('core_rich_media');

        $block_id = get_block_id($map);

        if (empty($map['param'])) {
            $cat = $GLOBALS['SITE_DB']->query_select_value('images', 'cat', null, 'GROUP BY cat ORDER BY COUNT(*) DESC');
            if ($cat === null) {
                $cat = 'root';
            }
        } else {
            $cat = $map['param'];
        }

        $mill = array_key_exists('time', $map) ? intval($map['time']) : 8000; // milliseconds between animations
        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('galleries');
        $order = array_key_exists('order', $map) ? $map['order'] : '';

        if ($cat == 'root') {
            $cat_select = db_string_equal_to('cat', $cat);
        } else {
            require_code('selectcode');
            $cat_select = selectcode_to_sqlfragment($cat, 'cat', 'galleries', 'parent_id', 'cat', 'name', false, false);
        }

        $images = array();
        $images_full = array();
        $titles = array();
        $html = array();

        $extra_join_image = '';
        $extra_join_video = '';
        $extra_where_image = '';
        $extra_where_video = '';

        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            $as_guest = array_key_exists('as_guest', $map) ? ($map['as_guest'] == '1') : false;
            $viewing_member_id = $as_guest ? $GLOBALS['FORUM_DRIVER']->get_guest_id() : mixed();
            list($privacy_join_video, $privacy_where_video) = get_privacy_where_clause('video', 'r', $viewing_member_id);
            list($privacy_join_image, $privacy_where_image) = get_privacy_where_clause('image', 'r', $viewing_member_id);
            $extra_join_image .= $privacy_join_image;
            $extra_join_video .= $privacy_join_video;
            $extra_where_image .= $privacy_where_image;
            $extra_where_video .= $privacy_where_video;
        }

        if (get_option('filter_regions') == '1') {
            require_code('locations');
            $extra_where_image .= sql_region_filter('image', 'r.id');
            $extra_where_video .= sql_region_filter('video', 'r.id');
        }

        $image_rows = $GLOBALS['SITE_DB']->query('SELECT r.*,\'image\' AS content_type FROM ' . get_table_prefix() . 'images r ' . $extra_join_image . ' WHERE ' . $cat_select . $extra_where_image . ' AND validated=1 ORDER BY add_date ASC', 100/*reasonable amount*/, 0, false, true, array('title' => 'SHORT_TRANS', 'description' => 'LONG_TRANS'));
        $video_rows = $GLOBALS['SITE_DB']->query('SELECT r.*,thumb_url AS url,\'video\' AS content_type FROM ' . get_table_prefix() . 'videos r ' . $extra_join_video . ' WHERE ' . $cat_select . $extra_where_video . ' AND validated=1 ORDER BY add_date ASC', 100/*reasonable amount*/, 0, false, true, array('title' => 'SHORT_TRANS', 'description' => 'LONG_TRANS'));
        $all_rows = array();
        if ($order != '') {
            foreach (explode(',', $order) as $o) {
                $num = substr($o, 1);

                if (is_numeric($num)) {
                    switch (substr($o, 0, 1)) {
                        case 'i':
                            foreach ($image_rows as $i => $row) {
                                if ($row['id'] == intval($num)) {
                                    $all_rows[] = $row;
                                    unset($image_rows[$i]);
                                }
                            }
                            break;
                        case 'v':
                            foreach ($video_rows as $i => $row) {
                                if ($row['id'] == intval($num)) {
                                    $all_rows[] = $row;
                                    unset($video_rows[$i]);
                                }
                            }
                            break;
                    }
                }
            }
        }
        $all_rows = array_merge($all_rows, $image_rows, $video_rows);
        require_code('images');
        foreach ($all_rows as $row) {
            $url = $row['thumb_url'];
            if (url_is_local($url)) {
                $url = get_custom_base_url() . '/' . $url;
            }
            $images[] = $url;

            $full_url = $row['url'];
            if (url_is_local($full_url)) {
                $full_url = get_custom_base_url() . '/' . $full_url;
            }
            $images_full[] = $full_url;

            $titles[] = get_translated_text($row['title']);
            $just_media_row = db_map_restrict($row, array('id', 'description'));
            $html[] = get_translated_tempcode($row['content_type'] . 's', $just_media_row, 'description');
        }

        if (count($images) == 0) {
            $submit_url = mixed();
            if ((has_actual_page_access(null, 'cms_galleries', null, null)) && (has_submit_permission('mid', get_member(), get_ip_address(), 'cms_galleries', array('galleries', $cat))) && (can_submit_to_gallery($cat))) {
                $submit_url = build_url(array('page' => 'cms_galleries', 'type' => 'add', 'cat' => $cat, 'redirect' => SELF_REDIRECT), get_module_zone('cms_galleries'));
            }
            return do_template('BLOCK_NO_ENTRIES', array(
                '_GUID' => 'aa84d65b8dd134ba6cd7b1b7bde99de2',
                'BLOCK_ID' => $block_id,
                'HIGH' => false,
                'TITLE' => do_lang_tempcode('GALLERY'),
                'MESSAGE' => do_lang_tempcode('NO_ENTRIES', 'image'),
                'ADD_NAME' => do_lang_tempcode('ADD_IMAGE'),
                'SUBMIT_URL' => $submit_url,
            ));
        }

        $nice_cat = str_replace('*', '', $cat);
        if (preg_match('#^[' . URL_CONTENT_REGEXP . ']+$#', $nice_cat) == 0) {
            $nice_cat = 'root';
        }
        $gallery_url = build_url(array('page' => 'galleries', 'type' => 'browse', 'id' => $nice_cat), $zone);

        return do_template('BLOCK_MAIN_IMAGE_FADER', array(
            '_GUID' => '92337749fa084393a97f97eedbcf81f6',
            'BLOCK_ID' => $block_id,
            'GALLERY_URL' => $gallery_url,
            'PREVIOUS_URL' => $images[count($images) - 1],
            'PREVIOUS_URL_FULL' => $images[count($images_full) - 1],
            'FIRST_URL' => $images[0],
            'FIRST_URL_FULL' => $images_full[0],
            'NEXT_URL' => isset($images[1]) ? $images[1] : '',
            'NEXT_URL_FULL' => isset($images_full[1]) ? $images_full[1] : '',
            'IMAGES' => $images,
            'IMAGES_FULL' => $images_full,
            'TITLES' => $titles,
            'HTML' => $html,
            'MILL' => strval($mill),
        ));
    }
}
