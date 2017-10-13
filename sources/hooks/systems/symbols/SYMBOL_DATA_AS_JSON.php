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
        global $ZONE;

        require_code('global2');
        require_code('symbols');

        // TODO: As stuff is removed from here, re-review the symbols.php/symbols2.php split to ensure it doesn't load symbols2.php on a typical page request yet symbols.php is minimal

        $lang = user_lang();
        $value = array(
            'PAGE'              => ecv_PAGE($lang, [], []),
            'ZONE'              => ecv_ZONE($lang, [], []),
            'MEMBER'            => ecv_MEMBER($lang, [], []),
            'IS_GUEST'          => ecv_IS_GUEST($lang, [], []),
            'USERNAME'          => ecv_USERNAME($lang, [], []),
            'DATE_AND_TIME'     => ecv_DATE_TIME($lang, [], []),
            'DATE'              => ecv_DATE($lang, [], []),
            'TIME'              => ecv_TIME($lang, [], []),
            'FROM_TIMESTAMP'    => ecv_FROM_TIMESTAMP($lang, [], []),
            'HIDE_HELP_PANEL'   => ecv_HIDE_HELP_PANEL($lang, [], []),
            'MOBILE'            => ecv_MOBILE($lang, [], []),
            'THEME'             => ecv_THEME($lang, [], []),
            'JS_ON'             => ecv_JS_ON($lang, [], []),
            'LANG'              => ecv_LANG($lang, [], []),
            'DEV_MODE'          => ecv_DEV_MODE($lang, [], []),
            'HTTP_STATUS_CODE'  => ecv_HTTP_STATUS_CODE($lang, [], []),
            'KEEP'              => ecv_KEEP($lang, [], []),
            'FORCE_PREVIEWS'    => ecv_FORCE_PREVIEWS($lang, [], []),
            'SITE_NAME'         => ecv_SITE_NAME($lang, [], []),
            'BRAND_NAME'        => ecv_BRAND_NAME($lang, [], []),
            'IS_STAFF'          => ecv_IS_STAFF($lang, [], []),
            'IS_ADMIN'          => ecv_IS_ADMIN($lang, [], []),
            'IS_HTTPAUTH_LOGIN' => ecv_IS_HTTPAUTH_LOGIN($lang, [], []),
            'IS_A_COOKIE_LOGIN' => ecv_IS_A_COOKIE_LOGIN($lang, [], []),
            'INLINE_STATS'      => ecv_INLINE_STATS($lang, [], []),
            'CSP_NONCE'         => ecv_CSP_NONCE($lang, [], []),
            'RUNNING_SCRIPT'    => current_script(),
        );

        require_code('urls');

        $value['zone_default_page'] = ($ZONE !== null) ? $ZONE['zone_default_page'] : '';
        $value['sees_javascript_error_alerts'] = has_privilege(get_member(), 'sees_javascript_error_alerts');
        $value['can_try_url_schemes'] = can_try_url_schemes();
        $value['staff_tooltips_url_patterns'] = $this->staff_tooltips_url_patterns($value['IS_STAFF'] === '1');

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }

    /**
     * Find URL patterns staff tooltips can be added on.
     *
     * @param  boolean $is_staff If the current user is a staff member
     * @return array
     */
    private function staff_tooltips_url_patterns($is_staff)
    {
        $url_patterns = array();
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
                $url = build_url($attributes, $zone, array(), false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
            if (isset($info['edit_page_link_pattern'])) {
                list($zone, $attributes,) = page_link_decode($info['edit_page_link_pattern']);
                $url = build_url($attributes, $zone, array(), false, false, true);
                $pattern = $this->_escape_url_pattern_for_js_regex($url->evaluate());
                $hook = $content_type;
                $url_patterns[$pattern] = $hook;
            }
        }

        return $url_patterns;
    }

    /**
     * Turn a page-link pattern into a regexp.
     *
     * @param  string $pattern Pattern
     * @return string
     */
    public function _escape_url_pattern_for_js_regex($pattern)
    {
        $pattern = str_replace('/', '\\/', $pattern);
        $pattern = str_replace('?', '\\?', $pattern);
        $pattern = str_replace('_WILD\\/', '([^&]*)\\/?', $pattern);
        $pattern = str_replace('_WILD', '([^&]*)', $pattern);

        return '^' . $pattern;
    }
}