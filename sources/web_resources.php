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
 * @package    core
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__web_resources()
{
    global $EARLY_SCRIPT_ORDER;
    $EARLY_SCRIPT_ORDER = array('jquery');
}

/**
 * Make sure that the given javascript file is loaded up.
 *
 * @sets_output_state
 *
 * @param  ID_TEXT $javascript The javascript file required
 */
function require_javascript($javascript)
{
    global $JAVASCRIPTS, $SMART_CACHE, $JS_OUTPUT_STARTED_LIST;

    if (empty($javascript)) {
        return;
    }

    $JAVASCRIPTS[$javascript] = true;

    if (array_key_exists($javascript, $JS_OUTPUT_STARTED_LIST)) {
        return;
    }

    $JS_OUTPUT_STARTED_LIST[$javascript] = true;

    $SMART_CACHE->append('JAVASCRIPTS', $javascript);
}

/**
 * Force a JavaScript file to be cached (ordinarily we can rely on this to be automated by require_javascript/javascript_tempcode).
 *
 * @param  string $j The javascript file required
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @param  boolean $allow_defer Allow the compilation to be deferred through a PHP call (useful for parallelising compilation)
 * @return string The path to the javascript file in the cache (blank: no file) (defer: defer compilation through a script; only possible if $allow_defer is set)
 */
function javascript_enforce($j, $theme = null, $allow_defer = false)
{
    list($minify, $https, $mobile) = _get_web_resources_env();

    if (($allow_defer) && (function_exists('can_static_cache')) && (can_static_cache())) {
        $allow_defer = false;
    }

    global $SITE_INFO;

    // Make sure the JavaScript exists
    if ($theme === null) {
        $theme = @method_exists($GLOBALS['FORUM_DRIVER'], 'get_theme') ? $GLOBALS['FORUM_DRIVER']->get_theme() : 'default';
    }
    $dir = get_custom_file_base() . '/themes/' . $theme . '/templates_cached/' . filter_naughty(user_lang());
    $js_cache_stem = $dir;
    $js_cache_stem .= '/';
    $js_cache_stub = '';
    if (!$minify) {
        $js_cache_stub .= '_non_minified';
    }
    if ($https) {
        $js_cache_stub .= '_ssl';
    }
    if ($mobile) {
        $js_cache_stub .= '_mobile';
    }
    $js_cache_stub .= '.js';
    $js_cache_path = $js_cache_stem . filter_naughty($j) . $js_cache_stub;

    global $CACHE_TEMPLATES;
    $support_smart_decaching = support_smart_decaching();
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(true);
    }
    $is_cached = (is_file($js_cache_path)) && ($CACHE_TEMPLATES || !running_script('index')/*must cache for non-index to stop getting blanked out in depended sub-script output generation and hence causing concurrency issues*/) && (!is_browser_decaching()) && ((!in_safe_mode()) || (isset($GLOBALS['SITE_INFO']['safe_mode'])));
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(false);
    }

    if (($support_smart_decaching) || (!$is_cached)) {
        $found = find_template_place($j, '', $theme, '.js', 'javascript');
        if ($found === null) {
            return '';
        }
        $theme = $found[0];
        $full_path = get_custom_file_base() . '/themes/' . $theme . $found[1] . $j . $found[2];
        if (!is_file($full_path)) {
            $full_path = get_file_base() . '/themes/' . $theme . $found[1] . $j . $found[2];
        }

        // Caching support for global.js (this is a FUDGE to hard-code this)
        if ($j == 'global') {
            $js_source_stem = get_file_base() . '/themes/default/javascript/';
            $js_source_stub = '.js';
            $deps = array(
                $js_source_stem . 'UTIL' . $js_source_stub,
                $js_source_stem . 'DOM' . $js_source_stub,
                $js_source_stem . 'CMS' . $js_source_stub,
                $js_source_stem . 'CMS_FORM' . $js_source_stub,
                $js_source_stem . 'CMS_UI' . $js_source_stub,
                $js_source_stem . 'CMS_TEMPLATES' . $js_source_stub,
                $js_source_stem . 'CMS_VIEWS' . $js_source_stub,
                $js_source_stem . 'CMS_BEHAVIORS' . $js_source_stub,
            );
            $SITE_INFO['dependency__' . $full_path] = implode(',', $deps);
        }
    }

    if (
        ($support_smart_decaching &&
            (!is_file($js_cache_path)) ||
            ((filemtime($js_cache_path) < filemtime($full_path)) && (@filemtime($full_path) <= time())) ||
            ((!empty($SITE_INFO['dependency__' . $full_path])) && (!dependencies_are_good(explode(',', $SITE_INFO['dependency__' . $full_path]), filemtime($js_cache_path))))
        ) || (!$is_cached)
    ) {
        if (@filesize($full_path) == 0) {
            return '';
        }

        if ($allow_defer) {
            return 'defer';
        }

        if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] != '1')) {
            if (!is_dir($dir)) {
                require_code('files2');
                make_missing_directory($dir);
            }
        }

        require_code('web_resources2');
        js_compile($j, $js_cache_path, $minify, $theme);
    }

    if (@intval(filesize($js_cache_path)) == 0/*@ for race condition*/) {
        return '';
    }

    return $js_cache_path;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) all the JavaScript files that have been required.
 *
 * @return Tempcode The Tempcode to tie in the JavaScript files
 */
