<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/*EXTRA FUNCTIONS: shell_exec|escapeshellarg*/

// php _tests/index.php _bash_parser

/**
 * Composr test case class (unit testing).
 */
class _bash_parser_test_set extends cms_test_case
{
    public function testValidCode()
    {
        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }

        require_code('files2');
        $php_path = find_php_path();
        $files = get_directory_contents(get_file_base(), '', IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, array('php'));
        $files[] = 'install.php';
        foreach ($files as $path) {
            if (basename($path) == 'phpstub.php') {
                continue;
            }

            // NB: php-no-ext bit works around bug in Windows version of PHP with slow startup. Make a ../php-no-ext/php.ini file with no extensions listed for loading
            $message = shell_exec($php_path . ' -l ' . escapeshellarg(get_file_base() . '/' . $path) . ' -c ' . escapeshellarg(get_file_base() . '/../php-no-ext'));
            $this->assertTrue(strpos($message, 'No syntax errors detected') !== false, $message . ' (' . $path . ')');
        }
    }
}
