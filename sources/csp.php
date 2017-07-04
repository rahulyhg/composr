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

/*
Our implementation of different parts of CSP...

CONSIDERING:
default-src                 (default)                                   base on list of trusted sites, and 'self'
child-src                   (frames and webworkers)                     base on list of trusted sites, and 'self', and extra configuration ["csp_allowed_iframe_descendants"]
connect-src                 (outbound connections from JavaScript)      base on list of trusted sites, and 'self'
script-src                  (JavaScript files)                          base on list of trusted sites, and 'self' (if not strict nonces), and 'unsafe-inline' if configured otherwise nonce
-
base-uri                    (<base> URL)                                'self'
plugin-types                (embedded plugin types)                     configurable ["csp_whitelisted_plugins"]
form-action                 (form targets)                              base on list of trusted sites, and 'self'
frame-ancestors             (what may embed the site)                   base on list of trusted sites, and 'self', and extra configuration ["csp_allowed_iframe_ancestors"]
block-all-mixed-content     (block HTTP content when HTTPS running)     configurable ["csp_allow_insecure_resources" inverted] if HTTPS running
upgrade-insecure-requests   (upgrade HTTP requests to HTTPS)            only if SSL on for the page
report-uri                  (report CSP violations)                     [goes to a logger script if configured ["csp_report_issues"]]

CONSIDERING TO HAVE NO RESTRICTION:
font-src                    (fonts)                                     [overcomplex and no clear security risk, impractical for themers to stick to]
img-src                     (images)                                    [impractical for real users to stick to]; explicitly include data:
media-src                   (audio and video)                           [impractical for real users to stick to]
object-src                  (embedding plugin targets)                  [impractical for real users to stick to]
style-src                   (CSS files)                                 base on list of trusted sites, and 'self' (if not strict nonces), and 'unsafe-inline' [which means the trusted sites etc are essentially ignored]

CONSIDERING TO HAVE FULL RESTRICTION:
manifest-src                (application manifests)                     [we do not package as an application]

NOT CONSIDERING:
frame-src                   (frames)                                    [included in child-src, and worker-src not available yet in browsers - plus deprecated in CSP 2 although back in CSP 3]
worker-src                  (webworkers)                                [only in CSP 3 and included in child-src]
-
sandbox                     (heavy blanket restrictions)                [we are already doing fine-grained control]
disown-opener               (no target windows link back by DOM)        [we are using rel="noopener" already, only in CSP 3 and not properly specced out yet]
navigation-to               (limited outbound linking)                  [impractical for real users to stick to]
require-sri-for             (require files to define and match hashes)  [we can determine which files will be hashed or not ourselves, no need to force it]
<X-Frame-Options header>                                                [duplicates CSP's child-src/frame-src]

ALLOWABLE:
'unsafe-eval'               (allow JavaScript eval and similar)         configurable ["csp_allow_eval_js"] but disallowed by default; some PHP code may unable explicitly; adds to script-src
'strict-dynamic'            (allow dynamic script insertion in JS)      configurable ["csp_allow_dyn_js"] but allowed by default because common third-party libraries like GA may need this; adds to script-src

PARTIALLY ALLOWABLE:
'unsafe-inline'             (allow inline JavaScript and CSS)           disallowed by default; some PHP code may unable explicitly; adds to script-src, for style-src we always enable 'unsafe-inline'

Any of the configurability may be overridden by PHP code calling load_csp with overridden options.


Good sources of documentation...

http://caniuse.com/#search=csp
https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy
https://developers.google.com/web/fundamentals/security/csp/
https://en.wikipedia.org/wiki/Content_Security_Policy
https://blogs.windows.com/msedgedev/2017/01/10/edge-csp-2/#qVG9yQBp92ZzVcjd.97
https://scotthelme.co.uk/csp-cheat-sheet/
https://content-security-policy.com/
https://w3c.github.io/webappsec-csp/
https://csp.withgoogle.com/
*/

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__csp()
{
    require_code('crypt');

    /**
     * CSP Nonce
     * @global boolean $CSP_NONCE
     */
    global $CSP_NONCE;
    $CSP_NONCE = produce_salt();

    global $CSP_ENABLED;
    $CSP_ENABLED = false;

    if (!defined('CSP_PRETTY_STRICT')) {
        define('CSP_PRETTY_STRICT', array(
            'csp_enabled' => '1',
            'csp_whitelisted_plugins' => '',

            'csp_allow_inline_js' => '0',
        ));

        define('CSP_VERY_STRICT', array(
            'csp_enabled' => '1',
            'csp_exceptions' => '',
            'csp_whitelisted_plugins' => '',
            'csp_allowed_iframe_ancestors' => '',
            'csp_allowed_iframe_descendants' => '',

            'csp_allow_eval_js' => '0',
            'csp_allow_dyn_js' => '0',
            'csp_allow_insecure_resources' => '1',

            // Not usually configurable but may be forced
            'csp_allow_inline_js' => '0',
        ));
    }
}

