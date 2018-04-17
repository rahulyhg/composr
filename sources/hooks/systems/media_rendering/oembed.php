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
 * @package    core_rich_media
 */
/*
Notes...
 - The cache_age property is not supported. It would significantly complicate the API and hurt performance, and we don't know a use case for it. The spec says it is optional to support.
 - Link/semantic-webpage rendering will not use passed description parameter, etc. This is intentional: the normal flow of rendering through a standardised media template is not used.
*/

/**
 * Hook class.
 */
class Hook_media_rendering_oembed extends Media_renderer_with_fallback
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
     * @param  ?array $meta_details The media signature, so we can go on this on top of the mime-type (null: not known)
     * @return integer Recognition precedence
     */
    public function recognises_mime_type($mime_type, $meta_details = null)
    {
        if ($mime_type == 'text/html' || $mime_type == 'application/xhtml+xml') {
            if ($meta_details !== null) {
                if (($meta_details['t_json_discovery'] != '') || ($meta_details['t_xml_discovery'] != '')) {
                    return MEDIA_RECOG_PRECEDENCE_MEDIUM;
                }
            }
        }
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
        if (looks_like_url($url) && $this->_find_oembed_endpoint($url) !== null) {
            return MEDIA_RECOG_PRECEDENCE_MEDIUM;
        }
        return MEDIA_RECOG_PRECEDENCE_NONE;
    }

    /**
     * If we can handle this URL, get the thumbnail URL.
     *
     * @param  URLPATH $src_url Video URL
     * @return ?string The thumbnail URL (null: no match).
     */
    public function get_video_thumbnail($src_url)
    {
        $data = $this->get_oembed_data_result($src_url, array());
        if ((!is_null($data)) && (isset($data['thumbnail_url']))) {
            return $data['thumbnail_url'];
        }
        return null;
    }

    /**
     * Do an oEmbed lookup.
     *
     * @param  URLPATH $url URL to render
     * @param  array $attributes Attributes (e.g. width, height)
     * @return ?array Fully parsed/validated oEmbed result (null: fail)
     */
    public function get_oembed_data_result($url, $attributes)
    {
        $endpoint = $this->_find_oembed_endpoint($url);
        if ($endpoint === null) {
            return null;
        }

        // Work out the full endpoint URL to call
        if (strpos($endpoint, '?') === false) {
            $endpoint .= '?url=' . urlencode($url);
        } else {
            if ((strpos($endpoint, '?url=') === false) && (strpos($endpoint, '&url=') === false)) {
                $endpoint .= '&url=' . urlencode($url);
            }
        }
        if ((!array_key_exists('width', $attributes)) || ($attributes['width'] != '')) {
            $endpoint .= '&maxwidth=' . urlencode(array_key_exists('width', $attributes) ? $attributes['width'] : get_option('oembed_max_size'));
        }
        if ((!array_key_exists('height', $attributes)) || ($attributes['height'] != '')) {
            $endpoint .= '&maxheight=' . urlencode(array_key_exists('height', $attributes) ? $attributes['height'] : get_option('oembed_max_size'));
        }
        $format_in_path = (strpos($endpoint, '{format}') !== false);
        $preferred_format = 'json';
        if ($format_in_path) {
            $endpoint = str_replace('{format}', $preferred_format, $endpoint);
        } else {
            if (strpos($endpoint, '&format=') === false) {
                $endpoint .= '&format=' . urlencode($preferred_format);
            }
        }

        // Call endpoint
        require_code('files');
        require_code('files2');
        $result = cache_and_carry('http_download_file', array($endpoint, null, false, false, 'Composr', null, null, null, null, null, null, null, null, 2.0));
        if ($result === false || $result[4] != '200') {
            return null;
        }

        // Handle
        require_code('character_sets');
        $data = array();
        switch ($result[1]) {
            case 'text/xml':
            case 'text/xml+oembed':
                require_code('xml');
                $parsed = new CMS_simple_xml_reader($result[0]);
                list($root_tag, $root_attributes, , $this_children) = $parsed->gleamed;
                if ($root_tag == 'oembed') {
                    foreach ($this_children as $child) {
                        list($key, , $val) = $child;
                        $data[$key] = convert_to_internal_encoding($val, 'utf-8');
                    }
                }
                break;
            case 'application/json':
            case 'application/json+oembed':
            case 'text/javascript': // noembed uses this, naughty
                require_code('json');
                $_data = json_decode($result[0], true);
                if ($_data === null) {
                    return null;
                }
                $data = array();
                foreach ($_data as $key => $val) { // It's currently an object, we want an array
                    if (is_null($val)) {
                        continue;
                    }
                    if ((is_array($val)) || (is_object($val))) {
                        continue;
                    }
                    $data[$key] = is_string($val) ? convert_to_internal_encoding($val, 'utf-8') : strval($val);
                }
                break;
            default:
                return null;
        }

        // Validation
        if ((!array_key_exists('type', $data)) && (array_key_exists('thumbnail_url', $data))) { // yfrog being weird
            $data['type'] = 'link';
        }
        if (!array_key_exists('type', $data)) {
            return null; // E.g. an error result, with an "error" value - but we don't show errors as we just fall back instead
        }
        if ((!array_key_exists('thumbnail_url', $data)) && (array_key_exists('media_url', $data))) {
            $data['thumbnail_url'] = $data['media_url']; // noembed uses this, naughty
            unset($data['media_url']);
        }
        if ((array_key_exists('thumbnail_url', $data)) && (array_key_exists('url', $data)) && (strpos($data['thumbnail_url'], 'https://noembed.com/') !== false)) {
            $data['url'] = $data['thumbnail_url']; // noembed uses 'url' incorrectly, naughty
        }
        switch ($data['type']) {
            case 'photo':
                if ((!array_key_exists('url', $data)) || (!array_key_exists('width', $data)) || (!array_key_exists('height', $data))) {
                    return null;
                }
                break;

            case 'video':
                if ((!array_key_exists('width', $data)) || (!array_key_exists('height', $data))) {
                    return null;
                }
            // intentionally rolls on...
            case 'rich':
                if (!array_key_exists('html', $data)) {
                    return null;
                }

                // Check security
                $url_details = parse_url($url);
                $url_details2 = parse_url($endpoint);
                $whitelist = explode("\n", get_option('oembed_html_whitelist'));
                if ((!in_array($url_details['host'], $whitelist)) && (!in_array($url_details2['host'], $whitelist)) && (!in_array(preg_replace('#^www\.#', '', $url_details['host']), $whitelist))) {
                    /* We could do this but it's not perfect, it still has some level of trust
                    require_code('comcode_compiler');
                    $len = strlen($data['html']);
                    filter_html(false, $GLOBALS['FORUM_DRIVER']->get_guest_id(), 0, $len, $data['html'], true, false);
                    */
                    $data['html'] = strip_tags($data['html']);
                }

                break;

            case 'link':
                break;

            default:
                return null;
        }

        // See if we can improve things
        if ($data['type'] == 'photo') {
            $matches = array();

            // Flickr
            if (preg_match('#^(https?://[^/]+\.staticflickr\.com/.*_)[nm](\.jpg)$#', $data['url'], $matches) != 0) {
                unset($data['thumb_url']);
                $data['url'] = $matches[1] . 'b' . $matches[2];
                $w = $data['width'];
                $h = $data['height'];
                $data['width'] = '1024';
                $data['height'] = strval(intval(1024.0 * floatval($h) / floatval($w)));
            } // Instagram
            elseif (preg_match('#^(https?://[^/]+\.instagram\.com/.*_)[as](\.jpg)$#', $data['url'], $matches) != 0) {
                unset($data['thumb_url']);
                $data['url'] = $matches[1] . 'n' . $matches[2];
                $w = $data['width'];
                $h = $data['height'];
                $data['width'] = '640';
                $data['height'] = strval(intval(640.0 * floatval($h) / floatval($w)));
            }
        } elseif ($data['type'] == 'video') {
            // Instagram
            if ((preg_match('#^(https?://([^/]+\.)?instagram\.com/.*/)$#', $url, $matches) != 0) && ($data['html'] == '')) {
                $data['html'] = '<iframe src="' . escape_html($url) . 'embed/" width="612" height="710" frameborder="0" scrolling="no" allowtransparency="true"></iframe>';
            }
        }

        return $data;
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

        $data = $this->get_oembed_data_result($url, $attributes);
        if ($data === null) {
            return $this->_fallback_render($url, $attributes, $source_member);
        }

        if ((peek_media_mode() & MEDIA_LOWFI) != 0) {
            if (isset($data['thumbnail_url'])) {
                $data['type'] = 'photo';
                $data['url'] = $data['thumbnail_url'];
            } else {
                return $this->_fallback_render($url, $attributes, $source_member);
            }
        }

        if ((!$as_admin) && (!has_privilege(($source_member === null) ? get_member() : $source_member, 'search_engine_links'))) {
            $rel = 'nofollow';
        } else {
            $rel = null;
        }

        switch ($data['type']) {
            case 'photo':
                $map = array(
                    'click_url' => $url,
                );
                if (isset($data['width'])) {
                    unset($attributes['width']);
                    $map['width'] = $data['width'];
                }
                if (isset($data['height'])) {
                    unset($attributes['height']);
                    $map['height'] = $data['height'];
                }
                $url = $data['url']; // NB: This will also have been constrained to the maxwidth/maxheight (at least it is for Flickr)
                /* Cannot control the size, so we'll make our own inside image_websafe
                if (array_key_exists('thumbnail_url', $data)) {
                    $map['thumb_url'] = $data['thumbnail_url'];
                }
                if (array_key_exists('thumbnail_width', $data)) {
                    $map['width'] = $data['thumbnail_width'];
                }
                if (array_key_exists('thumbnail_height', $data)) {
                    $map['height'] = $data['thumbnail_height'];
                }
                */
                if (array_key_exists('description', $data)) {
                    $map['description'] = $data['description']; // not official, but embed.ly has it
                } elseif (array_key_exists('title', $data)) {
                    $map['description'] = $data['title'];
                }
                /* $url should be the full image not to view the resource, so we don't need to trick the mime type
                require_code('mime_types');
                require_code('files');
                $map['mime_type'] = get_mime_type(get_file_extension($map['thumb_url']));
                */
                require_code('media_renderer');
                return render_media_url($url, $url_safe, $attributes + $map, false, $source_member, MEDIA_TYPE_ALL, 'image_websafe');

            case 'video':
            case 'rich':
                return do_template('MEDIA_WEBPAGE_OEMBED_' . strtoupper($data['type']), array(
                    'TITLE' => array_key_exists('title', $data) ? $data['title'] : '',
                    'HTML' => $data['html'],
                    'WIDTH' => array_key_exists('width', $data) ? $data['width'] : '',
                    'HEIGHT' => array_key_exists('height', $data) ? $data['height'] : '',
                    'URL' => $url,
                    'REL' => $rel,
                ));

            case 'link':
                if (!array_key_exists('thumbnail_url', $data)) {
                    return $this->_fallback_render($url, $attributes, $source_member, array_key_exists('title', $data) ? $data['title'] : '');
                }

                // embed.ly and Wordpress may show thumbnail details within a "link" type
                return do_template('MEDIA_WEBPAGE_SEMANTIC', array(
                    '_GUID' => '58ab7a83f5671bcfd9587ca8d589441c',
                    'TITLE' => array_key_exists('title', $attributes) ? $attributes['title'] : '', // not official, but embed.ly has it
                    'META_TITLE' => array_key_exists('title', $data) ? $data['title'] : '', // not official, but embed.ly has it
                    'DESCRIPTION' => array_key_exists('description', $data) ? $data['description'] : '',
                    'IMAGE_URL' => $data['thumbnail_url'],
                    'URL' => $url,
                    'WIDTH' => ((array_key_exists('thumbnail_width', $attributes)) && ($attributes['thumbnail_width'] != '')) ? $attributes['thumbnail_width'] : get_option('thumb_width'),
                    'HEIGHT' => ((array_key_exists('thumbnail_height', $attributes)) && ($attributes['thumbnail_height'] != '')) ? $attributes['thumbnail_height'] : get_option('thumb_width'),
                    'REL' => $rel,
                ));
        }

        // Should not get here
        return new Tempcode();
    }

    /**
     * Provide code to display what is at the URL, when we fail to render with oEmbed.
     *
     * @param  mixed $url URL to render
     * @param  array $attributes Attributes (e.g. width, height, length)
     * @param  ?MEMBER $source_member Member to run as (null: current member)
     * @param  string $link_captions_title Text to show the link with
     * @return Tempcode Rendered version
     */
    public function _fallback_render($url, $attributes, $source_member, $link_captions_title = '')
    {
        if ($link_captions_title == '') {
            require_code('files2');
            $meta_details = get_webpage_meta_details($url);
            $link_captions_title = $meta_details['t_title'];
            if ($link_captions_title == '') {
                $link_captions_title = $url;
            }
        }

        require_code('comcode_renderer');
        if (is_null($source_member)) {
            $source_member = get_member();
        }
        $comcode = '';
        $url_tempcode = new Tempcode();
        $url_tempcode->attach($url);
        return _do_tags_comcode('url', array('param' => $link_captions_title), $url_tempcode, false, '', 0, $source_member, false, $GLOBALS['SITE_DB'], $comcode, false, false);
    }

    /**
     * Find an oEmbed endpoint for a URL.
     *
     * @param  URLPATH $url URL to find the oEmbed endpoint for
     * @return ?URLPATH Endpoint UR (null: none found)
     */
    public function _find_oembed_endpoint($url)
    {
        // Hard-coded
        $_oembed_manual_patterns = get_option('oembed_manual_patterns');
        $oembed_manual_patterns = explode("\n", $_oembed_manual_patterns);
        foreach ($oembed_manual_patterns as $oembed_manual_pattern) {
            if (strpos($oembed_manual_pattern, '=') !== false) {
                if (strpos($oembed_manual_pattern, ' = ') !== false) {
                    list($url_pattern, $endpoint) = explode(' = ', $oembed_manual_pattern, 2);
                } else { // No spaces around "=", so will use regexps to split around last "=" sign
                    $url_pattern = preg_replace('#(.*)=.*$#', '${1}', $oembed_manual_pattern); // Before last =
                    $endpoint = preg_replace('#^.*=#', '', $oembed_manual_pattern); // After last =
                }
                if (@preg_match('#^' . str_replace('#', '\#', $url_pattern) . '$#', $url) != 0) {
                    return $endpoint;
                }
            }
        }

        // Auto-discovery
        require_code('files2');
        $meta_details = get_webpage_meta_details($url);
        $mime_type = $meta_details['t_mime_type'];
        if ($mime_type == 'text/html' || $mime_type == 'application/xhtml+xml') {
            if ($meta_details['t_json_discovery'] != '') {
                return $meta_details['t_json_discovery'];
            }
            if ($meta_details['t_xml_discovery'] != '') {
                return $meta_details['t_xml_discovery'];
            }
        }

        return null;
    }
}
