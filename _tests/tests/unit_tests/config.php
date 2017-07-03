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
class config_test_set extends cms_test_case
{
    public function testMissingOptions()
    {
        require_code('files');

        $matches = array();
        $done = array();

        $hooks = find_all_hooks('systems', 'config');

        require_code('files2');
        $contents = get_directory_contents(get_file_base());

        foreach ($contents as $f) {
            if (should_ignore_file($f, IGNORE_CUSTOM_DIR_GROWN_CONTENTS)) {
                continue;
            }

            $file_type = get_file_extension($f);

            if ($file_type == 'php') {
                $c = file_get_contents(get_file_base() . '/' . $f);

                $num_matches = preg_match_all('#get_option\(\'([^\']+)\'\)#', $c, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $hook = $matches[1][$i];

                    if (isset($done[$hook])) {
                        continue;
                    }

                    $this->assertTrue(isset($hooks[$hook]), 'Missing referenced config option (.php): ' . $hook);

                    $done[$hook] = true;
                }
            }

            if ($file_type == 'tpl' || $file_type == 'txt' || $file_type == 'css' || $file_type == 'js') {
                $c = file_get_contents(get_file_base() . '/' . $f);

                $num_matches = preg_match_all('#\{\$CONFIG_OPTION[^\w,]*,(\w+)\}#', $c, $matches);
                for ($i = 0; $i < $num_matches; $i++) {
                    $hook = $matches[1][$i];

                    if (isset($done[$hook])) {
                        continue;
                    }

                    $this->assertTrue(isset($hooks[$hook]), 'Missing referenced config option (' . $file_type . '): ' . $hook);

                    $done[$hook] = true;
                }
            }
        }
    }

    public function testConfigHookCompletenessAndConsistency()
    {
        require_code('files');

        $settings_needed = array(
            'human_name' => 'string',
            'type' => 'string',
            'category' => 'string',
            'group' => 'string',
            'explanation' => 'string',
            'shared_hosting_restricted' => 'string',
            'list_options' => 'string',
            'addon' => 'string',
        );
        $settings_optional = array(
            'theme_override' => 'boolean',
            'order_in_category_group' => 'integer',
            'required' => 'boolean',
        );
    }

    public function testAddonCategorisationConsistency()
    {
        $hooks = find_all_hook_obs('systems', 'config', 'Hook_config_');
        foreach ($hooks as $hook => $ob) {
            $details = $ob->get_details();
            foreach ($settings_needed as $setting => $type) {
                $this->assertTrue(array_key_exists($setting, $details), 'Missing setting: ' . $setting . ' in ' . $hook);
                if (array_key_exists($setting, $details)) {
                    $this->assertTrue(gettype($details[$setting]) == $type, 'Incorrect data type for: ' . $setting . ' in ' . $hook);
                }
            }
            foreach ($settings_optional as $setting => $type) {
                if (array_key_exists($setting, $details)) {
                    $this->assertTrue(gettype($details[$setting]) == $type, 'Incorrect data type for: ' . $setting . ' in ' . $hook);
                }
            }

            foreach (array_keys($details) as $setting) {
                $this->assertTrue(array_key_exists($setting, $settings_needed) || array_key_exists($setting, $settings_optional), 'Unknown setting: ' . $setting);
            }

            if (!empty($details['theme_override'])) {
                $this->assertTrue(in_array($details['type'], array('line', 'tick')), 'Invalid config input type for a theme-overridable option: ' . $setting);
            }

            $path = get_file_base() . '/sources/hooks/systems/config/' . $hook . '.php';
            if (!is_file($path)) {
                $path = get_file_base() . '/sources_custom/hooks/systems/config/' . $hook . '.php';
            }
            $file_contents = file_get_contents($path);

            $expected_addon = preg_replace('#^.*@package\s+(\w+).*$#s', '$1', $file_contents);
            $this->assertTrue($details['addon'] == $expected_addon, 'Addon mismatch for ' . $hook);

            $this->assertTrue($details['addon'] != 'core', 'Don\'t put config options in core, put them in core_configuration - ' . $hook);
        }

        require_code('files2');
        $files = get_directory_contents(get_file_base());
        foreach ($files as $f) {
            if (should_ignore_file($f, IGNORE_CUSTOM_DIR_GROWN_CONTENTS)) {
                continue;
            }

            $file_type = get_file_extension($f);

            if ($file_type == 'php' || $file_type == 'tpl' || $file_type == 'txt' || $file_type == 'css' || $file_type == 'js') {
                $c = file_get_contents(get_file_base() . '/' . $f);

                foreach (array_keys($hooks) as $hook) {
                    if ($hook == 'description') {
                        // Special case - we have a config option named 'description', and also a theme setting named 'description' -- they are separate
                    }

                    if (strpos($c, $hook) === false) {
                        continue;
                    }

                    require_code('hooks/systems/config/' . $hook);
                    $ob = object_factory('Hook_config_' . $hook);
                    $details = $ob->get_details();

                    if ($file_type == 'php') {
                        if (!empty($details['theme_override'])) {
                            $this->assertTrue(strpos($c, 'get_option(\'' . $hook . '\'') === false, $hook . ' must be accessed as a theme option (.php): ' . $f);
                        } else {
                            $this->assertTrue(strpos($c, 'get_theme_option(\'' . $hook . '\'') === false, $hook . ' must not be accessed as a theme option (.php): ' . $f);
                        }
                    }

                    elseif ($file_type == 'tpl' || $file_type == 'txt' || $file_type == 'css' || $file_type == 'js') {
                        if (!empty($details['theme_override'])) {
                            $this->assertTrue(preg_match('#\{\$CONFIG_OPTION[^\w,]*,' . $hook . '\}#', $c) == 0, $hook . ' must be accessed as a theme option: ' . $f);
                        } else {
                            $this->assertTrue(preg_match('#\{\$THEME_OPTION[^\w,]*,' . $hook . '\}#', $c) == 0, $hook . ' must not be accessed as a theme option: ' . $f);
                        }
                    }
                }
            }
        }
    }

