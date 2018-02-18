<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

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
class overused_globals_test_set extends cms_test_case
{
    public function testUnusedGlobals()
    {
        $matches = array();
        $found_globals = array();
        $documented_globals = array();
        $sanctified_globals = array();

        require_code('files2');

        $files = get_directory_contents(get_file_base(), '', IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_NONBUNDLED | IGNORE_FLOATING, true, true, array('php'));
        $files[] = 'install.php';
        foreach ($files as $path) {
            $done_for_file = array();

            $c = @file_get_contents(get_file_base() . '/' . $path);
            if ($c === false) {
                continue; // Probably a race condition between unit tests
            }
            if ($path != 'install.php') {
                if (strpos($c, '@chdir($FILE_BASE);') !== false) {
                    continue;
                }
            }

            // Front-end controller script, will have lots of globals

            // global $FOO
            $num_matches = preg_match_all('#^\s*global ([^;]*);#m', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $vars = explode(',', $matches[1][$i]);
                foreach ($vars as $var) {
                    $global = ltrim($var, ' $');

                    if (isset($done_for_file[$global])) {
                        continue;
                    }

                    if (!isset($found_globals[$global])) {
                        $found_globals[$global] = 0;
                    }
                    $found_globals[$global]++;

                    $done_for_file[$global] = true;
                }
            }

            // $GLOBALS['FOO']
            $num_matches = preg_match_all('#\$GLOBALS\[\'(\w+)\'\]#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $global = $matches[1][$i];

                if (isset($done_for_file[$global])) {
                    continue;
                }

                if (!isset($found_globals[$global])) {
                    $found_globals[$global] = 0;
                }
                $found_globals[$global]++;

                $done_for_file[$global] = true;
            }

            // Global documentation, which makes a global 'sanctified' for use in many files
            $num_matches = preg_match_all('#@global\s+[^\s]+\s+\$(\w+)#', $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $global = $matches[1][$i];
                $sanctified_globals[$global] = true;
            }
        }

        ksort($found_globals);

        foreach ($found_globals as $global => $count) {
            if ((!isset($sanctified_globals[$global])) && (strpos($global, '_CACHE') === false) && (strpos($global, 'DEV_MODE') === false) && ($global != 'SITE_INFO') && ($global != 'FILE_BASE') && ($global != 'LAST_TOPIC_ID') && ($global != 'LAST_TOPIC_IS_NEW') && ($global != 'RELATIVE_PATH') && ($global != '_CREATED_FILES') && ($global != '_MODIFIED_FILES')) {
                $this->assertTrue($count <= 6, 'Don\'t over-use global variables (' . $global . ', ' . integer_format($count) . ' files using).');
            }
        }
    }
}