function javascript_tempcode()
{
    global $JAVASCRIPTS, $JAVASCRIPT, $JS_OUTPUT_STARTED, $EARLY_SCRIPT_ORDER;

    $JS_OUTPUT_STARTED = true;

    $js = new Tempcode();

    list($minify, $https, $mobile) = _get_web_resources_env();

    // Fix order, so our main JavaScript, and jQuery, runs first
    if (isset($JAVASCRIPTS['global'])) {
        $arr_backup = $JAVASCRIPTS;
        $JAVASCRIPTS = array();

        foreach ($EARLY_SCRIPT_ORDER as $important_script) {
            if (isset($arr_backup[$important_script])) {
                $JAVASCRIPTS[$important_script] = true;
            }
        }

        $JAVASCRIPTS['global'] = true;
        $JAVASCRIPTS += $arr_backup;
    }

    $javascripts_to_do = $JAVASCRIPTS;
    foreach ($javascripts_to_do as $j => $do_enforce) {
        _javascript_tempcode($j, $js, null, null, null, $do_enforce);
    }

    if ($JAVASCRIPT !== null) {
        $js->attach($JAVASCRIPT);
    }

    return $js;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) for an individual JavaScript file.
 *
 * @param  ID_TEXT $j The javascript file required
 * @param  Tempcode $js Tempcode object (will be written into if appropriate)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @param  ?boolean $do_enforce Whether to generate the cached file if not already cached (null: from what is cached)
 * @ignore
 */
function _javascript_tempcode($j, &$js, $_minify = null, $_https = null, $_mobile = null, $do_enforce = true)
{
    list($minify, $https, $mobile) = _get_web_resources_env(null, $_minify, $_https, $_mobile);

    $temp = $do_enforce ? javascript_enforce($j, null, (!running_script('script')) && ($_minify === null) && ($_https === null) && ($_mobile === null)) : '';
    if (($temp != '') || (!$do_enforce)) {
        if ($temp == 'defer') {
            $GLOBALS['STATIC_CACHE_ENABLED'] = false;

            if ((function_exists('debugging_static_cache')) && (debugging_static_cache())) {
                error_log('SC: No static cache due to deferred JavaScript compilation, ' . $j);
            }

            $_theme = $GLOBALS['FORUM_DRIVER']->get_theme();
            $keep = symbol_tempcode('KEEP');
            $url = find_script('script') . '?script=' . urlencode($j) . $keep->evaluate() . '&theme=' . urlencode($_theme);
            if (get_param_string('keep_theme', null) !== $_theme) {
                $url .= '&keep_theme=' . urlencode($_theme);
            }
            if (!$minify) {
                $url .= '&keep_minify=0';
            }
            $js->attach(do_template('JAVASCRIPT_NEED_FULL', array('_GUID' => 'a2d7f0303a08b9aa9e92f8b0208ee9a7', 'URL' => $url, 'CODE' => $j)));
        } else {
            if (!$minify) {
                $j .= '_non_minified';
            }
            if ($https) {
                $j .= '_ssl';
            }
            if ($mobile) {
                $j .= '_mobile';
            }

            $support_smart_decaching = support_smart_decaching();
            $sup = ($support_smart_decaching && $temp != '' && !$GLOBALS['RECORD_TEMPLATES_USED']) ? strval(filemtime($temp)) : null; // Tweaks caching so that upgrades work without needing emptying browser cache; only runs if smart decaching is on because otherwise we won't have the mtime and don't want to introduce an extra filesystem hit

            $js->attach(do_template('JAVASCRIPT_NEED', array('_GUID' => 'b5886d9dfc4d528b7e1b0cd6f0eb1670', 'CODE' => $j, 'SUP' => $sup)));
        }
    }
}

/**
 * Make sure that the given CSS file is loaded up.
 *
 * @sets_output_state
 *
 * @param  ID_TEXT $css The CSS file required
 */