    public function testListConfigConsistency()
    {
        $hooks = find_all_hook_obs('systems', 'config', 'Hook_config_');
        foreach ($hooks as $hook => $ob) {
            $details = $ob->get_details();
            if ($details['type'] == 'list') {
                $list = explode('|', $details['list_options']);
                $this->assertTrue(in_array($ob->get_default(), $list));
            }
        }
    }

    public function testReasonablePerCategory()
    {
        $categories = array();

        $hooks = find_all_hook_obs('systems', 'config', 'Hook_config_');
        foreach ($hooks as $hook => $ob) {
            $details = $ob->get_details();
            if (!isset($categories[$details['category']])) {
                $categories[$details['category']] = 0;
            }
            $categories[$details['category']]++;
        }

        foreach ($categories as $category => $count) {
            if (in_array($category, array('TRANSACTION_FEES'))) { // Exceptions
                continue;
            }

            $this->assertTrue($count > 3, $category . ' only has ' . integer_format($count));
            $this->assertTrue($count < 160, $category . ' has as much as ' . integer_format($count)); // max_input_vars would not like a high number
        }
    }

    public function testConsistentGroupOrdering()
    {
        $categories = array();

        $hooks = find_all_hook_obs('systems', 'config', 'Hook_config_');
        foreach ($hooks as $hook => $ob) {
            $details = $ob->get_details();
            if (!isset($categories[$details['category']])) {
                $categories[$details['category']] = array();
            }
            if (!isset($categories[$details['category']][$details['group']])) {
                $categories[$details['category']][$details['group']] = array();
            }
            $categories[$details['category']][$details['group']][] = $details;
        }

        foreach ($categories as $category => $group) {
            foreach ($group as $group_name => $options) {
                $has_orders = null;
                $orders = array();

                foreach ($options as $option) {
                    $_has_orders = isset($option['order_in_category_group']);
                    if ($has_orders !== null) {
                        if ($has_orders != $_has_orders) {
                            $this->assertTrue(false, "'category' => '" . $category . "'" . ', ' . "'group' => '" . $group_name . "'" . ', has inconsistent ordering settings (some set, some not)');
                            break;
                        }
                    } else {
                        $has_orders = $_has_orders;
                    }

                    if ($has_orders) {
                        if (isset($orders[$option['order_in_category_group']])) {
                            $this->assertTrue(false, $category . '/' . $group_name . ' has duplicated order for ' . strval($option['order_in_category_group']));
                        }

                        $orders[$option['order_in_category_group']] = true;
                    }
                }
            }
        }
    }
}
