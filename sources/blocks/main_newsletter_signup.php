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
 * @package    newsletter
 */

/**
 * Block class.
 */
class Block_main_newsletter_signup
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
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
        $info['parameters'] = array('subject', 'path', 'to', 'param');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = '(count($_POST)==0)?$map:null';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS;
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 24;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        require_lang('newsletter');
        require_css('newsletter');

        $newsletter_id = array_key_exists('param', $map) ? intval($map['param']) : db_get_first_id();

        $_newsletter_title = $GLOBALS['SITE_DB']->query_select_value_if_there('newsletters', 'title', array('id' => $newsletter_id));
        if (is_null($_newsletter_title)) {
            return paragraph(do_lang_tempcode('MISSING_RESOURCE'), '', 'red_alert');
        }
        $newsletter_title = get_translated_text($_newsletter_title);

        $address = post_param_string('address' . strval($newsletter_id), '');
        if ($address != '') {
            require_code('newsletter');

            require_code('type_sanitisation');
            if (!is_email_address($address)) {
                $msg = do_template('INLINE_WIP_MESSAGE', array('_GUID' => '9ce849d0d2dc879acba609b907317c74', 'MESSAGE' => do_lang_tempcode('INVALID_EMAIL_ADDRESS')));
                return do_template('BLOCK_MAIN_NEWSLETTER_SIGNUP', array('_GUID' => '3759e07077d74e6537cab04c897e76d2', 'BLOCK_PARAMS' => block_params_arr_to_str($map), 'URL' => get_self_url(), 'MSG' => $msg));
            }

            if (!array_key_exists('path', $map)) {
                $map['path'] = 'uploads/website_specific/signup.txt';
            }
            $path_exists = file_exists(get_custom_file_base() . '/' . $map['path']);

            require_code('character_sets');
            $forename = post_param_string('firstname' . strval($newsletter_id), '');
            $surname = post_param_string('lastname' . strval($newsletter_id), '');

            $password = basic_newsletter_join($address, 4, null, !$path_exists/*Send confirm if we're not sending an intro email through this block*/, $newsletter_id, $forename, $surname);
            if ($password == '') {
                return do_template('INLINE_WIP_MESSAGE', array('_GUID' => 'bbbf2b31e71cbdbc2bcf2bdb7605142c', 'MESSAGE' => do_lang_tempcode('NEWSLETTER_THIS_ALSO')));
            }
            if ($password == do_lang('NA')) {
                $manage_url = build_url(array('page' => 'newsletter', 'email' => $address), get_module_zone('newsletter'));
                return do_template('INLINE_WIP_MESSAGE', array('_GUID' => '0ece8967a12afe4248cf5976e1dc903e', 'MESSAGE' => do_lang_tempcode('ALREADY_EMAIL_ADDRESS', escape_html($manage_url->evaluate()))));
            }

            require_code('mail');
            if ($path_exists) {
                $url = (url_is_local($map['path']) ? (get_custom_base_url() . '/') : '') . $map['path'];
                $subject = array_key_exists('subject', $map) ? $map['subject'] : do_lang('_WELCOME');
                $body = convert_to_internal_encoding(http_download_file($url));
                $body = str_replace('{password}', $password, $body);
                $body = str_replace('{email}', $address, $body);
                $body = str_replace('{forename}', $forename, $body);
                $body = str_replace('{surname}', $surname, $body);
                mail_wrap($subject, $body, array($address), empty($map['to']) ? null : $map['to'], '', '', 3, null, false, null, true);
            }

            return do_template('BLOCK_MAIN_NEWSLETTER_SIGNUP_DONE', array('_GUID' => '9953c83685df4970de8f23fcd5dd15bb', 'BLOCK_PARAMS' => block_params_arr_to_str($map), 'NEWSLETTER_TITLE' => $newsletter_title, 'NID' => strval($newsletter_id), 'PASSWORD' => $password, 'PATH_EXISTS' => $path_exists));
        } else {
            return do_template('BLOCK_MAIN_NEWSLETTER_SIGNUP', array('_GUID' => 'c0e6f9cdab3d624bf3d27b745e3de38f', 'BLOCK_PARAMS' => block_params_arr_to_str($map), 'NEWSLETTER_TITLE' => $newsletter_title, 'NID' => strval($newsletter_id), 'URL' => get_self_url()));
        }
    }
}
