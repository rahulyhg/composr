<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_themeing
 */

/**
 * Hook class.
 */
class Hook_ajax_tree_choose_theme_files
{
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
        if (!has_actual_page_access(get_member(), 'admin_themes', get_module_zone('admin_themes'))) {
            access_denied('I_ERROR');
        }

        $theme = get_param_string('theme');

        $out = '';

        require_lang('themes');
        require_code('themes2');
        require_code('files');

        if ($id === null) {
            $top_level = array(
                'templates' => array(do_lang('TEMPLATES_HTML'), 'DOC_TEMPLATES'),
                'templates-related' => array(do_lang('TEMPLATES_HTML_RELATED'), 'DOC_TEMPLATES_RELATED'),
                'css' => array(do_lang('TEMPLATES_CSS'), 'DOC_CSS'),
                'css-related' => array(do_lang('TEMPLATES_CSS_RELATED'), 'DOC_TEMPLATES_RELATED'),
                'javascript' => array(do_lang('TEMPLATES_JAVASCRIPT'), 'DOC_TEMPLATES_JAVASCRIPT'),
                'javascript-related' => array(do_lang('TEMPLATES_JAVASCRIPT_RELATED'), 'DOC_TEMPLATES_RELATED'),
                'xml' => array(do_lang('TEMPLATES_XML'), 'DOC_TEMPLATES_XML'),
                'xml-related' => array(do_lang('TEMPLATES_XML_RELATED'), 'DOC_TEMPLATES_RELATED'),
                'text' => array(do_lang('TEMPLATES_TEXT'), 'DOC_TEMPLATES_TEXT'),
                'text-related' => array(do_lang('TEMPLATES_TEXT_RELATED'), 'DOC_TEMPLATES_RELATED'),
                'addons' => array(do_lang('addons:ADDONS'), 'DOC_TEMPLATES_BY_ADDON'),
            );

            $test = $GLOBALS['SITE_DB']->query_select_value('theme_screen_tree', 'COUNT(*)');
            if ($test >= 0) {
                $top_level['screens'] = array(do_lang('SCREEN_TREES'), 'DOC_TEMPLATE_EDITOR_SCREENS');
            }

            $zones = find_all_zones(false, true, false, 0, 10);
            if (count($zones) < 10) {
                foreach ($zones as $zone_details) {
                    $top_level[$zone_details[0] . ':'] = array(do_lang('ZONE_IS', $zone_details[1]), 'DOC_TEMPLATE_EDITOR_COMCODE_PAGES');
                }
            }

            foreach ($top_level as $_id => $_bits) {
                list($title, $description_lang_string) = $_bits;
                $description_html = comcode_lang_string($description_lang_string);

                $out .= '
                <category
                    id="' . xmlentities($_id) . '"
                    serverid="' . xmlentities($_id) . '"
                    title="' . xmlentities($title) . '"
                    has_children="true"
                    selectable="false"
                    description_html="' . xmlentities($description_html->evaluate()) . '"
                ></category>';
            }
        } else {
            $is_related = (substr($id, -8) == '-related');
            if ($is_related) {
                $id = substr($id, 0, strlen($id) - 8);
                $relations = collapse_2d_complexity('rel_a', 'cnt', $GLOBALS['SITE_DB']->query_select('theme_template_relations', array('rel_a', 'COUNT(*) AS cnt'), null, 'GROUP BY rel_a'));
            }

            switch ($id) {
                case 'templates':
                case 'css':
                case 'javascript':
                case 'xml':
                case 'text':
                    $subdir = $id;

                    $action_log_times = $this->load_actionlog_times_templates($theme);

                    $template_files = get_template_files_list($theme, $subdir);
                    foreach (array_keys($template_files) as $_template_file) {
                        $template_file_path = find_template_path($_template_file, $subdir, $theme);
                        if (empty($template_file_path)) {
                            continue;
                        }
                        $template_file = $subdir . '/' . $_template_file;

                        $description_html = $this->get_template_details_table($theme, $template_file, $template_file_path, $action_log_times);

                        if ($is_related) {
                            $has_children = isset($relations[$template_file]);

                            if ($has_children) {
                                $out .= '
                                <category
                                    id="' . xmlentities($this->get_next_id()) . '"
                                    serverid="' . xmlentities($template_file) . '"
                                    title="' . xmlentities($_template_file) . '"
                                    has_children="' . ($has_children ? 'true' : 'false') . '"
                                    selectable="true"
                                    description_html="' . xmlentities($description_html->evaluate()) . '"
                                ></category>';
                            }
                        } else {
                            list($img_url, $img_url_2, $template_file_shortened) = $this->get_template_file_icons($template_file);

                            $out .= '
                            <entry
                                id="' . xmlentities($this->get_next_id()) . '"
                                serverid="' . xmlentities($template_file) . '"
                                title="' . xmlentities($template_file_shortened) . '"
                                selectable="true"
                                description_html="' . xmlentities($description_html->evaluate()) . '"
                                img_url="' . xmlentities($img_url) . '"
                                img_url_2="' . xmlentities($img_url_2) . '"
                            ></entry>';
                        }
                    }
                    break;

                case 'addons':
                    $addons = find_all_hooks('systems', 'addon_registry');
                    foreach (array_keys($addons) as $addon) {
                        $has_children = (count($this->templates_for_addons($addon)) > 0);

                        $out .= '
                        <category
                            id="' . xmlentities($this->get_next_id()) . '"
                            serverid="' . xmlentities('<' . $addon . '>') . '"
                            title="' . xmlentities($addon) . '"
                            has_children="' . ($has_children ? 'true' : 'false') . '"
                            selectable="true"
                        ></category>';
                    }
                    break;

                case 'screens':
                    $screens = $GLOBALS['SITE_DB']->query_select('theme_screen_tree', array('page_link'), null, 'ORDER BY page_link');
                    foreach ($screens as $screen) {
                        $page_link = $screen['page_link'];

                        $out .= '
                        <category
                            id="' . xmlentities($page_link) . '"
                            serverid="' . xmlentities($page_link) . '"
                            title="' . xmlentities($page_link) . '"
                            has_children="true"
                            selectable="false"
                        ></category>';
                    }
                    break;

                default:
                    if (preg_match('#^(<\w+>)$#', $id) != 0) {
                        // Must be browsing an addon

                        $action_log_times = $this->load_actionlog_times_templates($theme);

                        $templates = array_keys($this->templates_for_addons(trim($id, '<>')));
                        foreach ($templates as $template) {
                            $template_file_path = find_template_path(basename($template), dirname($template), $theme);
                            if (empty($template_file_path)) {
                                continue;
                            }

                            $description_html = $this->get_template_details_table($theme, $template, $template_file_path, $action_log_times);

                            list($img_url, $img_url_2, $template_file_shortened) = $this->get_template_file_icons($template);

                            $out .= '
                            <entry
                                id="' . xmlentities($this->get_next_id()) . '"
                                serverid="' . xmlentities($template) . '"
                                title="' . xmlentities($template_file_shortened) . '"
                                selectable="true"
                                description_html="' . xmlentities($description_html->evaluate()) . '"
                                img_url="' . xmlentities($img_url) . '"
                                img_url_2="' . xmlentities($img_url_2) . '"
                            ></entry>';
                        }
                    }

                    elseif (preg_match('#^(templates|css|javascript|xml|text)/\w+\.(tpl|css|js|xml|txt)$#', $id) != 0) {
                        // Must be for related templates

                        $action_log_times = $this->load_actionlog_times_templates($theme);

                        $related = collapse_1d_complexity('rel_b', $GLOBALS['SITE_DB']->query_select('theme_template_relations', array('rel_b'), array('rel_a' => $id), 'ORDER BY rel_b'));
                        array_unshift($related, $id);
                        foreach ($related as $rel) {
                            $template_file_path = find_template_path(basename($rel), dirname($rel), $theme);
                            if (empty($template_file_path)) {
                                continue;
                            }

                            $description_html = $this->get_template_details_table($theme, $rel, $template_file_path, $action_log_times);

                            list($img_url, $img_url_2, $template_file_shortened) = $this->get_template_file_icons($rel);

                            $out .= '
                            <entry
                                id="' . xmlentities($this->get_next_id()) . '"
                                serverid="' . xmlentities($rel) . '"
                                title="' . xmlentities($template_file_shortened) . '"
                                selectable="true"
                                description_html="' . xmlentities($description_html->evaluate()) . '"
                                img_url="' . xmlentities($img_url) . '"
                                img_url_2="' . xmlentities($img_url_2) . '"
                            ></entry>';
                        }
                    }

                    elseif (strpos(rtrim($id, ':'), ':') !== false) {
                        // Must be a screen show meta-tree...

                        $json_tree = $GLOBALS['SITE_DB']->query_select_value('theme_screen_tree', 'json_tree', array('page_link' => $id));
                        $tree = json_decode($json_tree, true);
                        $out .= $this->build_screen_tree($theme, $tree);

                    } else {
                        // Must be a zone, show pages in it...

                        $zone = rtrim($id, ':');

                        $action_log_times = $this->load_actionlog_times_pages($zone);

                        $pages = find_all_pages_wrap($zone, false, false, FIND_ALL_PAGES__PERFORMANT, 'comcode');
                        ksort($pages);
                        foreach (array_keys($pages) as $page) {
                            if (is_integer($page)) {
                                $page = strval($page);
                            }

                            list(, , $path) = find_comcode_page(get_site_default_lang(), $page, $zone);

                            $description_html = $this->get_comcode_page_details_table($page, $zone, $path, $action_log_times);

                            list($img_url, $img_url_2) = $this->get_template_file_icons($page . '.txt');

                            $out .= '
                            <entry
                                id="' . xmlentities($this->get_next_id()) . '"
                                serverid="' . xmlentities($zone . ':' . $page) . '"
                                title="' . xmlentities($page) . '"
                                selectable="true"
                                description_html="' . xmlentities($description_html->evaluate()) . '"
                                img_url="' . xmlentities($img_url) . '"
                                img_url_2="' . xmlentities($img_url_2) . '"
                            ></entry>';
                        }
                    }
                    break;
            }
        }

