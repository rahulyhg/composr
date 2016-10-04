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
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_symbol_SYMBOL_DATA_AS_JSON
{
    /**
     * Run function for symbol hooks. Searches for tasks to perform.
     *
     * @param  array $param Symbol parameters
     * @return string Result
     */
    public function run($param)
    {
        require_code('symbols');
        require_code('symbols2');

        $lang = user_lang();
        $value = array(
            'PAGE_TITLE'          => ecv_PAGE_TITLE($lang, [], []),
            'MEMBER'              => ecv_MEMBER($lang, [], []),
            'IS_GUEST'            => ecv_IS_GUEST($lang, [], []),
            'USERNAME'            => ecv_USERNAME($lang, [], []),
            'AVATAR'              => ecv_AVATAR($lang, [], []),
            'MEMBER_EMAIL'        => ecv_MEMBER_EMAIL($lang, [], []),
            'PHOTO'               => ecv_PHOTO($lang, [], []),
            'MEMBER_PROFILE_URL'  => ecv_MEMBER_PROFILE_URL($lang, [], []),
            'DATE_AND_TIME'       => ecv_DATE_TIME($lang, [], []),
            'DATE'                => ecv_DATE($lang, [], []),
            'TIME'                => ecv_TIME($lang, [], []),
            'FROM_TIMESTAMP'      => ecv_FROM_TIMESTAMP($lang, [], []),
            'HIDE_HELP_PANEL'     => ecv_HIDE_HELP_PANEL($lang, [], []),
            'MOBILE'              => ecv2_MOBILE($lang, [], []),
            'THEME'               => ecv2_THEME($lang, [], []),
            'JS_ON'               => ecv_JS_ON($lang, [], []),
            'LANG'                => ecv2_LANG($lang, [], []),
            'BROWSER_UA'          => ecv2_BROWSER_UA($lang, [], []),
            'OS'                  => ecv2_OS($lang, [], []),
            'DEV_MODE'            => ecv_DEV_MODE($lang, [], []),
            'USER_AGENT'          => ecv2_USER_AGENT($lang, [], []),
            'IP_ADDRESS'          => ecv2_IP_ADDRESS($lang, [], []),
            'TIMEZONE'            => ecv2_TIMEZONE($lang, [], []),
            'HTTP_STATUS_CODE'    => ecv2_HTTP_STATUS_CODE($lang, [], []),
            'CHARSET'             => ecv2_CHARSET($lang, [], []),
            'KEEP'                => ecv_KEEP($lang, [], []),
            'FORCE_PREVIEWS'      => ecv_FORCE_PREVIEWS($lang, [], []),
            'PREVIEW_URL'         => ecv_PREVIEW_URL($lang, [], []),
            'SITE_NAME'           => ecv2_SITE_NAME($lang, [], []),
            'COPYRIGHT'           => ecv2_COPYRIGHT($lang, [], []),
            'DOMAIN'              => ecv2_DOMAIN($lang, [], []),
            'FORUM_BASE_URL'      => ecv2_FORUM_BASE_URL($lang, [], []),
            'BASE_URL'            => ecv2_BASE_URL($lang, [], []),
            'CUSTOM_BASE_URL'     => ecv2_CUSTOM_BASE_URL($lang, [], []),
            'BASE_URL_NOHTTP'     => ecv2_BASE_URL_NOHTTP($lang, [], []),
            'CUSTOM_BASE_URL_NOHTTP' => ecv2_CUSTOM_BASE_URL_NOHTTP($lang, [], []),
            'BRAND_NAME'          => ecv2_BRAND_NAME($lang, [], []),
            'IS_STAFF'            => ecv_IS_STAFF($lang, [], []),
            'IS_ADMIN'            => ecv_IS_ADMIN($lang, [], []),
            'VERSION'             => ecv2_VERSION($lang, [], []),
            'COOKIE_PATH'         => ecv2_COOKIE_PATH($lang, [], []),
            'COOKIE_DOMAIN'       => ecv2_COOKIE_DOMAIN($lang, [], []),
            'IS_HTTPAUTH_LOGIN'   => ecv_IS_HTTPAUTH_LOGIN($lang, [], []),
            'IS_A_COOKIE_LOGIN'   => ecv2_IS_A_COOKIE_LOGIN($lang, [], []),
            'SESSION_COOKIE_NAME' => ecv2_SESSION_COOKIE_NAME($lang, [], []),
            'GROUP_ID'            => ecv2_GROUP_ID($lang, [], []),
        );

        require_code('config');
        $value['CONFIG_OPTION'] = [
            'thumbWidth'        => get_option('thumb_width'),
            'jsOverlays'        => get_option('js_overlays'),
            'jsCaptcha'         => get_option('js_captcha'),
            'googleAnalytics'   => get_option('google_analytics'),
            'longGoogleCookies' => get_option('long_google_cookies'),
            'editarea'          => get_option('editarea'),
            'enableAnimations'  => get_option('enable_animations'),
            'detectJavascript'  => get_option('detect_javascript'),
            'isOnTimezoneDetection' => get_option('is_on_timezone_detection'),
            'fixedWidth'        => get_option('fixed_width'),
            'infiniteScrolling' => get_option('infinite_scrolling'),
            'wysiwyg'           => get_option('wysiwyg'),
            'eagerWysiwyg'      => get_option('eager_wysiwyg'),
            'simplifiedAttachmentsUi'   => get_option('simplified_attachments_ui'),
            'showInlineStats'           => get_option('show_inline_stats'),
            'notificationDesktopAlerts' => get_option('notification_desktop_alerts'),
            'enableThemeImgButtons' => get_option('enable_theme_img_buttons'),
            'enablePreviews' => get_option('enable_previews'),
        ];

        $value['VALUE_OPTION'] = [
            'jsKeepParams' => get_value('js_keep_params'),
            'commercialSpellchecker' => get_value('commercial_spellchecker'),
        ];

        $value['HAS_PRIVILEGE'] = [
            'seesJavascriptErrorAlerts' =>  has_privilege(get_member(), 'sees_javascript_error_alerts')
        ];

        require_code('urls');
        $value['EXTRA'] = [
            'canTryUrlSchemes' => can_try_url_schemes(),
            'staffTooltipsUrlPatterns' => $this->staff_tooltips_url_patterns($value['IS_STAFF'] === '1')
        ];

        return json_encode($value);
    }

    public function staff_tooltips_url_patterns($is_staff) {
        $url_patterns = [];
        if (!$is_staff) {
            return $url_patterns;
        }

        require_code('content');
        $cma_hooks = find_all_hooks('systems', 'content_meta_aware');
        foreach (array_keys($cma_hooks) as $content_type) {
            $content_type_ob = get_content_object($content_type);

            if (!isset($content_type_ob)) {
                continue;
            }

            $info = $content_type_ob->info();
            if (isset($info['view_page_link_pattern'])) {
                list($zone, $attributes,) = page_link_decode($info['view_page_link_pattern']);
                $url = build_url($attributes, $zone, null, false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
            if (isset($info['edit_page_link_pattern'])) {
                list($zone, $attributes,) = page_link_decode($info['edit_page_link_pattern']);
                $url = build_url($attributes, $zone, null, false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
        }

        return $url_patterns;
    }

    public function _escape_url_pattern_for_js_regex($pattern) {
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = str_replace('?', '\\?', $pattern);
        $pattern = str_replace('_WILD\\/', '([^&]*)\\/?', $pattern);
        $pattern = str_replace('_WILD', '([^&]*)', $pattern);

        return '^' . $pattern;
    }
}
