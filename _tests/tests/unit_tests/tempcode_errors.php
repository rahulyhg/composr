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

/**
 * Composr test case class (unit testing).
 */
class tempcode_errors_test_set extends cms_test_case
{
    public function testNoBadPassed()
    {
        require_code('files');

        disable_php_memory_limit();

        $paths = array(
            get_file_base() . '/themes/default/templates',
            get_file_base() . '/themes/default/templates_custom',
        );
        foreach ($paths as $path) {
            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if (strtolower(substr($file, -4)) == '.tpl') {
                    $c = file_get_contents($path . '/' . $file);

                    $this->assertTrue(strpos($c, '{+START,IF_PASSED,{') === false, 'Bad IF_PASSED parameter in ' . $file . ' template');
                    $this->assertTrue(strpos($c, '{+START,IF_NON_PASSED,{') === false, 'Bad IF_NON_PASSED parameter in ' . $file . ' template');
                    $this->assertTrue(preg_match('#\{\+START,IF,[A-Z]#', $c) == 0, 'Bad IF parameter in ' . $file . ' template');
                }
            }
            closedir($dh);
        }
    }
}