/**
 * Load up CSP settings.
 *
 * @param  ?array $options Overrides for options; any non-set properties will result in no-change to the current CSP state or if for a new state CSP_VERY_STRICT (null: load full clean state from configuration)
 * @param  ?MEMBER $enable_more_open_html_for Allow more open HTML for a particular member ID (null: no member). It still will use the HTML blacklist functionality (unless they have even higher access already), but will remove the more restrictive whitelist functionality. Should only be used with CSP_PRETTY_STRICT/CSP_VERY_STRICT which will further decreasing the risk from dangerous HTML, even though the risk should be very low anyway due to the blacklist filter.
 */
function load_csp($options = null, $enable_more_open_html_for = null)
{
    global $CSP_NONCE;

    // Decide what our options will be...

    static $previous_state = null; // Initial state is defaulting to strict
    if ($previous_state === null) {
        $previous_state = CSP_VERY_STRICT;
    }

    if ($options === null) { // Full clean state from configuration
        $options = array(
            'csp_enabled' => get_option('csp_enabled'),
            'csp_exceptions' => get_option('csp_exceptions'),
            'csp_whitelisted_plugins' => get_option('csp_whitelisted_plugins'),
            'csp_allowed_iframe_ancestors' => get_option('csp_allowed_iframe_ancestors'),
            'csp_allowed_iframe_descendants' => get_option('csp_allowed_iframe_descendants'),

            'csp_allow_eval_js' => get_option('csp_allow_eval_js'),
            'csp_allow_dyn_js' => get_option('csp_allow_dyn_js'),
            'csp_allow_insecure_resources' => get_option('csp_allow_insecure_resources'),

            'csp_allow_inline_js' => '0', // Not used
        );
    } else {
        $options = $options + $previous_state; // Merge new state with previous state
    }

    $previous_state = $options;

    $csp_enabled = ($options['csp_enabled'] != '0');
    $report_only = ($options['csp_enabled'] == '2');
    $csp_exceptions = $options['csp_exceptions'];
    $csp_whitelisted_plugins = $options['csp_whitelisted_plugins'];
    $csp_allowed_iframe_ancestors = $options['csp_allowed_iframe_ancestors'];
    $csp_allowed_iframe_descendants = $options['csp_allowed_iframe_descendants'];

    $csp_allow_inline_js = ($options['csp_allow_inline_js'] == '1');
    $csp_allow_eval_js = ($options['csp_allow_eval_js'] == '1');
    $csp_allow_dyn_js = ($options['csp_allow_dyn_js'] == '1');
    $csp_allow_insecure_resources = ($options['csp_allow_insecure_resources'] !== '0');

    if ($enable_more_open_html_for !== null) {
        global $PRIVILEGE_CACHE;
        has_privilege($enable_more_open_html_for, 'allow_html'); // Force loading, so we can amend the cached value cleanly
        $PRIVILEGE_CACHE[$enable_more_open_html_for]['allow_html'][''][''][''] = 1;
    }

    // Check if the current page is excluded from CSP...

    if (is_string($csp_exceptions) && ($csp_exceptions !== '')) {
        require_code('global3');
        $current_page_name = get_page_name();
        $matches = array();
        if (preg_match('/' . $csp_exceptions . '/', $current_page_name, $matches)) {
            $csp_enabled = false;
        }
    }

    global $CSP_ENABLED;
    $CSP_ENABLED = $csp_enabled;

    if (!$csp_enabled) {
        @header_remove('Content-Security-Policy');
        return;
    }

    // Now build the CSP header clauses for sources...

    $clauses = array();

    // default-src
    $_sources_list = _csp_extract_sources_list(2);
    $clauses[] = 'default-src ' . implode(' ', $_sources_list);

    // style-src
    $_sources_list = _csp_extract_sources_list(2);
    $_sources_list[] = "'unsafe-inline'"; // It's not feasible for us to remove all inline CSS
    //$_sources_list[] = "'nonce-{$CSP_NONCE}'"; Incompatible with unsafe-inline
    $clauses[] = 'style-src ' . implode(' ', $_sources_list);

    // script-src
    $_sources_list = _csp_extract_sources_list(2);
    if ($csp_allow_inline_js) {
        $_sources_list[] = "'unsafe-inline'"; // Not usually configurable but may be forced
    } else {
        $_sources_list[] = "'nonce-{$CSP_NONCE}'";
    }
    if ($csp_allow_eval_js) {
        $_sources_list[] = "'unsafe-eval'"; // Actually this is an option not a true source
    }
    if ($csp_allow_dyn_js) {
        $_sources_list[] = "'strict-dynamic'"; // Actually this is an option not a true source
    }
    $clauses[] = 'script-src ' . implode(' ', $_sources_list);

    // child-src
    $_sources_list = _csp_extract_sources_list(2, $csp_allowed_iframe_descendants);
    if ($_sources_list === null) {
        $_sources_list = array();
        $_sources_list[] = '*';
    }
    $_sources_list[] = "'nonce-{$CSP_NONCE}'"; // In case W3C start supporting it for iframe elements
    $clauses[] = 'child-src ' . implode(' ', $_sources_list);

    // connect-src
    $_sources_list = _csp_extract_sources_list(2);
    $clauses[] = 'connect-src ' . implode(' ', $_sources_list);

    // font-src (unlimited)
    $_sources_list = array();
    $_sources_list[] = '*';
    $clauses[] = 'font-src ' . implode(' ', $_sources_list);

    // object-src (unlimited)
    $_sources_list = array();
    $_sources_list[] = '*';
    $clauses[] = 'object-src ' . implode(' ', $_sources_list);

    // img-src (unlimited)
    $_sources_list = array();
    $_sources_list[] = '*';
    $_sources_list[] = 'data:';
    $clauses[] = 'img-src ' . implode(' ', $_sources_list);

    // media-src (unlimited)
    $_sources_list = array();
    $_sources_list[] = '*';
    $clauses[] = 'media-src ' . implode(' ', $_sources_list);

    // manifest-src (disabled)
    $_sources_list = array();
    $_sources_list[] = "'none'";
    $clauses[] = 'manifest-src ' . implode(' ', $_sources_list);

    // Now build the CSP header clauses for other options...

    // base-url
    $clauses[] = "base-uri 'self'";

    // plugin-types
    if (trim($csp_whitelisted_plugins) != '') {
        $clauses[] = 'plugin-types ' . str_replace("\n", ' ', $csp_whitelisted_plugins);
    }

    // form-action
    $_sources_list = _csp_extract_sources_list(2);
    $clauses[] = 'form-action ' . implode(' ', $_sources_list);

    // frame-ancestors
    $_sources_list = _csp_extract_sources_list(2, $csp_allowed_iframe_ancestors);
    if ($_sources_list === null) {
        $_sources_list = array();
        $_sources_list[] = '*';
    }
    $clauses[] = 'frame-ancestors ' . implode(' ', $_sources_list);

    // block-all-mixed-content
    if (!$csp_allow_insecure_resources) {
        $clauses[] = 'block-all-mixed-content';
    }

    // upgrade-insecure-requests
    if (function_exists('addon_installed')) { // If not still booting
        if (substr(get_base_url(), 0, 8) == 'https://') {
            $clauses[] = 'upgrade-insecure-requests';
        }
    }

    // report-uri
    if ((function_exists('get_option')) && (get_option('csp_report_issues') == '1')) {
        $clauses[] = 'report-uri ' . find_script('csp_logging'); // Note 'report-uri' is deprecated in CSP 3, which is not implemented or finished at the time of writing
    }

    // Now build the CSP header...

    $header = '';
    foreach ($clauses as $clause) {
        if ($header != '') {
            $header .= '; ';
        }

        $header .= $clause;
    }

    // Output the CSP header...

    if ($report_only) {
        @header('Content-Security-Policy-Report-Only: ' . $header);
    } else {
        @header('Content-Security-Policy: ' . $header);
    }
}

