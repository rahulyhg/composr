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

// php _tests/index.php _find_broken_screen_links

// This covers internal links. Also see _broken_links, which does scanning for external links.

/**
 * Composr test case class (unit testing).
 */
class _find_broken_screen_links_test_set extends cms_test_case
{
    // This test is not necessarily required to pass, but it may hint at issues; best just to make it pass anyway (it does at the time at writing)
    public function testScreenLinks()
    {
        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }

        $found = array();
        require_code('files2');
        $files = get_directory_contents(get_file_base(), '', IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING, true, true, array('php'));
        $files[] = 'install.php';
        foreach ($files as $path) {
            $c = file_get_contents(get_file_base() . '/' . $path);
            $matches = array();
            $num_matches = preg_match_all("#build_url\(array\('page'\s*=>\s*'(\w+)',\s*'type'\s*=>\s*'(\w+)'#", $c, $matches);
            for ($i = 0; $i < $num_matches; $i++) {
                $page = $matches[1][$i];
                $type = $matches[2][$i];
                $all = $matches[0][$i];
                $found[$all] = array($page, $type); // To de-duplicate
            }
        }

        foreach ($found as $all => $d) {
            list($page, $type) = $d;

            if ($page != '_SELF') {
                $zone = get_module_zone($page);
                $path = _get_module_path($zone, $page);
                $module_path = zone_black_magic_filterer(($zone == '') ? ('pages/modules/' . filter_naughty_harsh($page) . '.php') : (filter_naughty($zone) . '/pages/modules/' . filter_naughty_harsh($page) . '.php'), true);
                if (!is_file($module_path)) {
                    $this->assertTrue(false, 'Missing module ' . $page);
                }
                $c2 = file_get_contents($module_path);
                if (strpos($c2, "'{$type}'") === false) {
                    if (strpos($c2, 'extends Standard_crud_module') !== false && in_array($type, array('add', '_add', 'edit', '_edit', '__edit', 'add_category', '_add_category', 'edit_category', '_edit_category', '__edit_category', 'add_other', '_add_other', 'edit_other', '_edit_other', '__edit_other'))) {
                        continue;
                    }

                    $this->assertTrue(false, 'Linking error with ' . $all);
                }
            }
        }
    }
}