function require_css($css)
{
    global $CSSS, $SMART_CACHE, $CSS_OUTPUT_STARTED_LIST, $CSS_OUTPUT_STARTED;

    if (empty($css)) {
        return;
    }

    $CSSS[$css] = true;

    if (array_key_exists($css, $CSS_OUTPUT_STARTED_LIST)) {
        return;
    }

    $CSS_OUTPUT_STARTED_LIST[$css] = true;

    $SMART_CACHE->append('CSSS', $css);

    // Has to move into footer
    if ($CSS_OUTPUT_STARTED) {
        $value = new Tempcode();
        _css_tempcode($css, $value, $value);
        attach_to_screen_footer($value);
    }
}

/**
 * Force a CSS file to be cached.
 *
 * @param  string $c The CSS file required
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @param  boolean $allow_defer Allow the compilation to be deferred through a PHP call (useful for parallelising compilation)
 * @return string The path to the CSS file in the cache (blank: no file) (defer: defer compilation through a script; only possible if $allow_defer is set)
 */
function css_enforce($c, $theme = null, $allow_defer = false)
{
    list($minify, $https, $mobile) = _get_web_resources_env();

    if (($allow_defer) && (function_exists('can_static_cache')) && (can_static_cache())) {
        $allow_defer = false;
    }

    global $SITE_INFO;

    // Make sure the CSS file exists
    if ($theme === null) {
        $theme = @method_exists($GLOBALS['FORUM_DRIVER'], 'get_theme') ? $GLOBALS['FORUM_DRIVER']->get_theme() : 'default';
    }
    $active_theme = $theme;
    $dir = get_custom_file_base() . '/themes/' . $theme . '/templates_cached/' . filter_naughty(user_lang());
    $css_cache_path = $dir . '/' . filter_naughty($c);
    if (!$minify) {
        $css_cache_path .= '_non_minified';
    }
    if ($https) {
        $css_cache_path .= '_ssl';
    }
    if ($mobile) {
        $css_cache_path .= '_mobile';
    }
    $css_cache_path .= '.css';

    global $CACHE_TEMPLATES;
    $support_smart_decaching = support_smart_decaching();
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(true);
    }
    $is_cached = (is_file($css_cache_path)) && ($CACHE_TEMPLATES || !running_script('index')/*must cache for non-index to stop getting blanked out in depended sub-script output generation and hence causing concurrency issues*/) && (!is_browser_decaching()) && ((!in_safe_mode()) || (isset($GLOBALS['SITE_INFO']['safe_mode'])));
    if (GOOGLE_APPENGINE) {
        gae_optimistic_cache(false);
    }

    if (($support_smart_decaching) || (!$is_cached)) {
        $found = find_template_place($c, '', $theme, '.css', 'css');
        if ($found === null) {
            return '';
        }
        $theme = $found[0];
        $full_path = get_custom_file_base() . '/themes/' . $theme . $found[1] . $c . $found[2];
        if (!is_file($full_path)) {
            $full_path = get_file_base() . '/themes/' . $theme . $found[1] . $c . $found[2];
        }
    }

    if (
        ($support_smart_decaching &&
            (!is_file($css_cache_path)) ||
            ((filemtime($css_cache_path) < filemtime($full_path)) && (@filemtime($full_path) <= time())) ||
            ((!empty($SITE_INFO['dependency__' . $full_path])) && (!dependencies_are_good(explode(',', $SITE_INFO['dependency__' . $full_path]), filemtime($css_cache_path))))
        ) || (!$is_cached)
    ) {
        if (@filesize($full_path) == 0) {
            return '';
        }

        if ($allow_defer) {
            static $deferred_one = false;
            if ((!$deferred_one) && (!cms_is_writable(dirname($css_cache_path)))) {
                attach_message(do_lang_tempcode('WRITE_ERROR', escape_html(dirname($css_cache_path))), 'warn');
            }
            $deferred_one = true;

            return 'defer';
        }

        if ((!isset($SITE_INFO['no_disk_sanity_checks'])) || ($SITE_INFO['no_disk_sanity_checks'] != '1')) {
            if (!is_dir($dir)) {
                require_code('files2');
                make_missing_directory($dir);
            }
        }

        require_code('web_resources2');
        css_compile($active_theme, $theme, $c, $full_path, $css_cache_path, $minify);
    }

    if (@intval(filesize($css_cache_path)) == 0/*@ for race condition*/) {
        return '';
    }

    if (get_page_name() == 'cms_comcode_pages') {
        // Allows WYSIWYG to load correct theme into WYSIWYG via URL search and replace
        return 'defer';
    }

    return $css_cache_path;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) all the CSS files that have been required.
 *
 * @param  boolean $inline Force inline CSS
 * @param  boolean $only_global Only do global CSS
 * @param  ?string $context HTML context for which we filter (minimise) any CSS we spit out as inline (null: none)
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme)
 * @return Tempcode The Tempcode to tie in the CSS files
 */