/**
 * Extracts CSP sources from the given string, plus trusted sites.
 *
 * @param  integer $level Trusted sites level
 * @set 1 2
 * @param  string $sources_csv Comma-separated list of valid CSP 'sources' (blank: just trusted sites)
 * @param  boolean $include_self Include a self reference
 * @return ?array CSP sources (null: allow all, only possible when $sources_csv is passed) (empty: disallow all except local)
 */
function _csp_extract_sources_list($level, $sources_csv = '', $include_self = true)
{
    $sources_csv = trim($sources_csv);

    if ($sources_csv == '*') {
        // All
        return null;
    }

    $sources_list = array();

    if ($include_self) {
        $sources_list[] = "'self'";
    }

    require_code('input_filter');
    $_trusted_sites = get_trusted_sites($level);
    if ($_trusted_sites == array()) {
        foreach ($_trusted_sites as $partner) {
            $sources_list[] = $partner;
        }
    }

    $sources = ($sources_csv == '') ? array() : preg_split('#[, \n]#', $sources_csv);
    foreach ($sources as $_source) {
        $source = _csp_clean_source($_source);
        if ($source !== null) {
            $sources_list[] = $source;
        }
    }

    return $sources_list;
}

/**
 * Cleanup a CSP source value.
 *
 * @param  string $_source Raw value
 * @return ?string Fixed value (null: corrupt, don't use)
 */
