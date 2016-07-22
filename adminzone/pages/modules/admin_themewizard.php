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
 * @package    themewizard
 */

/**
 * Module page class.
 */
class Module_admin_themewizard
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Allen Ellis';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        $ret = array();

        if (!$be_deferential && !$support_crosslinks) {
            $ret['browse'] = array('THEMEWIZARD', 'menu/adminzone/style/themes/themewizard');
        }

        $ret['make_logo'] = array('LOGOWIZARD', 'menu/adminzone/style/themes/logowizard');

        return $ret;
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('themes');

        if ($type == 'make_logo' || $type == '_make_logo' || $type == '__make_logo') {
            //set_helper_panel_text(comcode_lang_string('DOC_LOGOWIZARD'));
        } else {
            set_helper_panel_tutorial('tut_themes');

            set_helper_panel_text(comcode_lang_string('DOC_THEMEWIZARD'));
        }

        if ($type == 'browse') {
            breadcrumb_set_parents(array(array('_SELF:adminzone:browse', do_lang_tempcode('MANAGE_THEMES'))));

            breadcrumb_set_self(do_lang_tempcode('THEMEWIZARD'));

            $this->title = get_screen_title('_THEMEWIZARD', true, array(escape_html(integer_format(1)), escape_html(integer_format(4))));
        }

        if ($type == 'step2') {
            $this->title = get_screen_title('_THEMEWIZARD', true, array(escape_html(integer_format(2)), escape_html(integer_format(4))));
        }

        if ($type == 'step3') {
            $this->title = get_screen_title('_THEMEWIZARD', true, array(escape_html(integer_format(3)), escape_html(integer_format(4))));
        }

        if ($type == 'step4') {
            $this->title = get_screen_title('_THEMEWIZARD', true, array(escape_html(integer_format(4)), escape_html(integer_format(4))));
        }

        if ($type == 'step2' || $type == 'step3' || $type == 'step4') {
            breadcrumb_set_parents(array(array('_SEARCH:admin_themes', do_lang_tempcode('THEMES')), array('_SELF:_SELF:browse', do_lang_tempcode('THEMEWIZARD'))));
        }

        if ($type == 'make_logo') {
            breadcrumb_set_self(do_lang_tempcode('LOGOWIZARD'));

            $this->title = get_screen_title('_LOGOWIZARD', true, array(escape_html(integer_format(1)), escape_html(integer_format(3))));
        }

        if ($type == '_make_logo') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:make_logo', do_lang_tempcode('LOGOWIZARD'))));

            $this->title = get_screen_title('_LOGOWIZARD', true, array(escape_html(integer_format(2)), escape_html(integer_format(3))));
        }

        if ($type == '__make_logo') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:make_logo', do_lang_tempcode('START'))));

            $this->title = get_screen_title('_LOGOWIZARD', true, array(escape_html(integer_format(3)), escape_html(integer_format(3))));
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_code('themes2');
        require_code('themewizard');
        require_css('themes_editor');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->step1();
        }
        if ($type == 'step2') {
            return $this->step2();
        }
        if ($type == 'step3') {
            return $this->step3();
        }
        if ($type == 'step4') {
            return $this->step4();
        }
        if ($type == 'make_logo') {
            return $this->make_logo();
        }
        if ($type == '_make_logo') {
            return $this->_make_logo();
        }
        if ($type == '__make_logo') {
            return $this->__make_logo();
        }

        return new Tempcode();
    }

    /**
     * UI for a theme wizard step (choose colour).
     *
     * @return Tempcode The UI
     */
    public function step1()
    {
        if (!function_exists('imagepng')) {
            warn_exit(do_lang_tempcode('GD_NEEDED'));
        }

        $post_url = build_url(array('page' => '_SELF', 'type' => 'step2'), '_SELF', array('keep_theme_seed', 'keep_theme_dark', 'keep_theme_source', 'keep_theme_algorithm'), false, true);
        $text = do_lang_tempcode('THEMEWIZARD_1_DESCRIBE');
        $submit_name = do_lang_tempcode('PROCEED');

        require_code('form_templates');

        $source_theme = get_param_string('source_theme', 'default');

        $hidden = new Tempcode();
        if (count(find_all_themes()) == 1) {
            $hidden->attach(form_input_hidden('source_theme', $source_theme));
        } else {
            $themes = create_selection_list_themes($source_theme, true);
        }

        $fields = new Tempcode();

        $fields->attach(form_input_codename(do_lang_tempcode('NEW_THEME'), do_lang_tempcode('DESCRIPTION_NAME'), 'themename', get_param_string('themename', ''), true));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '0373ce292326fa209a6a44d829f547d4', 'SECTION_HIDDEN' => false, 'TITLE' => do_lang_tempcode('PARAMETERS'))));

        $fields->attach(form_input_colour(do_lang_tempcode('SEED_COLOUR'), do_lang_tempcode('DESCRIPTION_SEED_COLOUR'), 'seed', '#' . preg_replace('/^\#/', '', get_param_string('seed', find_theme_seed('default'))), true));

        if (count(find_all_themes()) != 1) {
            $fields->attach(form_input_list(do_lang_tempcode('SOURCE_THEME'), do_lang_tempcode('DESCRIPTION_SOURCE_THEME'), 'source_theme', $themes, null, true));
        }

        $radios = new Tempcode();
        $radios->attach(form_input_radio_entry('algorithm', 'equations', $source_theme == 'default', do_lang_tempcode('THEMEGEN_ALGORITHM_EQUATIONS')));
        $radios->attach(form_input_radio_entry('algorithm', 'hsv', $source_theme != 'default', do_lang_tempcode('THEMEGEN_ALGORITHM_HSV')));
        $fields->attach(form_input_radio(do_lang_tempcode('THEMEGEN_ALGORITHM'), do_lang_tempcode('DESCRIPTION_THEMEGEN_ALGORITHM'), 'algorithm', $radios, true));

        $fields->attach(form_input_tick(do_lang_tempcode('DARK_THEME'), do_lang_tempcode('DESCRIPTION_DARK_THEME'), 'dark', get_param_integer('dark', 0) == 1));

        $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'e809c785aff72bbfeec3829a0b2f464d', 'SECTION_HIDDEN' => true, 'TITLE' => do_lang_tempcode('ADVANCED'))));
        $fields->attach(form_input_tick(do_lang_tempcode('INHERIT_CSS'), do_lang_tempcode('DESCRIPTION_INHERIT_CSS'), 'inherit_css', get_param_integer('inherit_css', 0) == 1));

        require_javascript('ajax');
        $script = find_script('snippet');
        $javascript = "
            var form=document.getElementById('main_form');
            form.elements['source_theme'].onchange=function() {
                var default_theme=(form.elements['source_theme'].options[form.elements['source_theme'].selectedIndex].value=='default');
                form.elements['algorithm'][0].checked=default_theme;
                form.elements['algorithm'][1].checked=!default_theme;
            }
            form.old_submit=form.onsubmit;
            form.onsubmit=function() {
                document.getElementById('submit_button').disabled=true;
                var url='" . addslashes($script) . "?snippet=exists_theme&name='+window.encodeURIComponent(form.elements['themename'].value);
                if (!do_ajax_field_test(url))
                {
                    document.getElementById('submit_button').disabled=false;
                    return false;
                }
                document.getElementById('submit_button').disabled=false;
                if (typeof form.old_submit!='undefined' && form.old_submit) return form.old_submit();
                return true;
            };
        ";

        return do_template('FORM_SCREEN', array('_GUID' => '98963f4d7ff60744382f937e6cc5acbf', 'GET' => true, 'SKIP_WEBSTANDARDS' => true, 'TITLE' => $this->title, 'JAVASCRIPT' => $javascript, 'FIELDS' => $fields, 'URL' => $post_url, 'TEXT' => $text, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name, 'HIDDEN' => $hidden));
    }

    /**
     * UI for a theme wizard step (choose preview).
     *
     * @return Tempcode The UI
     */
    public function step2()
    {
        $source_theme = get_param_string('source_theme');
        $algorithm = get_param_string('algorithm');
        $seed = preg_replace('/^\#/', '', get_param_string('seed'));
        $dark = get_param_integer('dark', 0);
        $inherit_css = get_param_integer('inherit_css', 0);
        $themename = get_param_string('themename');
        require_code('type_sanitisation');
        if ((!is_alphanumeric($themename, true)) || (strlen($themename) > 40)) {
            warn_exit(do_lang_tempcode('BAD_CODENAME'));
        }
        if ((file_exists(get_custom_file_base() . '/themes/' . $themename)) || ($themename == 'default')) {
            warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($themename)));
        }

        // Check length (6 chars)
        if (strlen($seed) != 6) {
            warn_exit(do_lang_tempcode('INVALID_COLOUR'));
        }

        list($_theme,) = calculate_theme($seed, $source_theme, $algorithm, 'colours', $dark == 1);
        $theme = array();
        $theme['SOURCE_THEME'] = $source_theme;
        $theme['ALGORITHM'] = $algorithm;
        $theme['RED'] = $_theme['red'];
        $theme['GREEN'] = $_theme['green'];
        $theme['BLUE'] = $_theme['blue'];
        $theme['DOMINANT'] = $_theme['dominant'];
        $theme['LD'] = $_theme['LD'];
        $theme['DARK'] = $_theme['dark'];
        $theme['SEED'] = $_theme['seed'];
        $theme['TITLE'] = $this->title;
        $theme['CHANGE_URL'] = build_url(array('page' => '_SELF', 'type' => 'browse', 'source_theme' => $source_theme, 'algorithm' => $algorithm, 'seed' => $seed, 'dark' => $dark, 'inherit_css' => $inherit_css, 'themename' => $themename), '_SELF');
        $theme['STAGE3_URL'] = build_url(array('page' => '_SELF', 'type' => 'step3', 'source_theme' => $source_theme, 'algorithm' => $algorithm, 'seed' => $seed, 'dark' => $dark, 'inherit_css' => $inherit_css, 'themename' => $themename), '_SELF');

        return do_template('THEMEWIZARD_2_SCREEN', $theme);
    }

    /**
     * UI for a theme wizard step (choose save).
     *
     * @return Tempcode The UI
     */
    public function step3()
    {
        $source_theme = get_param_string('source_theme');
        $algorithm = get_param_string('algorithm');
        $seed = get_param_string('seed');
        $dark = get_param_integer('dark');
        $inherit_css = get_param_integer('inherit_css');
        $themename = get_param_string('themename');

        $post_url = build_url(array('page' => '_SELF', 'type' => 'step4'), '_SELF');
        $submit_name = do_lang_tempcode('ADD_THEME');
        require_code('form_templates');
        $fields = new Tempcode();
        $fields->attach(form_input_tick(do_lang_tempcode('USE_ON_ZONES'), do_lang_tempcode('DESCRIPTION_USE_ON_ZONES'), 'use_on_all', true));
        $hidden = new Tempcode();
        $hidden->attach(form_input_hidden('source_theme', $source_theme));
        $hidden->attach(form_input_hidden('algorithm', $algorithm));
        $hidden->attach(form_input_hidden('seed', $seed));
        $hidden->attach(form_input_hidden('themename', $themename));
        $hidden->attach(form_input_hidden('dark', strval($dark)));
        $hidden->attach(form_input_hidden('inherit_css', strval($inherit_css)));

        return do_template('FORM_SCREEN', array('_GUID' => '349383d77ecfce8c65f3303cfec86ea0', 'SKIP_WEBSTANDARDS' => true, 'TITLE' => $this->title, 'TEXT' => do_lang_tempcode('REFRESH_TO_FINISH'), 'FIELDS' => $fields, 'URL' => $post_url, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name, 'HIDDEN' => $hidden));
    }

    /**
     * UI for a theme wizard step (actualisation).
     *
     * @return Tempcode The UI
     */
    public function step4()
    {
        // Add theme
        $source_theme = post_param_string('source_theme');
        $algorithm = post_param_string('algorithm');
        $seed = post_param_string('seed');
        $themename = post_param_string('themename');
        $use = (post_param_integer('use_on_all', 0) == 1);
        $dark = post_param_integer('dark');
        $inherit_css = post_param_integer('inherit_css');

        send_http_output_ping();
        if (php_function_allowed('set_time_limit')) {
            set_time_limit(0);
        }

        require_code('type_sanitisation');
        if ((!is_alphanumeric($themename, true)) || (strlen($themename) > 40)) {
            warn_exit(do_lang_tempcode('BAD_CODENAME'));
        }
        make_theme($themename, $source_theme, $algorithm, $seed, $use, $dark == 1, $inherit_css == 1);
        $myfile = @fopen(get_custom_file_base() . '/themes/' . filter_naughty($themename) . '/theme.ini', GOOGLE_APPENGINE ? 'wb' : 'wt') or intelligent_write_error(get_custom_file_base() . '/themes/' . filter_naughty($themename) . '/theme.ini');
        fwrite($myfile, 'title=' . $themename . "\n");
        fwrite($myfile, 'description=' . do_lang('NA') . "\n");
        fwrite($myfile, 'seed=' . $seed . "\n");
        if (fwrite($myfile, 'author=' . $GLOBALS['FORUM_DRIVER']->get_username(get_member(), true) . "\n") == 0) {
            warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'), false, true);
        }
        fclose($myfile);
        sync_file('themes/' . filter_naughty($themename) . '/theme.ini');

        // We're done
        $message = do_lang_tempcode('THEMEWIZARD_4_DESCRIBE', escape_html('#' . $seed), escape_html($themename));

        require_code('templates_donext');
        return do_next_manager($this->title, $message,
            null,
            null,
            /* TYPED-ORDERED LIST OF 'LINKS'  */
            null, // Add one
            null, // Edit this
            null, // Edit one
            null, // View this
            null, // View archive
            null, // Add to category
            null, // Add one category
            null, // Edit one category
            null, // Edit this category
            null, // View this category
            /* SPECIALLY TYPED 'LINKS' */
            array(),
            array(),
            array(
                array('menu/_generic_admin/edit_this', array('admin_themes', array('type' => 'edit_theme', 'theme' => $themename), get_module_zone('admin_themes')), do_lang_tempcode('EDIT_THEME')),
                array('menu/adminzone/style/themes/templates', array('admin_themes', array('type' => 'edit_templates', 'theme' => $themename), get_module_zone('admin_themes')), do_lang('EDIT_TEMPLATES')),
                array('menu/adminzone/style/themes/theme_images', array('admin_themes', array('type' => 'manage_images', 'theme' => $themename), get_module_zone('admin_themes')), do_lang('EDIT_THEME_IMAGES')),
                array('menu/adminzone/style/themes/themes', array('admin_themes', array('type' => 'browse'), get_module_zone('admin_themes')), do_lang('MANAGE_THEMES'))
            ),
            do_lang('THEME')
        );
    }

    /**
     * UI for a logo wizard step (ask for input).
     *
     * @return Tempcode The UI
     */
    public function make_logo()
    {
        if (!function_exists('imagepng')) {
            warn_exit(do_lang_tempcode('GD_NEEDED'));
        }

        $post_url = build_url(array('page' => '_SELF', 'type' => '_make_logo'), '_SELF');

        $root_theme = $GLOBALS['FORUM_DRIVER']->get_theme('');
        $theme_image_url = build_url(array('page' => 'admin_themes', 'type' => 'edit_image', 'id' => 'logo/-logo', 'lang' => user_lang(), 'theme' => $root_theme), get_module_zone('admin_themes'));
        $standalone_theme_image_url = build_url(array('page' => 'admin_themes', 'type' => 'edit_image', 'id' => 'logo/standalone_logo', 'lang' => user_lang(), 'theme' => $root_theme), get_module_zone('admin_themes'));
        $text = do_lang_tempcode('LOGOWIZARD_1_DESCRIBE', escape_html($theme_image_url->evaluate()), escape_html($standalone_theme_image_url->evaluate()));

        $submit_name = do_lang_tempcode('PROCEED');

        $default_logos = get_all_image_ids_type('logo/default_logos');
        shuffle($default_logos);
        $default_backgrounds = get_all_image_ids_type('logo/default_backgrounds');
        shuffle($default_backgrounds);

        require_code('form_templates');

        $fields = new Tempcode();
        $fields->attach(form_input_line(do_lang_tempcode('config:SITE_NAME'), do_lang_tempcode('DESCRIPTION_LOGO_NAME'), 'name', get_option('site_name'), true));
        $fields->attach(form_input_theme_image(do_lang_tempcode('LOGO_THEME_IMAGE'), '', 'logo_theme_image', $default_logos, null));
        $fields->attach(form_input_theme_image(do_lang_tempcode('BACKGROUND_THEME_IMAGE'), '', 'background_theme_image', $default_backgrounds));
        $font_choices = new Tempcode();
        $dh = opendir(get_file_base() . '/data_custom/fonts');
        $fonts = array();
        if ($dh !== false) {
            while (($f = readdir($dh)) !== false) {
                if (substr($f, -4) == '.ttf') {
                    $fonts[] = $f;
                }
            }
            closedir($dh);
        }
        $dh = opendir(get_file_base() . '/data/fonts');
        if ($dh !== false) {
            while (($f = readdir($dh)) !== false) {
                if (substr($f, -4) == '.ttf') {
                    $fonts[] = $f;
                }
            }
            closedir($dh);
        }
        $fonts = array_unique($fonts);
        sort($fonts);
        require_css('fonts');
        foreach ($fonts as $font) {
            if (stripos($font, 'veranda') !== false) {
                continue; // Not licensed for this, only used as a web standards patch for vertical text
            }

            $_font = basename($font, '.ttf');
            $_font_label = $_font;
            for ($i = 0; $i < 2; $i++) {
                $_font_label = preg_replace('#(It|Oblique)($| )#' . ((strtolower($_font_label) == $_font_label) ? 'i' : ''), ' Italic ', $_font_label);
                $_font_label = preg_replace('#(Bd|Bold)($| )#' . ((strtolower($_font_label) == $_font_label) ? 'i' : ''), ' Bold ', $_font_label);
            }
            $_font_label = trim(str_replace('  ', ' ', $_font_label));
            $_font_label = preg_replace('#BI$#' . ((strtolower($_font_label) == $_font_label) ? 'i' : ''), ' Bold Italic', $_font_label);
            $font_choices->attach(form_input_radio_entry('font', $_font, $_font == 'Vera', '<span style="font-family: ' . escape_html($_font) . '">' . escape_html($_font_label) . '</span>'));
        }
        $fields->attach(form_input_radio(do_lang_tempcode('comcode:FONT'), '', 'font', $font_choices, true));

        // Find the most appropriate theme to edit for
        $theme = $GLOBALS['SITE_DB']->query_select_value_if_there('zones', 'zone_theme', array('zone_name' => 'site'));
        if ($theme === null) { // Just in case the 'site' zone no longer exists
            $theme = $GLOBALS['SITE_DB']->query_select_value('zones', 'zone_theme', array('zone_name' => ''));
        }
        if ($theme == '-1') {
            $theme = preg_replace('#[^A-Za-z\d]#', '_', get_site_name());
        }
        if (!file_exists(get_custom_file_base() . '/themes/' . $theme)) {
            $theme = 'default';
        }
        require_code('themes2');

        $fields->attach(form_input_list(do_lang_tempcode('THEME'), do_lang_tempcode('DESCRIPTION_LOGOWIZARD_THEME'), 'theme', create_selection_list_themes($theme, true)));

        return do_template('FORM_SCREEN', array('_GUID' => '08449c0ae8edf5c0b3510611c9ac9618', 'SKIP_WEBSTANDARDS' => true, 'TITLE' => $this->title, 'FIELDS' => $fields, 'URL' => $post_url, 'TEXT' => $text, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name, 'HIDDEN' => ''));
    }

    /**
     * UI for a logo wizard step (show preview).
     *
     * @return Tempcode The UI
     */
    public function _make_logo()
    {
        $preview = do_template('LOGOWIZARD_2', array('_GUID' => '6e5a442860e5b7644b50c2345c3c8dee', 'NAME' => post_param_string('name'), 'FONT' => post_param_string('font'), 'LOGO_THEME_IMAGE' => post_param_string('logo_theme_image'), 'BACKGROUND_THEME_IMAGE' => post_param_string('background_theme_image'), 'THEME' => post_param_string('theme')));

        require_code('templates_confirm_screen');
        return confirm_screen($this->title, $preview, '__make_logo', 'make_logo');
    }

    /**
     * UI for a logo wizard step (set).
     *
     * @return Tempcode The UI
     */
    public function __make_logo()
    {
        $theme = post_param_string('theme');
        $font = post_param_string('font');
        $logo_theme_image = post_param_string('logo_theme_image');
        $background_theme_image = post_param_string('background_theme_image');

        // Do it
        require_code('themes2');
        $rand = uniqid('', true);
        foreach (array($theme, 'default') as $logo_save_theme) {
            $path = 'themes/' . $logo_save_theme . '/images_custom/' . $rand . '.png';

            if (!file_exists(dirname($path))) {
                require_code('files2');
                make_missing_directory(dirname($path));
            }

            $img = generate_logo(post_param_string('name'), $font, $logo_theme_image, $background_theme_image, false, $logo_save_theme);
            @imagepng($img, get_custom_file_base() . '/' . $path, 9) or intelligent_write_error($path);
            imagedestroy($img);
            require_code('images_png');
            png_compress(get_custom_file_base() . '/' . $path);
            actual_edit_theme_image('logo/-logo', $logo_save_theme, user_lang(), 'logo/-logo', $path);
            $rand = uniqid('', true);
            $path = 'themes/' . $logo_save_theme . '/images_custom/' . $rand . '.png';
            $img = generate_logo(post_param_string('name'), $font, $logo_theme_image, $background_theme_image, false, null, true);
            @imagepng($img, get_custom_file_base() . '/' . $path, 9) or intelligent_write_error($path);
            imagedestroy($img);
            require_code('images_png');
            png_compress(get_custom_file_base() . '/' . $path);
            actual_edit_theme_image('logo/standalone_logo', $logo_save_theme, user_lang(), 'logo/standalone_logo', $path);
        }
        Self_learning_cache::erase_smart_cache();

        $message = do_lang_tempcode('LOGOWIZARD_3_DESCRIBE', escape_html($theme));
        return inform_screen($this->title, $message);
    }
}
