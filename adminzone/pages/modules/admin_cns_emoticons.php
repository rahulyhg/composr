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
 * @package    core_cns
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_admin_cns_emoticons extends Standard_crud_module
{
    public $lang_type = 'EMOTICON';
    public $select_name = 'EMOTICON';
    public $orderer = 'e_code';
    public $array_key = 'e_code';
    public $title_is_multi_lang = false;
    public $non_integer_id = true;
    public $possibly_some_kind_of_upload = true;
    public $do_preview = null;
    public $menu_label = 'EMOTICONS';
    public $donext_entry_content_type = 'emoticon';
    public $donext_category_content_type = null;

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
        if (get_forum_type() != 'cns') {
            return null;
        }

        return array(
            'browse' => array('EMOTICONS', 'menu/adminzone/style/emoticons'),
        ) + parent::get_entry_points();
    }

    public $title;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @param  boolean $top_level Whether this is running at the top level, prior to having sub-objects called.
     * @param  ?ID_TEXT $type The screen type to consider for metadata purposes (null: read from environment).
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run($top_level = true, $type = null)
    {
        $type = get_param_string('type', 'browse');

        require_lang('cns');
        require_css('cns_admin');

        set_helper_panel_tutorial('tut_emoticons');

        if ($type == 'import') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('EMOTICONS'))));
        }

        if ($type == '_import') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:browse', do_lang_tempcode('EMOTICONS')), array('_SELF:_SELF:import', do_lang_tempcode('IMPORT_EMOTICONS'))));
        }

        if ($type == 'import' || $type == '_import') {
            $this->title = get_screen_title('IMPORT_EMOTICONS');
        }

        return parent::pre_run($top_level);
    }

    /**
     * Standard crud_module run_start.
     *
     * @param  ID_TEXT $type The type of module execution
     * @return Tempcode The output of the run
     */
    public function run_start($type)
    {
        $this->add_one_label = do_lang_tempcode('ADD_EMOTICON');
        $this->edit_this_label = do_lang_tempcode('EDIT_THIS_EMOTICON');
        $this->edit_one_label = do_lang_tempcode('EDIT_EMOTICON');

        require_lang('dearchive');
        require_code('images');

        if (get_forum_type() != 'cns') {
            warn_exit(do_lang_tempcode('NO_CNS'));
        } else {
            cns_require_all_forum_stuff();
        }

        require_code('cns_general_action');
        require_code('cns_general_action2');

        if ($type == 'add') {
            require_javascript('core_cns');
            $this->js_function_calls[] = 'moduleAdminCnsEmoticons';
        }

        if ($type == 'browse') {
            return $this->browse();
        }
        if ($type == 'import') {
            return $this->import();
        }
        if ($type == '_import') {
            return $this->_import();
        }
        return new Tempcode();
    }

    /**
     * The do-next manager for before content management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        require_code('templates_donext');
        return do_next_manager(
            get_screen_title('EMOTICONS'),
            comcode_lang_string('DOC_EMOTICONS'),
            array(
                array('menu/_generic_admin/import', array('_SELF', array('type' => 'import'), '_SELF'), do_lang('IMPORT_EMOTICONS')),
                array('menu/_generic_admin/add_one', array('_SELF', array('type' => 'add'), '_SELF'), do_lang('ADD_EMOTICON')),
                array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_EMOTICON')),
            ),
            do_lang('EMOTICONS')
        );
    }

    /**
     * The UI to import in bulk from an archive file.
     *
     * @return Tempcode The UI
     */
    public function import()
    {
        if (is_on_multi_site_network()) {
            attach_message(do_lang_tempcode('EDITING_ON_WRONG_MSN'), 'warn');
        }

        $post_url = build_url(array('page' => '_SELF', 'type' => '_import', 'uploading' => 1), '_SELF');
        $fields = new Tempcode();
        $fields->attach(form_input_upload_multi(do_lang_tempcode('UPLOAD'), do_lang_tempcode('DESCRIPTION_ARCHIVE_IMAGES', escape_html(str_replace(',', ', ', get_option('valid_images')))), 'file', true, null, null, true, str_replace(' ', '', get_option('valid_images'))));

        $text = paragraph(do_lang_tempcode('IMPORT_EMOTICONS_WARNING'));
        require_code('images');
        $max = floatval(get_max_image_size()) / floatval(1024 * 1024);
        /*if ($max < 1.0) { Ok - this is silly! Emoticons are tiny.
            require_code('files2');
            $config_url = get_upload_limit_config_url();
            $text->attach(paragraph(do_lang_tempcode(($config_url === null) ? 'MAXIMUM_UPLOAD' : 'MAXIMUM_UPLOAD_STAFF', escape_html(($max > 10.0) ? integer_format(intval($max)) : float_format($max)), escape_html(($config_url === null) ? '' : $config_url))));
        }*/

        $hidden = build_keep_post_fields();
        $hidden->attach(form_input_hidden('test', '1'));
        handle_max_file_size($hidden);

        return do_template('FORM_SCREEN', array('_GUID' => '1910e01ec183392f6b254671dc7050a3', 'TITLE' => $this->title, 'FIELDS' => $fields, 'SUBMIT_ICON' => 'menu___generic_admin__import', 'SUBMIT_NAME' => do_lang_tempcode('BATCH_IMPORT_ARCHIVE_CONTENTS'), 'URL' => $post_url, 'TEXT' => $text, 'HIDDEN' => $hidden));
    }

    /**
     * The actualiser to import in bulk from an archive file.
     *
     * @return Tempcode The UI
     */
    public function _import()
    {
        post_param_string('test'); // To pick up on max file size exceeded errors

        require_code('uploads');
        require_code('images');
        is_plupload(true);

        set_mass_import_mode();

        foreach ($_FILES as $attach_name => $__file) {
            $tmp_name = $__file['tmp_name'];
            $file = $__file['name'];

            if (is_image($file, IMAGE_CRITERIA_WEBSAFE, has_privilege(get_member(), 'comcode_dangerous'))) {
                $urls = get_url('', $attach_name, 'themes/default/images_custom');
                $path = $urls[0];
                $this->_import_emoticon($path);
            } else {
                attach_message(do_lang_tempcode('INVALID_FILE_TYPE_VERY_GENERAL', escape_html(get_file_extension($file))), 'warn');
            }
        }

        log_it('IMPORT_EMOTICONS');

        return $this->do_next_manager($this->title, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Import an emoticon.
     *
     * @param  PATH $path Path to the emoticon file, on disk (must be in theme images folder).
     */
    public function _import_emoticon($path)
    {
        $emoticon_code = basename($path, '.' . get_file_extension($path));

        if (file_exists(get_file_base() . '/themes/default/images/emoticons/index.html')) {
            $image_code = 'emoticons/' . $emoticon_code;
        } else {
            $image_code = 'cns_emoticons/' . $emoticon_code;
        }
        $url_path = 'themes/default/images_custom/' . rawurlencode(basename($path));

        $GLOBALS['SITE_DB']->query_delete('theme_images', array('id' => $image_code));
        $GLOBALS['SITE_DB']->query_insert('theme_images', array('id' => $image_code, 'theme' => 'default', 'path' => $url_path, 'lang' => get_site_default_lang()));
        $GLOBALS['FORUM_DB']->query_delete('f_emoticons', array('e_code' => ':' . $emoticon_code . ':'), '', 1);
        $GLOBALS['FORUM_DB']->query_insert('f_emoticons', array(
            'e_code' => ':' . $emoticon_code . ':',
            'e_theme_img_code' => $image_code,
            'e_relevance_level' => 2,
            'e_use_topics' => 0,
            'e_is_special' => 0
        ));

        Self_learning_cache::erase_smart_cache();
    }

    /**
     * Get Tempcode for a post template adding/editing form.
     *
     * @param  SHORT_TEXT $code The emoticon code
     * @param  SHORT_TEXT $theme_img_code The theme image code
     * @param  integer $relevance_level The relevance level of the emoticon
     * @range  0 4
     * @param  BINARY $use_topics Whether the emoticon is usable as a topic emoticon
     * @param  BINARY $is_special Whether this may only be used by privileged members
     * @return array A pair: The input fields, Hidden fields
     */
    public function get_form_fields($code = ':-]', $theme_img_code = '', $relevance_level = 1, $use_topics = 1, $is_special = 0)
    {
        if (is_on_multi_site_network()) {
            attach_message(do_lang_tempcode('EDITING_ON_WRONG_MSN'), 'warn');
        }

        $fields = new Tempcode();
        $hidden = new Tempcode();

        $fields->attach(form_input_line(do_lang_tempcode('CODE'), do_lang_tempcode('DESCRIPTION_EMOTICON_CODE'), 'code', $code, true));

        require_code('themes2');
        $ids = get_all_image_ids_type('cns_emoticons', false, $GLOBALS['FORUM_DB']);

        if (get_base_url() == get_forum_base_url()) {
            $set_name = 'image';
            $required = true;
            $set_title = do_lang_tempcode('IMAGE');
            $field_set = (count($ids) == 0) ? new Tempcode() : alternate_fields_set__start($set_name);

            require_code('images');
            $field_set->attach(form_input_upload(do_lang_tempcode('UPLOAD'), '', 'file', $required, null, null, true, get_allowed_image_file_types()));

            $image_chooser_field = form_input_theme_image(do_lang_tempcode('STOCK'), '', 'theme_img_code', $ids, null, $theme_img_code, null, false, $GLOBALS['FORUM_DB']);
            $field_set->attach($image_chooser_field);

            $fields->attach(alternate_fields_set__end($set_name, $set_title, '', $field_set, $required));

            handle_max_file_size($hidden, 'image');
        } else {
            if (count($ids) == 0) {
                warn_exit(do_lang_tempcode('NO_SELECTABLE_THEME_IMAGES_MSN', 'cns_emoticons'));
            }

            $image_chooser_field = form_input_theme_image(do_lang_tempcode('STOCK'), '', 'theme_img_code', $ids, null, $theme_img_code, null, true, $GLOBALS['FORUM_DB']);
            $fields->attach($image_chooser_field);
        }

        $list = new Tempcode();
        for ($i = 0; $i <= 4; $i++) {
            $list->attach(form_input_list_entry(strval($i), $i == $relevance_level, do_lang_tempcode('EMOTICON_RELEVANCE_LEVEL_' . strval($i))));
        }
        $fields->attach(form_input_list(do_lang_tempcode('RELEVANCE_LEVEL'), do_lang_tempcode('DESCRIPTION_RELEVANCE_LEVEL'), 'relevance_level', $list));

        $fields->attach(form_input_tick(do_lang_tempcode('USE_TOPICS'), do_lang_tempcode('DESCRIPTION_USE_TOPICS'), 'use_topics', $use_topics == 1));
        $fields->attach(form_input_tick(do_lang_tempcode('EMOTICON_IS_SPECIAL'), do_lang_tempcode('DESCRIPTION_EMOTICON_IS_SPECIAL'), 'is_special', $is_special == 1));

        return array($fields, $hidden);
    }

    /**
     * Standard crud_module list function.
     *
     * @return Tempcode The selection list
     */
    public function create_selection_list_radio_entries()
    {
        $_m = $GLOBALS['FORUM_DB']->query_select('f_emoticons', array('e_code', 'e_theme_img_code'));
        $entries = new Tempcode();
        $first = true;
        foreach ($_m as $m) {
            $url = find_theme_image($m['e_theme_img_code'], true);

            if ($url == '') { // Automatic cleanup of ones deleted from disk
                $GLOBALS['FORUM_DB']->query_delete('f_emoticons', array('e_code' => $m['e_code']), '', 1);
                continue;
            }

            $entries->attach(do_template('FORM_SCREEN_INPUT_THEME_IMAGE_ENTRY', array('_GUID' => 'f7f64637d1c4984881f7acc68c2fe6c7', 'PRETTY' => $m['e_code'], 'CHECKED' => $first, 'NAME' => 'id', 'CODE' => $m['e_code'], 'URL' => $url)));
            $first = false;
        }

        return $entries;
    }

    /**
     * Standard crud_module edit form filler.
     *
     * @param  ID_TEXT $id The entry being edited
     * @return array A pair: The input fields, Hidden fields
     */
    public function fill_in_edit_form($id)
    {
        $m = $GLOBALS['FORUM_DB']->query_select('f_emoticons', array('*'), array('e_code' => $id), '', 1);
        if (!array_key_exists(0, $m)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
        }
        $r = $m[0];

        $ret = $this->get_form_fields($r['e_code'], $r['e_theme_img_code'], $r['e_relevance_level'], $r['e_use_topics'], $r['e_is_special']);

        return $ret;
    }

    /**
     * Standard crud_module add actualiser.
     *
     * @return ID_TEXT The entry added
     */
    public function add_actualisation()
    {
        require_code('themes2');

        $theme_img_code = post_param_theme_img_code('cns_emoticons', true, 'file', 'theme_img_code', $GLOBALS['FORUM_DB']);

        cns_make_emoticon(post_param_string('code'), $theme_img_code, post_param_integer('relevance_level'), post_param_integer('use_topics', 0), post_param_integer('is_special', 0));
        return post_param_string('code');
    }

    /**
     * Standard crud_module edit actualiser.
     *
     * @param  ID_TEXT $id The entry being edited
     */
    public function edit_actualisation($id)
    {
        require_code('themes2');

        $theme_img_code = post_param_theme_img_code('cns_emoticons', true, 'file', 'theme_img_code', $GLOBALS['FORUM_DB']);

        cns_edit_emoticon($id, post_param_string('code'), $theme_img_code, post_param_integer('relevance_level'), post_param_integer('use_topics', 0), post_param_integer('is_special', 0));

        $this->new_id = post_param_string('code');
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @param  ID_TEXT $id The entry being deleted
     */
    public function delete_actualisation($id)
    {
        cns_delete_emoticon($id);
    }
}