function css_tempcode($inline = false, $only_global = false, $context = null, $theme = null)
{
    global $CSSS, $CSS_OUTPUT_STARTED;

    $CSS_OUTPUT_STARTED = true;

    list($minify, $https, $mobile, $seed) = _get_web_resources_env();

    $css = new Tempcode();
    $css_need_inline = new Tempcode();
    if ($only_global) {
        $css_to_do = array('global' => true, 'no_cache' => true);
        if (isset($CSSS['email'])) {
            $css_to_do['email'] = true;
        }
    } else {
        $css_to_do = $CSSS;
    }
    foreach ($css_to_do as $c => $do_enforce) {
        if (is_integer($c)) {
            $c = strval($c);
        }

        _css_tempcode($c, $css, $css_need_inline, $inline, $context, $theme, $seed, null, null, null, $do_enforce);
    }
    $css_need_inline->attach($css);
    return $css_need_inline;
}

/**
 * Get Tempcode to tie in (to the HTML, in <head>) for an individual CSS file.
 *
 * @param  ID_TEXT $c The CSS file required
 * @param  Tempcode $css Main Tempcode object (will be written into if appropriate)
 * @param  Tempcode $css_need_inline Inline Tempcode object (will be written into if appropriate)
 * @param  boolean $inline Only do global CSS
 * @param  ?string $context HTML context for which we filter (minimise) any CSS we spit out as inline (null: none)
 * @param  ?ID_TEXT $theme The name of the theme (null: current theme) (null: from what is cached)
 * @param  ?ID_TEXT $_seed The seed colour (null: previous cached) (blank: none) (null: from what is cached)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @param  boolean $do_enforce Whether to generate the cached file if not already cached
 *
 * @ignore
 */
function _css_tempcode($c, &$css, &$css_need_inline, $inline = false, $context = null, $theme = null, $_seed = null, $_minify = null, $_https = null, $_mobile = null, $do_enforce = true)
{
    list($minify, $https, $mobile, $seed) = _get_web_resources_env($_seed, $_minify, $_https, $_mobile);

    if ($seed != '') {
        $keep = symbol_tempcode('KEEP');
        $css->attach(do_template('CSS_NEED_FULL', array('_GUID' => 'f2d7f0303a08b9aa9e92f8b0208ee9a7', 'URL' => find_script('themewizard') . '?type=css&show=' . urlencode($c) . '.css' . $keep->evaluate()), user_lang(), false, null, '.tpl', 'templates', $theme));
    } elseif (($c == 'no_cache') || ($inline)) {
        if ($context !== null) {
            require_code('mail');
            $__css = filter_css($c, $theme, $context);
        } else {
            $_css = do_template($c, array(), user_lang(), false, null, '.css', 'css', $theme);
            $__css = $_css->evaluate();
            $__css = str_replace('} ', '}' . "\n", preg_replace('#\s+#', ' ', $__css));
        }

        if (trim($__css) != '') {
            $css_need_inline->attach(do_template('CSS_NEED_INLINE', array('_GUID' => 'f5b225e080c633ffa033ec5af5aec866', 'CODE' => $__css), user_lang(), false, null, '.tpl', 'templates', $theme));
        }
    } else {
        $temp = $do_enforce ? css_enforce($c, $theme, (!running_script('sheet')) && ($context === null) && ($_minify === null) && ($_https === null) && ($_mobile === null)) : '';

        if ($temp == 'defer') {
            $GLOBALS['STATIC_CACHE_ENABLED'] = false;

            if ((function_exists('debugging_static_cache')) && (debugging_static_cache())) {
                error_log('SC: No static cache due to deferred CSS compilation, ' . $c);
            }

            $_theme = ($theme === null) ? $GLOBALS['FORUM_DRIVER']->get_theme() : $theme;
            $keep = symbol_tempcode('KEEP');
            $url = find_script('sheet') . '?sheet=' . urlencode($c) . $keep->evaluate() . '&theme=' . urlencode($_theme);
            if (get_param_string('keep_theme', null) !== $_theme) {
                $url .= '&keep_theme=' . urlencode($_theme);
            }
            if (!$minify) {
                $url .= '&keep_minify=0';
            }
            $css->attach(do_template('CSS_NEED_FULL', array('_GUID' => 'g2d7f0303a08b9aa9e92f8b0208ee9a7', 'URL' => $url), user_lang(), false, null, '.tpl', 'templates', $theme));
        } else {
            if (!$minify) {
                $c .= '_non_minified';
            }
            if ($https) {
                $c .= '_ssl';
            }
            if ($mobile) {
                $c .= '_mobile';
            }
            if (($temp != '') || (!$do_enforce)) {
                $support_smart_decaching = support_smart_decaching();
                $sup = ($support_smart_decaching && $temp != '') ? strval(filemtime($temp)) : null; // Tweaks caching so that upgrades work without needing emptying browser cache; only runs if smart decaching is on because otherwise we won't have the mtime and don't want to introduce an extra filesystem hit
                $css->attach(do_template('CSS_NEED', array('_GUID' => 'ed35fac857214000f69a1551cd483096', 'CODE' => $c, 'SUP' => $sup), user_lang(), false, null, '.tpl', 'templates', $theme));
            }
        }
    }
}

