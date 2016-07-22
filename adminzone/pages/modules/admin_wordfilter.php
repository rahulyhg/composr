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
 * @package    wordfilter
 */

/**
 * Module page class.
 */
class Module_admin_wordfilter
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 4;
        $info['locked'] = true;
        $info['update_require_upgrade'] = true;
        return $info;
    }

    /**
     * Uninstall the module.
     */
    public function uninstall()
    {
        $GLOBALS['SITE_DB']->drop_table_if_exists('wordfilter');
    }

    /**
     * Install the module.
     *
     * @param  ?integer $upgrade_from What version we're upgrading from (null: new install)
     * @param  ?integer $upgrade_from_hack What hack version we're upgrading from (null: new-install/not-upgrading-from-a-hacked-version)
     */
    public function install($upgrade_from = null, $upgrade_from_hack = null)
    {
        if ($upgrade_from === null) {
            $GLOBALS['SITE_DB']->create_table('wordfilter', array(
                'id' => '*AUTO',
                'word' => 'SHORT_TEXT',
                'w_replacement' => 'SHORT_TEXT',
                'w_substr' => 'BINARY'
            ));

            $naughties = array(
                'arsehole', 'asshole', 'arse', 'bastard', 'cock', 'cocked', 'cocksucker', 'crap', 'cunt', 'cum',
                'blowjob', 'bollocks', 'bondage', 'bugger', 'buggery', 'dickhead', 'dildo', 'faggot', 'fuck', 'fucked', 'fucking',
                'fucker', 'gayboy', 'jackoff', 'jerk-off', 'motherfucker', 'nigger', 'piss', 'pissed', 'puffter', 'pussy',
                'queers', 'retard', 'shag', 'shagged',
                'shat', 'shit', 'slut', 'twat', 'wank', 'wanker', 'whore',
            );
            foreach ($naughties as $word) {
                $GLOBALS['SITE_DB']->query_insert('wordfilter', array('word' => $word, 'w_replacement' => '', 'w_substr' => 0));
            }
        }

        if (($upgrade_from !== null) && ($upgrade_from < 4)) {
            $GLOBALS['SITE_DB']->add_auto_key('wordfilter');
        }
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
        return array(
            'browse' => array('MANAGE_WORDFILTER', 'menu/adminzone/security/wordfilter'),
        );
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

        require_lang('wordfilter');

        set_helper_panel_tutorial('tut_censor');

        if ($type == 'browse') {
            $this->title = get_screen_title('MANAGE_WORDFILTER');
        }

        if ($type == 'add') {
            $this->title = get_screen_title('ADD_WORDFILTER');
        }

        if ($type == 'remove') {
            $this->title = get_screen_title('DELETE_WORDFILTER');
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
        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->wordfilter_interface();
        }
        if ($type == 'add') {
            return $this->add_word();
        }
        if ($type == 'remove') {
            return $this->remove_word();
        }

        return new Tempcode();
    }

    /**
     * The UI to choose a filtered-word to edit, or to add a filtered-word.
     *
     * @return Tempcode The UI
     */
    public function wordfilter_interface()
    {
        require_code('form_templates');
        $list = new Tempcode();
        $words = $GLOBALS['SITE_DB']->query_select('wordfilter', array('*'), null, 'ORDER BY word');
        foreach ($words as $word) {
            $word_text = (($word['w_substr'] == 1) ? '*' : '') . $word['word'] . (($word['w_substr'] == 1) ? '*' : '');
            if ($word['w_replacement'] != '') {
                $word_text .= ' -> ' . $word['w_replacement'];
            }
            $list->attach(form_input_list_entry($word['word'], false, $word_text));
        }
        if (!$list->is_empty()) {
            $delete_url = build_url(array('page' => '_SELF', 'type' => 'remove'), '_SELF');
            $submit_name = do_lang_tempcode('DELETE_WORDFILTER');
            $fields = form_input_list(do_lang_tempcode('WORD'), '', 'word', $list);

            $tpl = do_template('FORM', array('_GUID' => 'a752cea5acab633e1cc0781f0e77e0be', 'TABINDEX' => strval(get_form_field_tabindex()), 'HIDDEN' => '', 'TEXT' => '', 'FIELDS' => $fields, 'URL' => $delete_url, 'SUBMIT_ICON' => 'menu___generic_admin__delete', 'SUBMIT_NAME' => $submit_name));
        } else {
            $tpl = new Tempcode();
        }

        // Do a form so people can add
        $post_url = build_url(array('page' => '_SELF', 'type' => 'add'), '_SELF');
        $submit_name = do_lang_tempcode('ADD_WORDFILTER');
        $fields = new Tempcode();
        $fields->attach(form_input_line(do_lang_tempcode('WORD'), do_lang_tempcode('DESCRIPTION_WORD'), 'word_2', '', true));
        $fields->attach(form_input_line(do_lang_tempcode('REPLACEMENT'), do_lang_tempcode('DESCRIPTION_REPLACEMENT'), 'replacement', '', false));
        $fields->attach(form_input_tick(do_lang_tempcode('WORD_SUBSTR'), do_lang_tempcode('DESCRIPTION_WORD_SUBSTR'), 'substr', false));
        $add_form = do_template('FORM', array('_GUID' => '5b1d45b374e15392b9f5496de8db2e1c', 'TABINDEX' => strval(get_form_field_tabindex()), 'SECONDARY_FORM' => true, 'SKIP_REQUIRED' => true, 'HIDDEN' => '', 'TEXT' => '', 'FIELDS' => $fields, 'SUBMIT_ICON' => 'menu___generic_admin__add_one', 'SUBMIT_NAME' => $submit_name, 'URL' => $post_url));

        return do_template('WORDFILTER_SCREEN', array('_GUID' => '4b355f5d2cecc0bc26e76a69716cc841', 'TITLE' => $this->title, 'TPL' => $tpl, 'ADD_FORM' => $add_form));
    }

    /**
     * The actualiser to add a filtered-word.
     *
     * @return Tempcode The UI
     */
    public function add_word()
    {
        $word = post_param_string('word_2');
        $this->_add_word($word, post_param_string('replacement'), post_param_integer('substr', 0));

        // Show it worked / Refresh
        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Add a filtered-word.
     *
     * @param  SHORT_TEXT $word The filtered-word
     * @param  SHORT_TEXT $replacement Replacement (blank: block entirely)
     * @param  BINARY $substr Whether to perform a substring match
     */
    public function _add_word($word, $replacement, $substr)
    {
        $test = $GLOBALS['SITE_DB']->query_select_value_if_there('wordfilter', 'word', array('word' => $word));
        if ($test !== null) {
            warn_exit(do_lang_tempcode('ALREADY_EXISTS', escape_html($word)));
        }

        $GLOBALS['SITE_DB']->query_insert('wordfilter', array('word' => $word, 'w_replacement' => $replacement, 'w_substr' => $substr));

        log_it('ADD_WORDFILTER', $word);
    }

    /**
     * The actualiser to delete a filtered-word.
     *
     * @return Tempcode The UI
     */
    public function remove_word()
    {
        $this->_remove_word(post_param_string('word'));

        // Show it worked / Refresh
        $url = build_url(array('page' => '_SELF', 'type' => 'browse'), '_SELF');
        return redirect_screen($this->title, $url, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Delete a filtered-word.
     *
     * @param  SHORT_TEXT $word The filtered-word
     */
    public function _remove_word($word)
    {
        $GLOBALS['SITE_DB']->query_delete('wordfilter', array('word' => $word), '', 1);

        log_it('DELETE_WORDFILTER', $word);
    }
}
