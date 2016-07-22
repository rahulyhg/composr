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
 * Hook class.
 */
class Hook_preview_video
{
    /**
     * Find whether this preview hook applies.
     *
     * @return array Triplet: Whether it applies, the attachment ID type (may be null), whether the forum DB is used [optional]
     */
    public function applies()
    {
        require_code('uploads');
        $applies = (get_page_name() == 'cms_galleries') && ((get_param_string('type', '') == 'add_other') || (get_param_string('type', '') == '_edit_other'));
        return array($applies, null, false);
    }

    /**
     * Run function for preview hooks.
     *
     * @return array A pair: The preview, the updated post Comcode (may be null)
     */
    public function run()
    {
        require_code('uploads');

        $cat = post_param_string('cat');

        $urls = get_url('url', 'file', 'uploads/galleries', 0, CMS_UPLOAD_VIDEO, true, '', 'file2');
        if ($urls[0] == '') {
            if (post_param_integer('id', null) !== null) {
                $rows = $GLOBALS['SITE_DB']->query_select('videos', array('url', 'thumb_url'), array('id' => post_param_integer('id')), '', 1);
                $urls = $rows[0];

                $url = $urls['url'];
                $thumb_url = $urls['thumb_url'];
            } else {
                warn_exit(do_lang_tempcode('IMPROPERLY_FILLED_IN_UPLOAD'));
            }
        } else {
            $url = $urls[0];
            $thumb_url = $urls[1];
        }

        $length = post_param_integer('video_length', null);
        $width = post_param_integer('video_width', null);
        $height = post_param_integer('video_height', null);
        require_code('galleries');
        require_code('galleries2');
        $test = is_file(get_custom_base_url() . '/' . rawurldecode($url)) ? get_video_details(get_custom_base_url() . '/' . rawurldecode($url), basename($url)) : false;
        if ($test !== false) {
            list($_width, $_height, $_length) = $test;
        } else {
            list($_width, $_height, $_length) = array(intval(get_option('video_width_setting')), intval(get_option('video_height_setting')), 0);
        }
        if ($length === null) {
            $length = $_length;
        }
        if ($width === null) {
            $width = $_width;
        }
        if ($height === null) {
            $height = $_height;
        }
        $preview = show_gallery_video_media($url, $thumb_url, $width, $height, $length, get_member());

        return array($preview, null);
    }
}