function _csp_clean_source($_source)
{
    $source = trim($_source);

    if (strpos($source, '://') === false) {
        return $source;
    }

    $parts = parse_url($source);

    // parse_url returns false when the URL is seriously malformed
    if (!is_array($parts)) {
        return null;
    }

    if (empty($parts['host'])) {
        // Invalid/No domain specified
        return null;
    }

    if ($parts['scheme'] && ($parts['scheme'] !== 'http') && ($parts['scheme'] !== 'https')) {
        // Invalid scheme
        return null;
    }

    $source = '';

    if (!empty($parts['scheme'])) {
        $source .= $parts['scheme'] . '://';
    }

    $source .= $parts['host'];

    if (!empty($parts['port'])) {
        $source .= ':' . strval($parts['port']);
    }

    if (!empty($parts['path'])) {
        $source .= $parts['path'];
    }

    if (!empty($parts['query'])) {
        $source .= '?' . $parts['query'];
    }

    return $source;
}

/**
 * Set a CSP header to not allow any frames to include us.
 */
function set_no_clickjacking_csp()
{
    load_csp(array('csp_allowed_iframe_ancestors' => '')); // Overrides possible '*' setting
}

/**
 * Log CSP issues.
 */
function csp_logging_script()
{
    if (get_option('csp_report_issues') == '0') {
        return;
    }

    $data = @file_get_contents('php://input');

    set_http_status_code(204);

    trigger_error('CSP violation: ' . $data, E_USER_NOTICE);
}

/**
 * Stop the web browser trying to save us, and breaking some requests in the process.
 */
function disable_browser_reflective_xss_detection()
{
    @header('X-XSS-Protection: 0');
}

/**
 * Get CSP nonce in HTML attribute format.
 *
 * @return string HTML to insert
 */
function csp_nonce_html()
{
    global $CSP_NONCE;
    return isset($CSP_NONCE) ? ('nonce="' . escape_html($CSP_NONCE) . '"') : '';
}
