<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

// Pass &debug=1 for extra checks that would not be expected to ever consistently pass

/**
 * Composr test case class (unit testing).
 */
class css_beta_test_set extends cms_test_case
{
    public function testCorrectSetAsBeta()
    {
        $only_theme = get_param_string('only_theme', null);

        require_code('themes2');

        $themes = find_all_themes();
        foreach (array_keys($themes) as $theme) {
            if ($theme == '_unnamed_') {
                continue;
            }

            if (($only_theme !== null) && ($theme != $only_theme)) {
                continue;
            }

            $directories = array(
                 get_file_base() . '/themes/' . $theme . '/css_custom',
                 get_file_base() . '/themes/' . $theme . '/css',
            );

            $in_beta = array(
                'filter:',
                'flex-wrap:',
                'flex-grow:',
                'order:',
                'user-select:',
                'text-size-adjust:',
                'text-overflow:',
                'touch-action:',
                'appearance:',

                // For specific properties
                'display: flex',
            );
            // ^ Also keep in sync with BETA_CSS_PROPERTY.php comment

            foreach ($directories as $dir) {
                $d = opendir($dir);
                while (($e = readdir($d)) !== false) {
                    if (substr($e, -4) == '.css') {
                        $contents = file_get_contents($dir . '/' . $e);

                        $matches = array();
                        $found = preg_match_all('#\{\$BETA_CSS_PROPERTY,(.*)\}#i', $contents, $matches);
                        for ($i = 0; $i < $found; $i++) {
                            $property_line = $matches[1][$i];

                            $is_in_beta = false;
                            foreach ($in_beta as $_property) {
                                if (substr($property_line, 0, strlen($_property)) == $_property) {
                                    $is_in_beta = true;
                                }
                            }

                            $this->assertTrue($is_in_beta, 'Property ' . $property_line . ' should *not* be defined as beta in ' . $e . ' for theme ' . $theme);
                        }

                        foreach ($in_beta as $property) {
                            if ($property == 'display') {
                                continue;
                            }

                            $is_as_beta = (strpos($contents, "\t" . $property) === false) || (strpos($contents, ' ' . $property) === false);
                            $this->assertTrue($is_as_beta, 'Property ' . $property . ' should be defined as beta in ' . $e . ' for theme ' . $theme);
                        }
                    }
                }
                closedir($d);
            }
        }
    }
}