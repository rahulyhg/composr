<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_cleanup_tools
 */

/**
 * Module page class.
 */
class Module_admin_broken_urls
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user)
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name)
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled)
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        return array(
            'browse' => array('BROKEN_URLS', 'menu/adminzone/tools/cleanup'), // TODO new icon needed #2966
        );
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none)
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('cleanup');

        set_helper_panel_tutorial('tut_website_health');
        set_helper_panel_text(comcode_lang_string('DOC_BROKEN_URLS'));

        $this->title = get_screen_title('BROKEN_URLS');

        if ($type != 'browse') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('BROKEN_URLS'))));
        }

        if ($type == 'choose') {
            breadcrumb_set_self(do_lang_tempcode('CHOOSE'));
        }

        if ($type == 'check') {
            breadcrumb_set_self(do_lang_tempcode('DONE'));
        }

        return null;
    }

    protected $link_types = array();

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution
     */
    public function run()
    {
        require_code('form_templates');
        require_code('broken_urls');
        require_code('oauth');

        require_lang('zones');

        $this->link_types = array(
            'comcode_pages' => do_lang('COMCODE_PAGES'),
            'comcode_fields' => do_lang('_COMCODE'),
            'url_fields' => do_lang('URL_FIELDS'),
            'catalogue_fields' => do_lang('CATALOGUE_FIELDS'),
        );

        if (!empty(get_option('moz_access_id'))) {
            $this->link_types = array_merge($this->link_types, array(
                'moz_backlinks' => do_lang('MOZ_BACKLINKS'),
            ));
        }

        if ((get_oauth_refresh_token('google_search_console') !== null) && (get_option('google_apis_api_key') != '')) {
            $this->link_types = array_merge($this->link_types, array(
                'google_broken_backlinks__auth_permissions' => do_lang('GOOGLE_BROKEN_BACKLINKS__auth_permissions'),
                'google_broken_backlinks__not_found' => do_lang('GOOGLE_BROKEN_BACKLINKS__not_found'),
                'google_broken_backlinks__server_error' => do_lang('GOOGLE_BROKEN_BACKLINKS__server_error'),
                'google_broken_backlinks__soft404' => do_lang('GOOGLE_BROKEN_BACKLINKS__soft404'),
            ));
        }

        // Decide what we're doing
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->browse();
        }
        if ($type == 'choose') {
            return $this->choose();
        }
        if ($type == 'check') {
            return $this->check();
        }

        return new Tempcode();
    }

    /**
     * Choose what kind of URLs to find.
     *
     * @return Tempcode The result of execution
     */
    public function browse()
    {
        $fields = new Tempcode();

        $list = new Tempcode();
        foreach ($this->link_types as $type => $type_title) {
            $list->attach(form_input_list_entry($type, true, $type_title));
        }
        $fields->attach(form_input_multi_list(do_lang_tempcode('TYPE'), do_lang_tempcode('DESCRIPTION_LINK_TYPES'), 'chosen_link_types', $list));

        $_live_base_urls = get_value('live_base_urls', get_base_url(), true);
        $fields->attach(form_input_line_multi(do_lang_tempcode('LIVE_BASE_URLS'), do_lang_tempcode('DESCRIPTION_LIVE_BASE_URLS'), 'live_base_urls[]', explode('|', $_live_base_urls), 1));

        $fields->attach(form_input_integer(do_lang_tempcode('MAXIMUM_API_RESULTS'), do_lang_tempcode('DESCRIPTION_MAXIMUM_API_RESULTS'), 'maximum_api_results', 50, true));

        $submit_name = do_lang_tempcode('FIND_URLS');

        $url = build_url(array('page' => '_SELF', 'type' => 'choose'), '_SELF');

        return do_template('FORM_SCREEN', array(
            'TITLE' => $this->title,
            'SKIP_WEBSTANDARDS' => true,
            'HIDDEN' => '',
            'GET' => false,
            'URL' => $url,
            'FIELDS' => $fields,
            'TEXT' => '',
            'SUBMIT_ICON' => 'buttons__proceed',
            'SUBMIT_NAME' => $submit_name,
        ));
    }

    /**
     * Choose what URLs to scan.
     *
     * @return Tempcode The result of execution
     */
    public function choose()
    {
        disable_php_memory_limit();
        if (php_function_allowed('set_time_limit')) {
            @set_time_limit(0);
        }

        $url_scanner = new BrokenURLScanner();

        $chosen_link_types = isset($_POST['chosen_link_types']) ? $_POST['chosen_link_types'] : array();

        $live_base_urls = isset($_POST['live_base_urls']) ? $_POST['live_base_urls'] : array();
        $_live_base_urls = '';
        foreach ($live_base_urls as $live_base_url) {
            if ($live_base_url != '') {
                if ($_live_base_urls != '') {
                    $_live_base_urls .= '|';
                }
                $_live_base_urls .= $live_base_url;
            }
        }
        set_value('live_base_urls', $_live_base_urls, true);

        $maximum_api_results = post_param_integer('maximum_api_results');

        $urls = array();
        foreach ($this->link_types as $type => $type_title) {
            if (!in_array($type, $chosen_link_types)) {
                continue;
            }

            $_urls = call_user_func(array($url_scanner, 'enumerate_' . $type), $live_base_urls, $maximum_api_results);
            foreach ($_urls as $url_bits) {
                $url = $url_bits['url'];

                if ($url == '') {
                    continue;
                }
                if (substr($url, 0, 1) == '#') {
                    continue;
                }
                if (substr($url, 0, 7) == 'mailto:') {
                    continue;
                }
                if (strpos($url, 'admin-broken-urls') !== false) {
                    continue;
                }

                foreach ($live_base_urls as $live_base_url) {
                    if ($live_base_url != '') {
                        if (substr($url, 0, strlen($live_base_url)) == $live_base_url) {
                            $url = get_base_url() . substr($url, strlen($live_base_url));
                            break;
                        }
                    }
                }

                $full_url = qualify_url($url, get_base_url());

                if (!isset($urls[$url])) {
                    $urls[$url] = array(
                        'FULL_URL' => $full_url,
                        'TABLE_NAMES' => array(),
                        'FIELD_NAMES' => array(),
                        'IDENTIFIERS' => array(),
                        'CONTENT_TYPES' => array(),
                        'STATUS' => null,
                    );
                }

                $urls[$url]['TABLE_NAMES'][] = $url_bits['table_name'];
                $urls[$url]['FIELD_NAMES'][] = $url_bits['field_name'];
                $urls[$url]['IDENTIFIERS'][] = array('IDENTIFIER' => $url_bits['identifier'], 'EDIT_URL' => $url_bits['edit_url']);
                $urls[$url]['CONTENT_TYPES'][] = $type_title;
            }
        }
        sort_maps_by($urls, 'FULL_URL');

        if (count($urls) == 0) {
            warn_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        $table = do_template('BROKEN_URLS', array('URLS' => $urls, 'DONE' => false));

        $hidden = new Tempcode();
        $hidden->attach(form_input_hidden('urls', serialize($urls)));

        $fields = new Tempcode();

        $fields->attach(form_input_tick(do_lang_tempcode('SHOW_URL_PASSES'), do_lang_tempcode('DESCRIPTION_SHOW_URL_PASSES'), 'show_passes', false));

        $submit_name = do_lang_tempcode('CHECK_URLS');

        $url = build_url(array('page' => '_SELF', 'type' => 'check'), '_SELF');

        $form = do_template('FORM', array(
            'SKIP_WEBSTANDARDS' => true,
            'SKIP_REQUIRED' => true,
            'HIDDEN' => $hidden,
            'GET' => false,
            'URL' => $url,
            'FIELDS' => $fields,
            'TEXT' => '',
            'SUBMIT_ICON' => 'buttons__proceed',
            'SUBMIT_NAME' => $submit_name,
        ));

        return do_template('RESULTS_TABLE_SCREEN', array(
            'TITLE' => $this->title,
            'TEXT' => do_lang_tempcode('PENDING_LINK_CHECK'),
            'RESULTS_TABLE' => $table,
            'FORM' => $form,
        ));
    }

    /**
     * Check URLs.
     *
     * @return Tempcode The result of execution
     */
    public function check()
    {
        $urls = unserialize(post_param_string('urls'));

        $show_passes = (post_param_integer('show_passes', 0) == 1);

        require_code('tasks');
        return call_user_func_array__long_task(do_lang('BROKEN_URLS'), get_screen_title('BROKEN_URLS'), 'find_broken_urls', array($urls, $show_passes));
    }
}