        if ($default !== null && preg_match('#^\w*:\w+$#', $default)) {
            $out .= '<expand>screens</expand>';
            $out .= '<expand>' . $default . '</expand>';
        }

        return '<result>' . $out . '</result>';
    }

    /**
     * Find what addons templates are.
     *
     * @param ?ID_TEXT $filter_addon Just for this addon (null: all)
     * @return array Map of template file to addon
     */
    private function templates_for_addons($filter_addon = null)
    {
        static $templates_for_addons = array();
        if (isset($templates_for_addons[$filter_addon])) {
            return $templates_for_addons[$filter_addon];
        }

        $_templates_for_addons = array();
        $addons = find_all_hook_obs('systems', 'addon_registry', 'Hook_addon_registry_');
        foreach ($addons as $addon => $ob) {
            if ($filter_addon !== null && $filter_addon != $addon) {
                continue;
            }

            $_files = $ob->get_file_list();

            foreach (array('templates', 'css', 'javascript', 'xml', 'text') as $subdir) {
                $test_for = 'themes/default/' . $subdir . '/';
                $test_for_2 = 'themes/default/' . $subdir . '_custom/';
                foreach ($_files as $file_path) {
                    if (substr($file_path, 0, strlen($test_for)) == $test_for || substr($file_path, 0, strlen($test_for_2)) == $test_for_2) {
                        $file = basename($file_path);

                        if (($file != 'index.html') && ($file != '.htaccess')) {
                            $_templates_for_addons[$subdir . '/' . $file] = $addon;
                        }
                    }
                }
            }
        }

        ksort($_templates_for_addons);

        $templates_for_addons[$filter_addon] = $_templates_for_addons;

        return $_templates_for_addons;
    }

    /**
     * Find action-log details that show edit data for templates.
     *
     * @param  ID_TEXT $theme Theme being used
     * @param  ?ID_TEXT $filter The template file to get this for (null: all)
     * @return array Action-log details
     */
    private function load_actionlog_times_templates($theme, $filter = null)
    {
        $where = array('the_type' => 'EDIT_TEMPLATE', 'param_b' => $theme);
        if ($filter !== null) {
            $where['param_a'] = $filter;
        }
        $_action_log_times = $GLOBALS['SITE_DB']->query_select('actionlogs', array('MAX(date_and_time)', 'param_a', 'member_id'), $where, 'GROUP BY param_a');
        $action_log_times = list_to_map('param_a', $_action_log_times);
        return $action_log_times;
    }

    /**
     * Find action-log details that show edit data for pages.
     *
     * @param  ID_TEXT $zone Zone being used
     * @param  ?ID_TEXT $filter The page file to get this for (null: all)
     * @return array Action-log details
     */
    private function load_actionlog_times_pages($zone, $filter = null)
    {
        $where = array('the_type' => 'COMCODE_PAGE_EDIT', 'param_b' => $zone);
        if ($filter !== null) {
            $where['param_a'] = $filter;
        }
        $_action_log_times = $GLOBALS['SITE_DB']->query_select('actionlogs', array('MAX(date_and_time)', 'param_a', 'member_id'), $where, 'GROUP BY param_a');
        $action_log_times = list_to_map('param_a', $_action_log_times);
        return $action_log_times;
    }

    /**
     * Show details for a template file.
     *
     * @param  ID_TEXT $theme Theme being used
     * @param  ID_TEXT $template_file The template file to show for
     * @param  PATH $template_file_path Path to the template file
     * @param  ?array $action_log_times Combined map of action-log details (null: look-up)
     * @return Tempcode Details
     */
    private function get_template_details_table($theme, $template_file, $template_file_path, $action_log_times = null)
    {
        $templates_for_addons = $this->templates_for_addons();

        if ($action_log_times === null) {
            $action_log_times = $this->load_actionlog_times_templates($theme, $template_file);
        }

        return do_template('THEME_TEMPLATE_EDITOR_TEMPLATE_DETAIL', array(
            'FILE' => $template_file,
            'FULL_PATH' => $template_file_path,
            'LAST_EDITING_USERNAME' => isset($action_log_times[$template_file]) ? $GLOBALS['FORUM_DRIVER']->get_username($action_log_times[$template_file]['member_id']) : null,
            'LAST_EDITING_DATE' => (filectime($template_file_path) == filemtime($template_file_path)) ? null : get_timezoned_date_time(filemtime($template_file_path)),
            'FILE_SIZE' => clean_file_size(filesize($template_file_path)),
            'ADDON' => isset($templates_for_addons[$template_file]) ? $templates_for_addons[$template_file] : null,
        ));
    }

    /**
     * Show details for a Comcode page file.
     *
     * @param  ID_TEXT $page Page name
     * @param  ID_TEXT $zone Zone the page is in
     * @param  PATH $path Path to page
     * @param  ?array $action_log_times Combined map of action-log details (null: look-up)
     * @return Tempcode Details
     */
    private function get_comcode_page_details_table($page, $zone, $path, $action_log_times = null)
    {
        if ($action_log_times === null) {
            $action_log_times = $this->load_actionlog_times_pages($zone, $page);
        }

        return do_template('THEME_TEMPLATE_EDITOR_TEMPLATE_DETAIL', array(
            'FILE' => $zone . ':' . $page,
            'FULL_PATH' => $path,
            'LAST_EDITING_USERNAME' => isset($action_log_times[$page]) ? $GLOBALS['FORUM_DRIVER']->get_username($action_log_times[$page]['member_id']) : null,
            'LAST_EDITING_DATE' => (filectime($path) == filemtime($path)) ? null : get_timezoned_date_time(filemtime($path)),
            'FILE_SIZE' => clean_file_size(filesize($path)),
            'ADDON' => null,
        ));
    }

    /**
     * Build screen tree from a meta-tree node.
     *
     * @param  ID_TEXT $theme The theme we are working with
     * @param  array $node Node
     * @return string XML
     */
    private function build_screen_tree($theme, $node)
    {
        $children = '';
        $num_children = 0;
        foreach ($node['children'] as $_child) {
            $child = $this->build_screen_tree($theme, $_child);
            if ($child != '') {
                $children .= $child;
                $num_children++;
            }
        }
        $has_children = ($num_children > 0);

        if ($node['type'] == 'template') {
            $file = $node['subdir'] . '/' . $node['name'];

            $file_path = find_template_path($node['name'], $node['subdir'], $theme);
            if (empty($file_path)) {
                return '';
            }

            $description_html = $this->get_template_details_table($theme, $file, $file_path);
        } elseif ($node['type'] == 'comcode_page') {
            $file = $node['name'];
            $parts = explode(':', $file);
            $page = $parts[1];
            $zone = $parts[0];

            list(, , $file_path) = find_comcode_page(get_site_default_lang(), $page, $zone);
            if (empty($file_path)) {
                return '';
            }

            $description_html = $this->get_comcode_page_details_table($page, $zone, $file_path);
        } else {
            return $children;
        }

        if ($description_html === null) {
            return '';
        }

        $tag_type = $has_children ? 'category' : 'entry';

        list($img_url, $img_url_2, $template_file_shortened) = $this->get_template_file_icons($file);

        if ($img_url === null) {
            $image_xml = '';
        } else {
            $image_xml = '
                img_url="' . xmlentities($img_url) . '"
                img_url_2="' . xmlentities($img_url_2) . '"
            ';
        }

        return '
        <' . $tag_type . '
            id="' . xmlentities($this->get_next_id()) . '"
            serverid="' . xmlentities($file) . '"
            title="' . xmlentities($template_file_shortened) . '"
            selectable="true"
            description_html="' . xmlentities($description_html->evaluate()) . '"
            has_children="' . ($has_children ? 'true' : 'false') . '"
            expanded="' . ($has_children ? 'true' : 'false') . '"
            ' . $image_xml . '
        >' . $children . '</' . $tag_type . '>';
    }

    /**
     * Find icon and labelling details for a node.
     *
     * @param  ID_TEXT $file File
     * @return array A triple: icon, retina icon, label
     */
    private function get_template_file_icons($file)
    {
        $ext = get_file_extension(basename($file));

        switch ($ext) {
            case 'tpl':
            case 'css':
            case 'js':
            case 'xml':
                $img_url = find_theme_image('icons/16x16/filetypes/' . $ext);
                $img_url_2 = find_theme_image('icons/32x32/filetypes/' . $ext);
                break;

            case 'txt':
                $img_url = find_theme_image('icons/16x16/filetypes/page_' . $ext);
                $img_url_2 = find_theme_image('icons/32x32/filetypes/page_' . $ext);
                break;

            default:
                $img_url = null;
                $img_url_2 = null;
                break;
        }

        $template_file_shortened = basename($file, '.' . $ext);

        return array($img_url, $img_url_2, $template_file_shortened);
    }

    /**
     * Get next unique ID.
     *
     * @return string ID
     */
    private function get_next_id()
    {
        static $counter = 0;
        $counter++;
        return 'screen_node_' . strval($counter) . '_' . md5(serialize($_GET));
    }
}

