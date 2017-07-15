<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class _health_check_test_set extends cms_test_case
{
    // These tests will eventually become a part of the Health Check system https://compo.sr/tracker/view.php?id=3314

    protected function get_page_url($page = '')
    {
        static $ret = null;
        if ($ret !== null) {
            $ret = static_evaluate_tempcode(build_url(array('page' => ''), ''));
        }
        return $ret;
    }

    protected function get_page_content($page = '')
    {
        static $ret = false;
        if ($ret === false) {
            $ret = http_download_file($this->get_page_url($page), null, false);

            // Server blocked to access itself
            $this->assertTrue($ret !== null, 'The server cannot download itself');
        }
        return $ret;
    }

    protected function is_local_domain($domain = null)
    {
        if ($domain === null) {
            $domain = parse_url(get_base_url(), PHP_URL_HOST);
        }

        return ($domain == 'localhost') || (trim($domain, '0123456789.') == '') || (strpos($domain, ':') !== false);
    }

    // Expired SSL certificate, or otherwise malfunctioning SSL (if enabled)
    /*public function testForSSLIssues($manual_checks = false, $automatic_repair = false)
    {
        if ((addon_installed('ssl')) || (substr(get_base_url(), 0, 7) == 'https://')) {
            // If it's a problem with SSL verification in general
            $data = http_download_file('https://www.google.com/', null, false);
            $ok = (strpos($data, '<html') !== false);
            $this->assertTrue($ok, 'Problem downloading HTTP requests by SSL');

            if ($ok) {
                // If it's a problem with SSL verification on our domain specifically
                $domain = parse_url(get_base_url(), PHP_URL_HOST);
                if (get_value('disable_ssl_for__' . $domain) === null) {
                    $test_url = get_base_url(true) . '/uploads/index.html';

                    set_value('disable_ssl_for__' . $domain, '0');
                    $data = http_download_file($test_url, null, false);
                    $ok1 = (strpos($data, '<html') !== false);

                    if (!$ok1) {
                        set_value('disable_ssl_for__' . $domain, '1');
                        $data = http_download_file($test_url, null, false);
                        $ok2 = (strpos($data, '<html') !== false);

                        $this->assertTrue(!$ok2, 'Problem detected with the ' . $domain . ' SSL certificate'); // Issue with our SSL but not if verify is disabled, suggesting the problem is with verify

                        delete_value('disable_ssl_for__' . $domain);
                    } else {
                        $this->assertTrue(true); // No issue with our SSL
                    }
                }
            }
        }
    }*/

    // Heavy 404 errors on the same URLs, with no redirects
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // TODO: Decent 404 page with sitemap

    // Outgoing mail not working
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // CRON taking more than 5 minutes to run
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Lost packets doing simple outbound ping
    /*public function testForPingIssues($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $result = shell_exec('ping -c 10 8.8.8.8');
            $matches = array();
            if (preg_match('# (\d(\.\d+)%) packet loss#', $result, $matches) != 0) {
                $this->assertTrue(floatval($matches[1]) == 0.0, 'Unreliable Internet connection on server');
            }
        }
    }*/

    // Slow download speed
    /*public function testForSlowDownload($manual_checks = false, $automatic_repair = false)
    {
        $time_before = microtime(true);
        $result = http_download_file('http://www.google.com/');
        $time_after = microtime(true);

        $time = ($time_after - $time_before);

        $threshold = 0.4;

        $this->assertTrue($time < $threshold, 'Slow downloading speed (downloading Google home page took over ' . float_format($time) . ' seconds)');
    }*/

    // Slow upload speed
    /*public function testForSlowUpload($manual_checks = false, $automatic_repair = false)
    {
        $test_file_path = get_file_base() . '/data/curl-ca-bundle.crt';

        $data_to_send = str_repeat(file_get_contents($test_file_path), 20);

        $time_before = microtime(true);
        $post_params = array('test_data' => $data_to_send);
        $result = http_download_file('http://www.cloudflare.com/about-overview/', null, false, true, 'Composr', $post_params);
        $time_after = microtime(true);

        $time = ($time_after - $time_before);

        $megabytes_per_second = floatval(strlen($data_to_send)) / (1024.0 * 1024.0 * $time);

        $threshold_in_megabits_per_second = 4.0;

        $this->assertTrue($megabytes_per_second * 8.0 > $threshold_in_megabits_per_second, 'Slow uploading speed (' . float_format($megabytes_per_second) . ' Megabytes per second)');
    }*/

    // A page takes more than a second to load
    /*public function testForSlowPageSpeeds($manual_checks = false, $automatic_repair = false)
    {
        $page_links = array( // TODO: Make configurable
            ':',
        );

        foreach ($page_links as $page_link) {
            $url = page_link_to_url($page_link);

            $time_before = microtime(true);
            $result = http_download_file($url);
            $time_after = microtime(true);

            $time = ($time_after - $time_before);

            $threshold = 1.0; // Threshold is pretty high because we may have stale caches etc; we're looking for major issues, not testing our overall optimisation

            $this->assertTrue($time < $threshold, 'Slow page generation speed ("' . $page_link . '" page-link took over ' . float_format($time) . ' seconds)');
        }
    }*/

    // Meta description missing for page, too short, or too long
    /*public function testForBadMetaDescription($manual_checks = false, $automatic_repair = false)
    {
        $data = $this->get_page_content();
        if ($data === null) {
            return;
        }

        $meta_description = null;
        $matches = array();
        if (preg_match('#<meta\s+[^<>]*name="description"[^<>]*content="([^"]*)"#is', $data, $matches) != 0) {
            $meta_description = $matches[1];
        } elseif (preg_match('#<meta\s+[^<>]*content="([^"]*)"[^<>]*name="description"#is', $data, $matches) != 0) {
            $meta_description = $matches[1];
        }

        $ok = ($meta_description !== null);
        $this->assertTrue($ok, 'Could not find a meta description');
        if ($ok) {
            $len = strlen($meta_description);
            $min_threshold = 40;
            $max_threshold = 155;
            $this->assertTrue($len >= $min_threshold, 'Meta description lengthis under ' . strval($min_threshold) . ' @ ' . strval(integer_format($len)) . ' characters');
            $this->assertTrue($len <= $max_threshold, 'Meta description length is over ' . strval($max_threshold) . ' @ ' . strval(integer_format($len)) . ' characters');
        }
    }*/

    // Meta keywords missing for page, too few, or too many
    /*public function testForBadMetaKeywords($manual_checks = false, $automatic_repair = false)
    {
        $data = $this->get_page_content();
        if ($data === null) {
            return;
        }

        $meta_keywords = null;
        $matches = array();
        if (preg_match('#<meta\s+[^<>]*name="keywords"[^<>]*content="([^"]*)"#is', $data, $matches) != 0) {
            $meta_keywords = array_map('trim', explode(',', $matches[1]));
        } elseif (preg_match('#<meta\s+[^<>]*content="([^"]*)"[^<>]*name="keywords"#is', $data, $matches) != 0) {
            $meta_keywords = array_map('trim', explode(',', $matches[1]));
        }

        $ok = ($meta_keywords !== null);
        $this->assertTrue($ok, 'Could not find any meta keywords');
        if ($ok) {
            $count = count($meta_keywords);
            $min_threshold = 4;
            $max_threshold = 20;
            $this->assertTrue($count >= $min_threshold, 'Meta keyword count is under ' . strval($min_threshold) . ' @ ' . strval(integer_format($count)));
            $this->assertTrue($count <= $max_threshold, 'Meta keyword count is over ' . strval($max_threshold) . ' @ ' . strval(integer_format($count)));
        }
    }*/

    // No <title>, too short, or too long
    /*public function testForBadTitle($manual_checks = false, $automatic_repair = false)
    {
        $data = $this->get_page_content();
        if ($data === null) {
            return;
        }

        $title = null;
        $matches = array();
        if (preg_match('#<title[^<>]*>([^<>]*)</title>#is', $data, $matches) != 0) {
            $title = $matches[1];
        }

        $ok = ($title !== null);
        $this->assertTrue($ok, 'Could not find any <title>');
        if ($ok) {
            $len = strlen($title);
            $min_threshold = 4;
            $max_threshold = 70;
            $this->assertTrue($len >= $min_threshold, '<title> length is under ' . strval($min_threshold) . ' @ ' . strval(integer_format($len)));
            $this->assertTrue($len <= $max_threshold, '<title> length is over ' . strval($max_threshold) . ' @ ' . strval(integer_format($len)));
        }
    }*/

    // No <h1>
    /*public function testForBadH1($manual_checks = false, $automatic_repair = false)
    {
        $data = $this->get_page_content();
        if ($data === null) {
            return;
        }

        $header = null;
        $matches = array();
        if (preg_match('#<h1[^<>]*>([^<>]*)</h1>#is', $data, $matches) != 0) {
            $header = $matches[1];
        }

        $ok = ($header !== null);
        $this->assertTrue($ok, 'Could not find any <h1>');
    }*/

    // XML Sitemap not being extended
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // XML Sitemap fails validation test
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // robots.txt fails validation test
    /*public function testForRobotsTxtErrors($manual_checks = false, $automatic_repair = false)
    {
        $this->robotsParse(null, true);
    }

    // robots.txt banning Google on a live site
    public function testForRobotsTxtBlocking($manual_checks = false, $automatic_repair = false)
    {
        $url = $this->get_page_url();

        $google_blocked = $this->robotsAllowed($url, 'Googlebot', true);
        $other_blocked = $this->robotsAllowed($url, 'Googlebot', false); // We'll still check for Google, just with the other way of doing precedence

        if ($google_blocked == $other_blocked) {
            $this->assertTrue($google_blocked, 'Site blocked by robots.txt');
        } else {
            $this->assertTrue($google_blocked, 'Site blocked on Google by robots.txt as per Google\'s way of implementing robots standard');
            $this->assertTrue($other_blocked, 'Site blocked on Google by robots.txt as per standard (non-Google) way of implementing robots standard');
        }

        / *
        This shows how the inconsistency works...

        Standard block:
        User-Agent: *
        Disallow: /
        Allow: /composr
        (Disallow takes precedence due to order of rules)

        Google block:
        User-Agent: *
        Allow: /
        Disallow: /composr
        (Disallow takes precedence due to specificity)

        Consistent block:
        User-Agent: *
        Disallow: /composr
        Allow: /
        (Disallow takes precedence both due due to order of rules and specificity)
        * /
    }

    protected function robotsAllowed($url, $user_agent, $google_style)
    {
        $this->robotsParse($user_agent);

        $rules = $this->robots_rules;

        if ($rules === null) {
            return true;
        }

        $url_path = parse_url($url, PHP_URL_PATH);

        $best_precedence = 0;
        $allowed = true;
        foreach ($rules as $_rule) {
            list($key, $rule) = $_rule;

            switch ($key) {
                case 'allow':
                case 'disallow':
                    if ($rule == '') {
                        continue; // Ignored rule
                    }

                    if (preg_match('#^' . $rule . '#', $url_path) != 0) {
                        if ($google_style) {
                            if (strlen($rule) > $best_precedence) {
                                $allowed = ($key == 'allow');
                                $best_precedence = strlen($rule);
                            }
                        } else {
                            return ($key == 'allow');
                        }
                    }

                    break;
            }
        }
        return $allowed;
    }

    protected $rules;

    protected function robotsParse($user_agent, $error_messages = false)
    {
        // The best specification is by Google now:
        //  https://developers.google.com/search/reference/robots_txt

        $this->robots_rules = null;

        $robots_path = get_file_base() . '/robots.txt'; // TODO: Should be on domain root
        if (!is_file($robots_path)) {
            return;
        }

        $agents_regexp = preg_quote('*');
        if ($user_agent !== null) {
            $agents_regexp .= '|' . preg_quote($user_agent, '#');
        }

        $robots_lines = explode("\n", cms_file_get_contents_safe($robots_path));

        // Go through lines
        $rules = array();
        $following_rules_apply = false;
        $best_following_rules_apply = 0;
        $just_did_ua_line = false;
        $did_some_ua_line = false;
        foreach ($robots_lines as $line) {
            $line = trim($line);

            // Skip blank lines
            if ($line == '') {
                continue;
            }

            // Skip comment lines
            if ($line[0] == '#') {
                continue;
            }

            // The following rules only apply if the User-Agent matches
            $matches = array();
            if (preg_match('#^User-Agent:(.*)#i', $line, $matches) != 0) {
                $agent_spec = $matches[1];
                $_following_rules_apply = (preg_match('#(' . $agents_regexp . ')#i', $agent_spec) != 0); // It's a bit weird how "googlebot-xxx" would match but "google" would not, but that's the standard (and there's justification when you think about it)
                if ($_following_rules_apply) {
                    if (strlen($agent_spec) >= $best_following_rules_apply) {
                        $following_rules_apply = true;
                        $best_following_rules_apply = strlen($agent_spec);
                        $rules = array(); // Reset rules, as now this is the best scoring rules section (we don't merge sections)
                    }
                } elseif (!$just_did_ua_line) {
                    $following_rules_apply = false;
                }

                $just_did_ua_line = true;
                $did_some_ua_line = true;

                continue;
            }

            // Record rules
            if (preg_match('#^(\w+):\s*(.*)\s*$#i', $line, $matches) != 0) {
                $key = strtolower($matches[1]);
                $value = trim($matches[2]);

                $core_rule = ($key == 'allow') || ($key == 'disallow');

                if ($error_messages) {
                    $this->assertTrue(in_array($key, array('allow', 'disallow', 'sitemap', 'crawl-delay')), 'Unrecognised robots.txt rule:' . $key);

                    if ($core_rule) {
                        $this->assertTrue($did_some_ua_line, 'Floating ' . ucwords($key) . ' outside of any User-Agent section');
                    }
                }

                if ($following_rules_apply) {
                    // Add rules that apply to array for testing
                    if ($core_rule) {
                        $rule = addcslashes($value, '#\+?^[](){}|-'); // Escape things that are in regexps but should be literal here
                        $rule = str_replace('*', '.*', $rule); // * wild cards are ".*" in a regexp
                        // "$" remains unchanged

                        $rules[] = array($key, $rule);
                    } else {
                        $rules[] = array($key, $value);
                    }
                }

                $just_did_ua_line = false;

                continue;
            }

            // TODO: What if Sitemap URL on different domain or different protocol, or relative URL?

            // Unrecognised line
            if ($error_messages) {
                $this->assertTrue(false, 'Unrecognised line in robots.txt:' . $line);
            }
        }

        $this->robots_rules = $rules;
    }*/

    // robots.txt missing or does not block maintenance scripts
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // MyISAM database table(s) crashed
    /*public function testForCorruptTables($manual_checks = false, $automatic_repair = false)
    {
        if (strpos(get_db_type(), 'mysql') !== false) {
            $tables = $GLOBALS['SITE_DB']->query_select('db_meta', array('DISTINCT m_table'));
            foreach ($tables as $table) {
                $results = $GLOBALS['SITE_DB']->query('CHECK TABLE ' . get_table_prefix() . $table['m_table']);
                $ok = $results[0]['Msg_text'] == 'OK';

                if (!$ok) {
                    $message = 'Corrupt table likely repairing: ' . $table['m_table'] . ' gave status ' . $results[0]['Msg_text'];
                    if ($automatic_repair) {
                        $results_repair = $GLOBALS['SITE_DB']->query('REPAIR TABLE ' . get_table_prefix() . $table['m_table']);
                        $ok_repair = $results[0]['Msg_text'] == 'OK';
                        if ($ok_repair) {
                            $message = 'Corrupt table automatically repaired: ' . $table['m_table'] . ' gave status ' . $results[0]['Msg_text'];
                        }
                    }

                    $this->assertTrue($ok, $message);
                } else {
                    $this->assertTrue(true);
                }
            }
        }
    }*/

    // Missing </html> tag on page (implies page isn't fully generating) (configurable list of page-links)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Page too big (configurable list of page-links, configurable max size)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Disk space too low (and remove page-load request, "Little disk space check" and it's independent notification)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Page too small (configurable list of page-links, configurable max size)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // No guest access to page (configurable list of page-links)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Backups configured but not appearing under exports/backups
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Web server not accessible from external proxy
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Broken links page (configurable list of page-links) (and remove old cleanup tool that currently does this)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Broken images on page (configurable list of page-links) (would need a config option)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Inconsistent database state (and remove old cleanup tool that currently does this)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Outdated copyright date
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Fall in Google position (ties into main_staff_website_monitoring block)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Fall in hits
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // www/non-www redirect not handled well - either does not exist, or redirects deep to home page, and/or is not 301
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // https/non-https redirect not handled well - either does not exist, or redirects deep to home page, and/or is not 301
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // JS error on page (configurable list of page-links)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Integrity checker fail
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // E-mail queue piling up
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Newsletter queue piling up
    /*public function testForNewsletterQueueStuck($manual_checks = false, $automatic_repair = false)
    {
        $sql = 'SELECT COUNT(*) FROM ' . get_table_prefix() . 'newsletter_drip_send WHERE d_inject_time<' . strval(time() - 60 * 60 * 24 * 7);
        $count = $GLOBALS['SITE_DB']->query_value_if_there($sql);

        $this->assertTrue($count == 0, 'The newsletter queue has e-mails still not sent within a week');
    }*/

    // Stuff going into error log
    /*public function testForErrorLogFlooding($manual_checks = false, $automatic_repair = false)
    {
        $path = get_custom_file_base() . '/data_custom/errorlog.php';
        $myfile = fopen($path, 'rb');
        if ($myfile !== false) {
            $filesize = filesize($path);

            fseek($myfile, max(0, $filesize - 50000));

            fgets($myfile); // Skip line part-way-through

            $threshold_time = time() - 60 * 60 * 24 * 1;
            $threshold_count = 50; // TODO: Make configurable

            $dates = array();
            while (!feof($myfile)) {
                $line = fgets($myfile);

                $matches = array();
                if (preg_match('#^\[([^\[\]]*)\] #', $line, $matches) != 0) {
                    $timestamp = @strtotime($matches[1]);
                    if (($timestamp !== false) && ($timestamp > $threshold_time)) {
                        $dates[] = $timestamp;
                    }
                }
            }

            fclose($myfile);

            $this->assertTrue(count($dates) < $threshold_count, integer_format(count($dates)) . ' logged errors in the last day');
        }
    }*/

    // http:// URLs appearing on page when site has a https:// base URL (configurable list of page-links)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Non-https images/scripts/CSS/etc embedded on pages that are https (configurable list of page-links)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Web standards validation errors (configurable list of page-links, blank by default)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // URLs using this regexp https?://(localhost|127.|192.|10.).
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Links to broken CSS or JavaScript files
    /*public function testForBrokenWebIncludes($manual_checks = false, $automatic_repair = false)
    {
        $data = $this->get_page_content();
        if ($data === null) {
            return;
        }

        $urls = array();
        $matches = array();
        $num_matches = preg_match_all('#<link\s[^<>]*href="([^"]*)"[^<>]*rel="stylesheet"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }
        $num_matches = preg_match_all('#<link\s[^<>]*rel="stylesheet"[^<>]*href="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }
        $num_matches = preg_match_all('#<script\s[^<>]*src="([^"]*)"#is', $data, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $urls[] = $matches[1][$i];
        }

        foreach ($urls as $url) {
            if (substr($url, 0, 2) == '//') {
                $url = 'http:' . $url;
            }

            if (substr($url, 0, strlen(get_base_url(false)) + 1) == get_base_url(false) . '/') {
                continue;
            }

            if (substr($url, 0, strlen(get_base_url(true)) + 1) == get_base_url(true) . '/') {
                continue;
            }

            if (strpos($url, '://') !== false) {
                $result = http_download_file($url, null, false);
                $this->assertTrue(!empty($result), 'Broken included file: ' . $url);
            }
        }
    }*/

    // CRON tasks not successfully running all the way through
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Cache or temp directories unreasonably huge
    /*public function testForOverflowingDirectories($manual_checks = false, $automatic_repair = false)
    {
        require_code('files');
        require_code('files2');

        $mb = 1024 * 1024;
        $directories = array(
            'caches/guest_pages' => 500,
            'caches/lang' => 200,
            'caches/persistent' => 500,
            'caches/self_learning' => 500,
            'uploads/incoming' => 500,
            'safe_mode_temp' => 50, // TODO: temp in v11
            'themes/' . $GLOBALS['FORUM_DRIVER']->get_theme('') . '/templates_cached' => 20,
        );
        foreach ($directories as $dir => $max_threshold_size_in_mb) {
            if (file_exists(get_file_base() . '/' . $dir)) {
                $size = get_directory_size(get_file_base() . '/' . $dir);
                $this->assertTrue($size < $mb * $max_threshold_size_in_mb, 'Directory ' . $dir . ' is ' . clean_file_size($size));
            }
        }

        $directories = array(
            'uploads/incoming' => 50,
            'safe_mode_temp' => 50, // TODO: temp in v11
            'data_custom/profiling' => 50,
        );
        foreach ($directories as $dir => $max_contents_threshold) {
            $count = count(get_directory_contents(get_file_base() . '/' . $dir));
            $this->assertTrue($count < $max_contents_threshold, 'Directory ' . $dir . ' now contains ' . integer_format($count) . ' files, should hover only slightly over empty');
        }
    }*/

    // Logs too large
    /*public function testForLargeLogs($manual_checks = false, $automatic_repair = false)
    {
        require_code('files');

        $path = get_file_base() . '/data_custom';
        $dh = opendir($path);
        while (($f = readdir($dh)) !== false) {
            if (strpos($f, 'log') !== false) {
                $size = filesize($path . '/' . $f);
                $this->assertTrue($size < 1000000, 'Size of ' . $f . ' log is ' . clean_file_size($size));
            }
        }
        closedir($dh);
    }*/

    // Volatile tables unreasonably huge
    /*public function testForOverflowingTables($manual_checks = false, $automatic_repair = false)
    {
        $tables = array(
            'autosave' => 100000,
            'cache' => 1000000,
            'cached_comcode_pages' => 10000,
            'captchas' => 10000,
            'chat_active' => 100000,
            'chat_events' => 10000000,
            'cron_caching_requests' => 10000,
            'post_tokens' => 10000,
            'edit_pings' => 10000,
            'hackattack' => 1000000,
            'incoming_uploads' => 10000,
            'logged_mail_messages' => 100000,
            'messages_to_render' => 100000,
            'sessions' => 1000000,
            'sitemap_cache' => 100000,
            'temp_block_permissions' => 10000000,
            'url_title_cache' => 100000,
            'urls_checked' => 100000,
        );

        foreach ($tables as $table => $max_threshold) {
            $cnt = $GLOBALS['SITE_DB']->query_select_value($table, 'COUNT(*)');
            $this->assertTrue($cnt < max_threshold, 'Volatile-defined table now contains ' . integer_format($cnt) . ' records');
        }
    }*/

    // Admin account that has not logged in in months and should be deleted
    /*public function testForUnusedAdminAccounts($manual_checks = false, $automatic_repair = false)
    {
        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
        $members = $GLOBALS['FORUM_DRIVER']->member_group_query($admin_groups);
        foreach ($members as $member) {
            $last_visit = $GLOBALS['FORUM_DRIVER']->mrow_lastvisit($member);
            $username = $GLOBALS['FORUM_DRIVER']->mrow_username($member);
            $threshold = time() - 60 * 60 * 24 * 90; // TODO: Make configurable
            $this->assertTrue($last_visit > $threshold, 'Admin account ' . $username . ' not logged in for ' . display_time_period(time() - $last_visit) . ', consider deleting');
        }
    }*/

    // Unusual number of hack attacks
    /*public function testForHackAttackSpike($manual_checks = false, $automatic_repair = false)
    {
        $sql = 'SELECT COUNT(*) FROM ' . get_table_prefix() . 'hackattack WHERE date_and_time>' . strval(time() - 60 * 60 * 24);
        $num_failed = $GLOBALS['SITE_DB']->query_value_if_there($sql);
        $this->assertTrue($num_failed < 100, integer_format($num_failed) . ' hack-attack alerts happened today');
    }*/

    // Unusual number of failed logins
    /*public function testForFailedLoginsSpike($manual_checks = false, $automatic_repair = false)
    {
        $sql = 'SELECT COUNT(*) FROM ' . get_table_prefix() . 'failedlogins WHERE date_and_time>' . strval(time() - 60 * 60 * 24);
        $num_failed = $GLOBALS['SITE_DB']->query_value_if_there($sql);
        $this->assertTrue($num_failed < 100, integer_format($num_failed) . ' failed logins happened today');
    }*/

    // Unusual increase in rate limiting triggers (could indicate a distributed denial of service attack)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Unusual increase in CAPTCHA fails (could indicate a distributed denial of service attack)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Unusual increase in spam detection (could indicate a distributed denial of service attack)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // High server CPU load
    /*public function testForHighCPULoad($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $cpu = null;

            if (strpos(PHP_OS, 'Darwin') !== false) {
                $result = explode("\n", shell_exec('iostat'));
                array_shift($result);
                array_shift($result);
                if (isset($result[0])) {
                    $matches = array();
                    if (preg_match('#(\d+)\s+(\d+)\s+(\d+)\s+\d+\.\d+\s+\d+\.\d+\s+\d+\.\d+\s*$#', $result[0], $matches) != 0) {
                        $cpu = floatval($matches[1]) + floatval($matches[2]);
                    }
                }
            }

            if (strpos(PHP_OS, 'Linux') !== false) {
                $result = explode("\n", shell_exec('iostat'));
                array_shift($result);
                array_shift($result);
                array_shift($result);
                if (isset($result[0])) {
                    $matches = array();
                    if (preg_match('#^\s*(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)#', $result[0], $matches) != 0) {
                        $cpu = floatval($matches[1]) + floatval($matches[2]) + floatval($matches[3]);
                    }
                }
            }

            / *  This technique is okay in theory, but there's too much rounding when we're looking at a narrow threshold
            sleep(2); // Let CPU recover a bit from our own script
            $result = explode("\n", shell_exec('ps -A -o %cpu'));
            $cpu = 0.0;
            foreach ($result as $r) {
                if (is_numeric(trim($cpu))) {
                    $cpu += floatval($r);
                }
            }
            * /

            if ($cpu !== null) {
                $threshold = 97.0;

                $this->assertTrue($cpu < $threshold, 'CPU utilisation is ' . float_format($cpu) . '%');
            }
        }
    }*/ 

    // High server uptime value
    /*public function testForPoorUptimeValue($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $data = shell_exec('uptime');

            $matches = array();
            if (preg_match('#load averages:\s*(\d+\.\d+)#', $data, $matches) != 0) {
                $uptime = floatval($matches[1]);
                $threshold = 20; // TODO: Make a config option
                $this->assertTrue($uptime < $threshold, '"uptime" (server load) is ' . float_format($uptime) . '%');
            }
        }
    }*/

    // High server I/O load
    /*public function testForHighIOLoad($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $load = null;

            if (strpos(PHP_OS, 'Linux') !== false) {
                $result = explode("\n", shell_exec('iostat'));
                array_shift($result);
                array_shift($result);
                array_shift($result);
                if (isset($result[0])) {
                    $matches = array();
                    if (preg_match('#^\s*(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)#', $result[0], $matches) != 0) {
                        $load = floatval($matches[4]);
                    }
                }
            }

            if ($load !== null) {
                $threshold = 80.0;

                $this->assertTrue($load < $threshold, 'I/O load is causing wait time of ' . float_format($load) . '%');
            }
        }
    }*/

    // Hanging (long-running) PHP/Apache processes (the process names to monitor would be configurable)
    /*public function testForHangingProcesses($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $commands_regexp = 'php\d*|php\d*-cgi|php\d*-fpm|php\d*.dSYM'; // TODO: Make configurable
            $threshold_minutes = 5; // TODO: Make configurable

            $ps_cmd = 'ps -ocomm,etime';
            //$ps_cmd .= ' -A';
            $result = explode("\n", shell_exec($ps_cmd));
            foreach ($result as $r) {
                $matches = array();
                if (preg_match('#^(' . $commands_regexp . ')\s+(\d+(:(\d+))*)\s*$#', $r, $matches) != 0) {
                    $seconds = 0;
                    $time_parts = array_reverse(explode(':', $matches[2]));
                    foreach ($time_parts as $i => $_time_part) {
                        $time_part = intval($_time_part);

                        switch ($i) {
                            case 0:
                                $seconds += $time_part;
                                break;

                            case 1:
                                $seconds += $time_part * 60;
                                break;

                            case 2:
                                $seconds += $time_part * 60 * 60;
                                break;

                            case 3:
                            default: // We assume anything else is days, we don't know what other units may be here, and it's longer than we care of anyway
                                $seconds += $time_part * 60 * 60 * 24;
                                break;
                        }
                    }

                    $cmd = $matches[1];

                    $this->assertTrue($seconds < 60 * $threshold_minutes, 'Process "' . $cmd . '" has been running for ' . display_time_period($seconds));
                }
            }
        }
    }*/

    // Low free RAM
    /*public function testForLowRAM($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $kb_free = null;

            $matches = array();

            if (strpos(PHP_OS, 'Darwin') !== false) {
                $data = shell_exec('vm_stat');
                if (preg_match('#^Pages free:\s*(\d+)#m', $data, $matches) != 0) {
                    $kb_free = intval($matches[1]) * 4;
                }
            }

            if (strpos(PHP_OS, 'Linux') !== false) {
                $data = shell_exec('free');
                if (preg_match('#^Mem:\s+(\d+)\s+(\d+)\s+(\d+)#m', $data, $matches) != 0) {
                    $kb_free = intval($matches[3]);
                }
            }

            if ($kb_free !== null) {
                $mb_threshold = 200; // TODO: Make configurable
                $this->assertTrue($kb_free > $mb_threshold * 1024, 'Server has less than 200MB of free RAM');
            }
        }
    }*/

    // Infected with Malware (configurable list of page-links)
    /*public function testForMalwareInfection($manual_checks = false, $automatic_repair = false)
    {
        // API https://developers.google.com/safe-browsing/v4/

        $key = 'AIzaSyBJyvgYzg-moqMRBZwhiivNxhYvafqMWas'; // TODO: Make configurable
        if ($key == '') {
            return;
        }

        require_code('json'); // Change in v11

        / *if ($this->is_local_domain()) {   TODO Re-enable
            return;
        }* /

        $page_links = array( // TODO: Make configurable
            ':',
        );

        $urls = array();
        foreach ($page_links as $page_link) {
            $_url = page_link_to_url($page_link);
            if (!empty($_url)) {
                $urls[] = array('url' => $_url);
            }
        }
        $urls = array(array('url' => 'http://www23.omrtw.com')); // TODO: This is just temporary test data
        //$urls = array(array('url' => 'http://example.com')); // TODO: This is just temporary test data

        $url = 'https://safebrowsing.googleapis.com/v4/threatMatches:find?key=' . urlencode(trim($key));

        require_code('version2');
        $data = array(
            'client' => array(
                'clientId' => 'Composr',
                'clientVersion' => get_version_dotted(),
            ),
            'threatInfo' => array(
                'threatTypes' => array('MALWARE', 'SOCIAL_ENGINEERING'),
                'platformTypes' => array('ANY_PLATFORM'),
                'threatEntryTypes' => array('URL'),
                'threatEntries' => $urls,
            ),
        );
        $_result = http_download_file($url, null, false, false, 'Composr', array(json_encode($data)), null, null, null, null, null, null, null, 200.0, true, null, null, null, 'application/json');

        $this->assertTrue(!in_array($GLOBALS['HTTP_MESSAGE'], array('401', '403')), 'Error with our Google Safe Browsing API key (' . $GLOBALS['HTTP_MESSAGE'] . ')');
        $this->assertTrue(!in_array($GLOBALS['HTTP_MESSAGE'], array('400', '501', '503', '504')), 'Internal error with our Google Safe Browsing check (' . $GLOBALS['HTTP_MESSAGE'] . ')');

        $ok = in_array($GLOBALS['HTTP_MESSAGE'], array('200'));
        if ($ok) {
            $result = json_decode($_result, true);

            if (empty($result['matches'])) {
                $this->assertTrue(true);
            } else {
                foreach ($result['matches'] as $match) {
                    $this->assertTrue(false, 'Malware advisory provided by Google ' . json_encode($match) . ' (https://developers.google.com/safe-browsing/v3/advisory)');
                }
            }
        }
    }*/

    // What if DNS not resolving
    /*public function testForMailIssues($manual_checks = false, $automatic_repair = false)
    {
        if ((php_function_allowed('getmxrr')) && (php_function_allowed('checkdnsrr'))) {
            $domains = array();
            $domains[preg_replace('#^.*@#', '', get_option('staff_address'))] = get_option('staff_address');
            $domains[preg_replace('#^.*@#', '', get_option('website_email'))] = get_option('website_email');
            if (addon_installed('tickets')) {
                $domains[preg_replace('#^.*@#', '', get_option('ticket_email_from'))] = get_option('ticket_email_from');
            }

            $domains = array('ocportal.com' => 'chris@ocportal.com'); // TODO

            foreach ($domains as $domain => $email) {
                if ($this->is_local_domain($domain)) {
                    continue;
                }

                $mail_hosts = array();
                $this->assertTrue(@getmxrr($domain, $mail_hosts), 'Cannot look up MX records for our ' . $email . ' e-mail address');

                foreach ($mail_hosts as $host) {
                    $this->assertTrue(checkdnsrr($host, 'A'), 'Mail server DNS does not seem to be setup properly for our ' . $email . ' e-mail address');

                    if ((php_function_allowed('fsockopen')) && (php_function_allowed('gethostbyname')) && (php_function_allowed('gethostbyaddr'))) {
                        // See if SMTP running
                        $socket = @fsockopen($host, 25);
                        $can_connect = ($socket !== false);
                        $this->assertTrue($can_connect, 'Cannot connect to SMTP server for ' . $email . ' address');
                        if ($can_connect) {
                            fread($socket, 1024);
                            fwrite($socket, 'HELO ' . $domain . "\n");
                            $data = fread($socket, 1024);
                            fclose($socket);

                            $matches = array();
                            $has_helo = preg_match('#^250 ([^\s]*)#', $data, $matches) != 0;
                            $this->assertTrue($has_helo, 'Cannot get HELO response from SMTP server for ' . $email . ' address');
                            if ($has_helo) {
                                $reported_host = $matches[1];

                                / *
                                $reverse_dns_host = gethostbyaddr(gethostbyname($host));  Fails way too much

                                $this->assertTrue($reported_host == $reverse_dns_host, 'HELO response from SMTP server (' . $reported_host . ') not matching reverse DNS (' . $reverse_dns_host . ') for ' . $email . ' address');
                                * /
                            }
                        }
                    }
                }

                // What if mailbox missing? Or generally e-mail not received
                if ($manual_checks) {
                    require_code('mail');
                    mail_wrap('Test', 'Test e-mail from Health Check', array($email));
                    $this->assertTrue(false, 'Manual check: An e-mail was sent to ' . $email . ', confirm it was received');
                }
            }
        }
    }*/

    // TODO: Other spam issues. Blacklisted? SPF issue?

    // What if DNS not resolving
    /*public function testForDNSResolutionIssues($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('checkdnsrr')) {
            $domain = parse_url(get_base_url(), PHP_URL_HOST);

            if (!$this->is_local_domain($domain)) {
                $this->assertTrue(checkdnsrr($domain, 'A'), 'DNS does not seem to be setup properly for our domain');
            }
        }
    }*/

    // Running on an expired domain name
    /*public function testForExpiringDomainName($manual_checks = false, $automatic_repair = false)
    {
        if (php_function_allowed('shell_exec')) {
            $domain = parse_url(get_base_url(), PHP_URL_HOST);

            if (!$this->is_local_domain($domain)) {
                $result = shell_exec('whois \'domain ' . escapeshellarg($domain) . '\'');

                $matches = array();
                if (preg_match('#(Expiry date|Expiration date|Expiration):\s*([^\s]*)#im', $result, $matches) != 0) {
                    $expiry = strtotime($matches[2]);
                    if ($expiry > 0) {
                        $this->assertTrue($expiry > time() - 60 * 60 * 24 * 7, 'Domain seems to be expiring within a week or already expired');
                    }
                }
            }
        }
    }*/

    // Site seems to be configured on a base URL which is not what a public web request sees is running on that base URL (security)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Cookie problems
    /*public function testForLargeCookies($manual_checks = false, $automatic_repair = false)
    {
        $url = $this->get_page_url();

        $headers = get_headers($url, 1);
        $found_has_cookies_cookie = false;
        foreach ($headers as $key => $vals) {
            if (strtolower($key) == strtolower('Set-Cookie')) {
                if (is_string($vals)) {
                    $vals = array($val);
                }

                foreach ($vals as $val) {
                    if (preg_match('#^has_cookies=1;#', $val) != 0) {
                        $found_has_cookies_cookie = true;
                    }

                    // Large cookies set
                    $_val = preg_replace('#^.*=#U', '', preg_replace('#; .*$#s', '', $val));
                    $this->assertTrue(strlen($_val) < 100, 'Cookie with over 100 bytes being set which is bad for performance');
                }

                // Too many cookies set
                $this->assertTrue(count($vals) < 8, '8 or more cookies are being set which is bad for performance');
            }
        }

        // Composr cookies not set
        $this->assertTrue($found_has_cookies_cookie, 'Cookies not being properly set');
    }*/

    // No recent activity on any 1 of a set of configured Twitter accounts
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // No recent activity on any 1 of a set of configured Facebook accounts
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Output pages are not gzipped
    /*public function testForUncompressed($manual_checks = false, $automatic_repair = false)
    {
        //set_option('gzip_output', '1');

        $url = $this->get_page_url();

        stream_context_set_default(array('http' => array('header' => 'Accept-Encoding: gzip')));
        $headers = get_headers($url, 1);
        $is_gzip = false;
        foreach ($headers as $key => $vals) {
            if (strtolower($key) == strtolower('Content-Encoding')) {
                if (is_string($vals)) {
                    $vals = array($val);
                }

                foreach ($vals as $val) {
                    if ($val == 'gzip') {
                        $is_gzip = true;
                    }
                }
            }
        }
        $this->assertTrue($is_gzip, 'Page gzip compression is not enabled/working, significantly wasting bandwidth for page loads');
    }*/

    // TODO: Static file gzip test (CSS, images, JS)

    // Composr version no longer supported
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // PHP version no longer supported
    /*public function testForUnsupportedPHP($manual_checks = false, $automatic_repair = false)
    {
        require_code('version2');

        $v = strval(PHP_MAJOR_VERSION) . '.' . strval(PHP_MINOR_VERSION);

        $this->assertTrue(is_php_version_supported($v), 'Unsupported PHP version ' . $v);
    }*/

    // Cache headers not set correctly on static resources like images or CSS or JavaScript
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Repeated logins by individual user (indicates a login problem)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Logins from the same username but different countries (indicates hacking)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Staff not doing their tasks as identified by items in the staff checklist
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Google Analytics configured but not in output HTML
    /*public function testForGANonPresent($manual_checks = false, $automatic_repair = false)
    {
        $ga = get_option('google_analytics');
        if (trim($ga) != '') {
            $data = $this->get_page_content();
            if ($data === null) {
                return;
            }

            $this->assertTrue(strpos($data, $ga) !== false, 'Google Analytics enabled but not in page output (themeing issue?)');
        }
    }*/
    // TODO: Check with API data being collected?

    // Crawl errors (not warnings) in Google Webmaster Tools
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // https://developers.google.com/webmaster-tools/search-console-api-original/
        // TODO
    }*/

    // TODO: Check cannot download secured files in .htaccess / web.config

    // TODO: No lorem ipsum or TODOs

    // TODO: Structured data tool https://search.google.com/structured-data/testing-tool/u/0/#url=https%3A%2F%2Fcompo.sr

    // OpenGraph tagging problem (see https://developers.facebook.com/tools/debug/sharing/)
    /*public function testForTODO($manual_checks = false, $automatic_repair = false)
    {
        // TODO
    }*/

    // Things wrong found by checking manually
    public function testForManualValidation($manual_checks = false, $automatic_repair = false)
    {
        if (!$manual_checks) {
            return;
        }

        $this->assertTrue(false, 'Check HTML5 validation https://validator.w3.org/ (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Check CSS validation https://jigsaw.w3.org/css-validator/ (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Check WCAG validation https://achecker.ca/ (take warnings with a pinch of salt, not every suggestion is appropriate)');

        $this->assertTrue(false, 'Check for speed issues https://developers.google.com/speed/pagespeed/insights (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Check SSL security https://www.ssllabs.com/ssltest/ (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Check SEO https://seositecheckup.com/ (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Check for search issues in Google Webmaster Tools https://www.google.com/webmasters/tools/home');

        $this->assertTrue(false, 'Do a general check https://www.woorank.com/ (take warnings with a pinch of salt, not every suggestion is appropriate)');
        $this->assertTrue(false, 'Do a general check https://website.grader.com/ (take warnings with a pinch of salt, not every suggestion is appropriate)');

        $this->assertTrue(false, 'Test in Firefox');
        $this->assertTrue(false, 'Test in Google Chrome');
        $this->assertTrue(false, 'Test in IE10');
        $this->assertTrue(false, 'Test in IE11');
        $this->assertTrue(false, 'Test in Microsoft Edge');
        $this->assertTrue(false, 'Test in Safari');
        $this->assertTrue(false, 'Test in Google Chrome (mobile)');
        $this->assertTrue(false, 'Test in Safari (mobile)');
    }

    // --

    // TODO: Add testForManualValidation etc links to maintenance-sheet

    // TODO: Test everything on compo.sr
}
