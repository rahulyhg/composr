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
 * @package    core_rich_media
 */

/**
 * Hook class.
 */
class Hook_media_rendering_email
{
    /**
     * Get the label for this media rendering type.
     *
     * @return string The label
     */
    public function get_type_label()
    {
        require_lang('comcode');
        return do_lang('MEDIA_TYPE_' . preg_replace('#^Hook_media_rendering_#', '', __CLASS__));
    }

    /**
     * Find the media types this hook serves.
     *
     * @return integer The media type(s), as a bitmask
     */
    public function get_media_type()
    {
        return MEDIA_TYPE_ALL;
    }

    /**
     * See if we can recognise this mime type.
     *
     * @param  ID_TEXT $mime_type The mime type
     * @return integer Recognition precedence
     */
    public function recognises_mime_type($mime_type)
    {
        return MEDIA_RECOG_PRECEDENCE_NONE;
    }

    /**
     * See if we can recognise this URL pattern.
     *
     * @param  URLPATH $url URL to pattern match
     * @return integer Recognition precedence
     */
    public function recognises_url($url)
    {
        return MEDIA_RECOG_PRECEDENCE_NONE;
    }

    /**
     * Provide code to display what is at the URL, in the most appropriate way.
     *
     * @param  mixed $url URL to render
     * @param  mixed $url_safe URL to render (no sessions etc)
     * @param  array $attributes Attributes (e.g. width, height, length)
     * @param  boolean $as_admin Whether there are admin privileges, to render dangerous media types
     * @param  ?MEMBER $source_member Member to run as (null: current member)
     * @return Tempcode Rendered version
     */
    public function render($url, $url_safe, $attributes, $as_admin = false, $source_member = null)
    {
        if (is_object($url)) {
            $url = $url->evaluate();
        }

        require_code('mail');
        global $EMAIL_ATTACHMENTS;
        if (url_is_local($url)) {
            $url = get_custom_base_url() . '/' . $url;
        }
        if ((!array_key_exists('filename', $attributes)) || ($attributes['filename'] == '')) {
            $attributes['filename'] = urldecode(basename($url));
        }
        $EMAIL_ATTACHMENTS[$url] = $attributes['filename'];

        return new Tempcode();
    }
}
