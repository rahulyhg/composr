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
 * @package    galleries
 */

/**
 * Hook class.
 */
class Hook_symbol_GALLERY_VIDEO_FOR_URL
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        $value = '';

        if (array_key_exists(0, $param)) {
            $url = $param[0];
            if (strpos($url, ' ') !== false) {
                $url = rawurlencode(str_replace('%2F', '/', $url)); // In case was not properly encoded as URL
            }
            if (is_file(get_custom_file_base() . '/' . rawurldecode($url))) {
                $test = $GLOBALS['SITE_DB']->query_select_value_if_there('videos', 'id', array('url' => $url));
                if ($test !== null) {
                    $value = strval($test);
                } else {
                    require_code('galleries2');
                    require_code('exif');

                    require_lang('galleries');

                    $file = rawurldecode(basename($url));

                    $ret = get_video_details(get_custom_file_base() . '/' . rawurldecode($url), $file, true);
                    if ($ret !== false) {
                        list($width, $height, $length) = $ret;
                        if ($width === null) {
                            $width = intval(get_option('default_video_width'));
                        }
                        if ($height === null) {
                            $height = intval(get_option('default_video_height'));
                        }
                        if ($length === null) {
                            $length = 0;
                        }
                        $exif = get_exif_data(get_custom_file_base() . '/' . rawurldecode($url), $file);

                        $title = array_key_exists(1, $param) ? $param[1] : '';
                        if ($title == '') {
                            $title = $exif['UserComment'];
                        }

                        $cat = array_key_exists(2, $param) ? $param[1] : '';
                        if ($cat == '') {
                            $cat = 'root';
                        }

                        $allow_rating = array_key_exists(3, $param) ? ((intval($param[3]) == 1) ? 1 : 0) : 1;
                        $allow_comments = array_key_exists(4, $param) ? ((intval($param[4]) == 1) ? 1 : 0) : 1;
                        $allow_trackbacks = array_key_exists(5, $param) ? ((intval($param[5]) == 1) ? 1 : 0) : 1;

                        $id = add_video($title, $cat, '', $url, '', 1, $allow_rating, $allow_comments, $allow_trackbacks, do_lang('VIDEO_WAS_AUTO_IMPORTED'), $length, $width, $height);
                        store_exif('video', strval($id), $exif);
                    }
                }
            }
        }

        return $value;
    }
}