/**
 * Get the environment needed for web resources.
 *
 * @param  ?ID_TEXT $_seed The seed colour (blank: none) (null: from what is cached)
 * @param  ?boolean $_minify Whether minifying (null: from what is cached)
 * @param  ?boolean $_https Whether doing HTTPS (null: from what is cached)
 * @param  ?boolean $_mobile Whether operating in mobile mode (null: from what is cached)
 * @return array A tuple: whether we are minify, if HTTPS is on, if mobile mode is on, seed
 *
 * @ignore
 */
function _get_web_resources_env($_seed = null, $_minify = null, $_https = null, $_mobile = null)
{
    static $seed_cached = null;
    if ($_seed !== null) {
        $seed = $_seed;
    } elseif ($seed_cached === null || running_script('preview'/*may change seed in script code*/)) {
        if (function_exists('has_privilege') && has_privilege(get_member(), 'view_profiling_modes')) {
            $seed = get_param_string('keep_theme_seed', '');
        } else {
            $seed = '';
        }
        $seed_cached = $seed;
    } else {
        $seed = $seed_cached;
    }

    static $minify_cached = null;
    if ($_minify !== null) {
        $minify = $_minify;
    } elseif ($minify_cached === null || $seed != '') {
        if ($seed == '') {
            $minify = (get_param_integer('keep_minify', null) !== 0);
            $minify_cached = $minify;
        } else {
            $minify = false;
        }
    } else {
        $minify = $minify_cached;
    }

    static $https_cached = null;
    if ($_https !== null) {
        $https = $_https;
    } elseif ($https_cached === null) {
        $https = ((addon_installed('ssl')) && function_exists('is_page_https') && function_exists('get_zone_name') && ((tacit_https()) || is_page_https(get_zone_name(), get_page_name())));
        $https_cached = $https;
    } else {
        $https = $https_cached;
    }

    static $mobile_cached = null;
    if ($_mobile !== null) {
        $mobile = $_mobile;
    } elseif ($mobile_cached === null) {
        $mobile = is_mobile();
        $mobile_cached = $mobile;
    } else {
        $mobile = $mobile_cached;
    }

    return array($minify, $https, $mobile, $seed);
}

/**
 * Add some Comcode that does resource-inclusion for CSS and Javascript files that are currently loaded.
 *
 * @param  string $message_raw Comcode
 */
function inject_web_resources_context_to_comcode(&$message_raw)
{
    global $CSSS, $JAVASCRIPTS;

    $_css_comcode = '';
    foreach (array_keys($CSSS) as $i => $css) {
        if ($css == 'global' || $css == 'no_cache') {
            continue;
        }

        if ($_css_comcode != '') {
            $_css_comcode .= ',';
        }
        $_css_comcode .= $css;
    }
    if ($_css_comcode == '') {
        $css_comcode = '';
    } else {
        $css_comcode = '[require_css]' . $_css_comcode . '[/require_css]';
    }

    $_javascript_comcode = '';
    foreach (array_keys($JAVASCRIPTS) as $i => $javascript) {
        if ($javascript == 'global' || $javascript == 'custom_globals') {
            continue;
        }

        if ($_javascript_comcode != '') {
            $_javascript_comcode .= ',';
        }
        $_javascript_comcode .= $javascript;
    }
    if ($_javascript_comcode == '') {
        $javascript_comcode = '';
    } else {
        $javascript_comcode = '[require_javascript]' . $_javascript_comcode . '[/require_javascript]';
    }

    $message_raw = $css_comcode . $javascript_comcode . $message_raw;
}
