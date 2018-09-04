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
 * @package    core_addon_management
 */

/**
 * Hook class.
 */
class Hook_ajax_tree_choose_composr_homesite_addon
{
    /**
     * This will get the XML file from compo.sr.
     *
     * @param  ?ID_TEXT $id The ID to do under (null: root)
     * @param  ?ID_TEXT $default The ID to select by default (null: none)
     * @return string The XML file
     */
    protected function get_file($id, $default)
    {
        $stub = (get_param_integer('localhost', 0) == 1) ? get_base_url() : 'http://compo.sr';
        $v = 'Version ' . float_to_raw_string(cms_version_number(), 2, true);
        if ($id !== null) {
            $v = $id;
        }
        $url = $stub . '/data/ajax_tree.php?hook=choose_download&id=' . urlencode($v) . '&file_type=tar';
        if ($default !== null) {
            $url .= '&default=' . urlencode($default);
        }
        require_code('character_sets');
        $http_result = cms_http_request($url);
        $contents = $http_result->data;
        $utf = ($http_result->charset == 'utf-8');
        require_code('character_sets');
        $contents = convert_to_internal_encoding($contents, $http_result->charset);
        $contents = preg_replace('#^\s*<' . '\?xml version="1.0" encoding="[^"]*"\?' . '><request>#' . ($utf ? 'U' : ''), '', $contents);
        $contents = preg_replace('#</request>#' . ($utf ? 'U' : ''), '', $contents);
        $contents = preg_replace('#<category [^>]*has_children="false"[^>]*>[^>]*</category>#' . ($utf ? 'U' : ''), '', $contents);
        $contents = preg_replace('#<category [^>]*title="Manual install required"[^>]*>[^>]*</category>#' . ($utf ? 'U' : ''), '', $contents);
        return $contents;
    }

    /**
     * Run function for ajax-tree hooks. Generates XML for a tree list, which is interpreted by JavaScript and expanded on-demand (via new calls).
     *
     * @param  ?ID_TEXT $id The ID to do under (null: root)
     * @param  array $options Options being passed through
     * @param  ?ID_TEXT $default The ID to select by default (null: none)
     * @return string XML in the special category,entry format
     */
    public function run($id, $options, $default = null)
    {
        return $this->get_file($id, $default);
    }

    /**
     * Generate a simple selection list for the ajax-tree hook. Returns a normal <select> style <option>-list, for fallback purposes.
     *
     * @param  ?ID_TEXT $id The ID to do under (null: root) - not always supported
     * @param  array $options Options being passed through
     * @param  ?ID_TEXT $it The ID to select by default (null: none)
     * @param  string $prefix Prefix titles with this
     * @return Tempcode The nice list
     */
    public function simple($id, $options, $it = null, $prefix = '')
    {
        $file = $this->get_file($id, $it);

        $it_exp = ($it === null) ? array() : explode(',', $it);

        $list = new Tempcode();
        if ($id === null) { // Root, needs an NA option
            $list->attach(form_input_list_entry('', false, do_lang_tempcode('NA_EM')));
        }

        $matches = array();

        $num_matches = preg_match_all('#<entry id="(\d+)"[^<>]* title="([^"]+)"#', $file, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $list->attach(form_input_list_entry('https://compo.sr/site/dload.php?id=' . urlencode($matches[1][$i]), in_array($matches[1][$i], $it_exp), $prefix . $matches[2][$i]));
        }

        $num_matches = preg_match_all('#<category id="(\d+)" title="([^"]+)"#', $file, $matches);
        for ($i = 0; $i < $num_matches; $i++) {
            $list2 = $this->simple($matches[1][$i], $options, $it, $matches[2][$i] . ' > ');
            $list->attach($list2);
        }
        return $list;
    }
}
