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
 * @package    catalogues
 */

/**
 * Block class.
 */
class Block_main_contact_catalogues
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('to', 'param', 'subject', 'body_prefix', 'body_suffix', 'subject_prefix', 'subject_suffix', 'redirect', 'guid');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled)
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = '(post_param_string(\'subject\',\'\')!=\'\')?null:array(array_key_exists(\'param\',$map)?$map[\'param\']:\'\',array_key_exists(\'to\',$map)?$map[\'to\']:\'\',array_key_exists(\'guid\',$map)?$map[\'guid\']:\'\',array_key_exists(\'redirect\',$map)?$map[\'redirect\']:\'\',array_key_exists(\'subject\',$map)?$map[\'subject\']:\'\',array_key_exists(\'body_prefix\',$map)?$map[\'body_prefix\']:\'\',array_key_exists(\'body_suffix\',$map)?$map[\'body_suffix\']:\'\',array_key_exists(\'subject_prefix\',$map)?$map[\'subject_prefix\']:\'\',array_key_exists(\'subject_suffix\',$map)?$map[\'subject_suffix\']:\'\')';
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 24 * 7;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters
     * @return Tempcode The result of execution
     */
    public function run($map)
    {
        require_code('fields');

        require_code('mail');
        require_code('mail_forms');

        $text = new Tempcode();

        // Options...

        if (addon_installed('captcha')) {
            require_code('captcha');
            $use_captcha = ((get_option('captcha_on_feedback') == '1') && (use_captcha()));
        } else {
            $use_captcha = false;
        }

        $catalogue_name = array_key_exists('param', $map) ? $map['param'] : '';
        if ($catalogue_name == '') {
            $catalogue_name = $GLOBALS['SITE_DB']->query_select_value('catalogues', 'c_name'); // Random/arbitrary (first one that comes out of the DB)
        }

        $special_fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), array('c_name' => $catalogue_name), 'ORDER BY cf_order,' . $GLOBALS['SITE_DB']->translate_field_ref('cf_name'));

        $subject = array_key_exists('subject', $map) ? $map['subject'] : '';
        if ($subject == '') {
            $subject = get_translated_text($GLOBALS['SITE_DB']->query_select_value('catalogues', 'c_title'));
        }

        $to_email = array_key_exists('to', $map) ? $map['to'] : '';
        if ($to_email == '') {
            $to_email = null;
        }

        $body_prefix = array_key_exists('body_prefix', $map) ? $map['body_prefix'] : '';
        $body_suffix = array_key_exists('body_suffix', $map) ? $map['body_suffix'] : '';
        $subject_prefix = array_key_exists('subject_prefix', $map) ? $map['subject_prefix'] : '';
        $subject_suffix = array_key_exists('subject_suffix', $map) ? $map['subject_suffix'] : '';

        $block_id = md5(serialize($map));

        // Submission...

        if (post_param_string('_block_id', '') == $block_id) {
            // Check CAPTCHA
            if ($use_captcha) {
                enforce_captcha();
            }

            // Data
            $field_results = array();
            foreach ($special_fields as $field_num => $field) {
                $ob = get_fields_hook($field['cf_type']);
                $inputted_value = $ob->inputted_to_field_value(false, $field, null);
                if ($inputted_value !== null) {
                    $field_results[get_translated_text($field['cf_name'])] = $inputted_value;
                }
            }

            require_code('antispam');
            inject_action_spamcheck(null, post_param_string('email', null));

            // Send e-mail
            form_to_email($subject, $subject_prefix, $subject_suffix, $body_prefix, $body_suffix, $field_results, $to_email, false);
  
            // Redirect/messaging
            $redirect = array_key_exists('redirect', $map) ? $map['redirect'] : '';
            if ($redirect != '') {
                $redirect = page_link_to_url($redirect);
                require_code('site2');
                assign_refresh($redirect, 0.0);
            } else {
                attach_message(do_lang_tempcode('MESSAGE_SENT'), 'inform');
            }
        }

        // Form...

        require_code('form_templates');

        $fields = new Tempcode();

        $hidden = new Tempcode();

        if ($use_captcha) {
            $fields->attach(form_input_captcha($hidden));
            $text->attach(do_lang_tempcode('FORM_TIME_SECURITY'));
        }

        $field_groups = array();

        url_default_parameters__enable();

        foreach ($special_fields as $field_num => $field) {
            $ob = get_fields_hook($field['cf_type']);

            $_cf_name = get_translated_text($field['cf_name']);
            $field_cat = '';
            $matches = array();
            if (strpos($_cf_name, ': ') !== false) {
                $field_cat = substr($_cf_name, 0, strpos($_cf_name, ': '));
                if ($field_cat . ': ' == $_cf_name) {
                    $_cf_name = $field_cat; // Just been pulled out as heading, nothing after ": "
                } else {
                    $_cf_name = substr($_cf_name, strpos($_cf_name, ': ') + 2);
                }
            }
            if (!array_key_exists($field_cat, $field_groups)) {
                $field_groups[$field_cat] = new Tempcode();
            }

            $_cf_description = escape_html(get_translated_text($field['cf_description']));

            $GLOBALS['NO_DEV_MODE_FULLSTOP_CHECK'] = true;
            $result = $ob->get_field_inputter($_cf_name, $_cf_description, $field, null, true, !array_key_exists($field_num + 1, $special_fields));
            $GLOBALS['NO_DEV_MODE_FULLSTOP_CHECK'] = false;

            if ($result === null) {
                continue;
            }

            if (is_array($result)) {
                $field_groups[$field_cat]->attach($result[0]);
            } else {
                $field_groups[$field_cat]->attach($result);
            }

            if (option_value_from_field_array($field, 'tag') == 'email') {
                $hidden->attach(form_input_hidden('field_tagged__field_' . strval($field['id']), 'email'));
            }
            if (option_value_from_field_array($field, 'tag') == 'name') {
                $hidden->attach(form_input_hidden('field_tagged__field_' . strval($field['id']), 'name'));
            }
            if (option_value_from_field_array($field, 'tag') == 'subject') {
                $hidden->attach(form_input_hidden('field_tagged__field_' . strval($field['id']), 'subject'));
            }

            unset($result);
            unset($ob);
        }

        if (array_key_exists('', $field_groups)) { // Blank prefix must go first
            $field_groups_blank = $field_groups[''];
            unset($field_groups['']);
            $field_groups = array_merge(array($field_groups_blank), $field_groups);
        }
        foreach ($field_groups as $field_group_title => $extra_fields) {
            if (is_integer($field_group_title)) {
                $field_group_title = ($field_group_title == 0) ? '' : strval($field_group_title);
            }

            if ($field_group_title != '') {
                $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'c0b9f22ef5767da57a1ff65c06af96a1', 'TITLE' => $field_group_title)));
            }
            $fields->attach($extra_fields);
        }

        url_default_parameters__disable();

        $hidden->attach(form_input_hidden('subject', $subject));
        $hidden->attach(form_input_hidden('_block_id', $block_id));

        $url = get_self_url();

        $guid = isset($map['guid']) ? $map['guid'] : '7dc3957edf3b47399b688d72fae54128';

        return do_template('FORM', array(
            '_GUID' => $guid,
            'FIELDS' => $fields,
            'HIDDEN' => $hidden,
            'SUBMIT_ICON' => 'buttons__send',
            'SUBMIT_NAME' => do_lang_tempcode('SEND'),
            'URL' => $url,
            'TEXT' => $text,
            'SECONDARY_FORM' => true,
            'ANALYTIC_EVENT_CATEGORY' => $subject,
        ));
    }
}
