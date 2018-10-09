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
 * @package    health_check
 */

/**
 * Hook class.
 */
class Hook_health_check_upkeep extends Hook_Health_Check
{
    protected $category_label = 'Upkeep';

    /**
     * Standard hook run function to run this category of health checks.
     *
     * @param  ?array $sections_to_run Which check sections to run (null: all)
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     * @return array A pair: category label, list of results
     */
    public function run($sections_to_run, $check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        $this->process_checks_section('testComposrVersion', brand_name() . ' version', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testPHPVersion', 'PHP version', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testAdminAccountStaleness', 'Admin account staleness', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testCopyrightDate', 'Copyright date', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);
        $this->process_checks_section('testStaffChecklist', 'Staff checklist', $sections_to_run, $check_context, $manual_checks, $automatic_repair, $use_test_data_for_pass, $urls_or_page_links, $comcode_segments);

        return array($this->category_label, $this->results);
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testComposrVersion($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        if (cms_version_minor() == '?') {
            return;
        }

        switch (get_option('hc_version_check')) {
            case 'deprecated':
                $is_discontinued = $this->call_composr_homesite_api('is_release_discontinued', array('version' => cms_version_number()));
                $this->assertTrue($is_discontinued !== true, 'The ' . brand_name() . ' version is discontinued');
                break;

            case 'uptodate':
                require_code('version2');
                $info = get_future_version_information();
                $this->assertTrue(strpos($info->evaluate(), '<strong>not</strong>') === false, 'The ' . brand_name() . ' version is not up-to-date');
                break;
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testPHPVersion($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        require_code('version2');

        $v = strval(PHP_MAJOR_VERSION) . '.' . strval(PHP_MINOR_VERSION);

        $this->assertTrue(is_php_version_supported($v) !== false, 'Unsupported PHP version ' . $v);
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testAdminAccountStaleness($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        $threshold = time() - 60 * 60 * 24 * intval(get_option('hc_admin_stale_threshold'));

        $admin_groups = $GLOBALS['FORUM_DRIVER']->get_super_admin_groups();
        $members = $GLOBALS['FORUM_DRIVER']->member_group_query($admin_groups);
        foreach ($members as $member) {
            $member_id = $GLOBALS['FORUM_DRIVER']->mrow_id($member);
            $last_visit = $GLOBALS['FORUM_DRIVER']->mrow_lastvisit($member);
            $username = $GLOBALS['FORUM_DRIVER']->mrow_username($member);

            $diff = ($last_visit === null) ? '(never)' : display_time_period(time() - $last_visit);
            if (($automatic_repair) && (get_forum_type() == 'cns')) {
                $GLOBALS['FORUM_DB']->query_update('f_members', array('m_validated' => 0), array('id' => $member_id), '', 1);
                $this->assertTrue(($last_visit === null) || ($last_visit > $threshold), 'Admin account "' . $username . '" not logged in for a long time @ ' . $diff . ', automatically marked as non-validated');
            } else {
                $this->assertTrue(($last_visit === null) || ($last_visit > $threshold), 'Admin account "' . $username . '" not logged in for a long time @ ' . $diff . ', consider deleting');
            }
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testCopyrightDate($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context == CHECK_CONTEXT__INSTALL) {
            return;
        }
        if ($check_context == CHECK_CONTEXT__SPECIFIC_PAGE_LINKS) {
            return;
        }

        $data = $this->get_page_content();
        if ($data === null) {
            $this->stateCheckSkipped('Could not download page from website');
            return;
        }

        if ((date('m-d') == '00-01') || (date('m-d') == '12-31')) {
            // Allow for inconsistencies around new year
            $this->stateCheckSkipped('Too close to new year to run check');
            return;
        }

        $current_year = intval(date('Y', tz_time(time(), get_server_timezone())));

        $year = null;
        $matches = array();
        if (preg_match('#(Copyright|&copy;' . ((get_charset() == 'utf-8') ? ('|' . hex2bin('c2a9')) : '') . ').*(\d{4})[^\d]{1,10}(\d{4})#', $data, $matches) != 0) {
            $_year_first = intval($matches[2]);
            $_year = intval($matches[3]);
            if (($_year - $_year_first > 0) && ($_year - $_year_first < 100) && ($_year > $current_year - 10) && ($_year <= $current_year)) {
                $year = $_year;
            }
        } elseif (preg_match('#(Copyright|&copy;' . ((get_charset() == 'utf-8') ? ('|' . hex2bin('c2a9')) : '') . ').*(\d{4})#', $data, $matches) != 0) {
            $_year = intval($matches[2]);
            if (($_year > $current_year - 10) && ($_year <= $current_year)) {
                $year = $_year;
            }
        }

        if ($year !== null) {
            $this->assertTrue($year == $current_year, 'Copyright date seems outdated');
        }
    }

    /**
     * Run a section of health checks.
     *
     * @param  integer $check_context The current state of the website (a CHECK_CONTEXT__* constant)
     * @param  boolean $manual_checks Mention manual checks
     * @param  boolean $automatic_repair Do automatic repairs where possible
     * @param  ?boolean $use_test_data_for_pass Should test data be for a pass [if test data supported] (null: no test data)
     * @param  ?array $urls_or_page_links List of URLs and/or page-links to operate on, if applicable (null: those configured)
     * @param  ?array $comcode_segments Map of field names to Comcode segments to operate on, if applicable (null: N/A)
     */
    public function testStaffChecklist($check_context, $manual_checks = false, $automatic_repair = false, $use_test_data_for_pass = null, $urls_or_page_links = null, $comcode_segments = null)
    {
        if ($check_context != CHECK_CONTEXT__LIVE_SITE) {
            return;
        }

        if (!$manual_checks) {
            return;
        }

        require_code('blocks/main_staff_checklist');

        $hook_obs = find_all_hook_obs('blocks', 'main_staff_checklist', 'Hook_checklist_');
        foreach ($hook_obs as $hook => $object) {
            $ret = $object->run();

            foreach ($ret as $r) {
                list(, $seconds_due_in, $num_to_do) = $r;

                if ($seconds_due_in !== null) {
                    $ok = ($seconds_due_in >= 0);
                    $this->assertTrue($ok, 'Staff checklist items for [tt]' . $hook . '[/tt] due ' . (($seconds_due_in == -1) ? '(ASAP)' : display_time_period($seconds_due_in)) . ' ago');
                    break;
                }

                if ($num_to_do !== null) {
                    $ok = ($num_to_do == 0);
                    $this->assertTrue($ok, 'Staff checklist items for [tt]' . $hook . '[/tt], ' . integer_format($num_to_do) . ' item(s)');
                    break;
                }
            }
        }
    }
}
