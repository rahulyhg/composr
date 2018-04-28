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
 * @package    cns_forum
 */

/**
 * Forum e-mail integration class.
 *
 * @package        cns_forum
 */
class ForumEmailIntegration extends EmailIntegration
{
    protected $forum_id = null, $forum_row = null;

    /**
     * Send out an e-mail message for a forum post.
     *
     * @param  AUTO_LINK $topic_id The ID of the topic that got posted in.
     * @param  AUTO_LINK $post_id The ID of the post.
     * @param  AUTO_LINK $forum_id The forum that the topic is in.
     * @param  mixed $post_url URL to the post (URLPATH or Tempcode)
     * @param  string $topic_title Topic title
     * @param  string $post Post text
     * @param  MEMBER $to_member_id Member ID of recipient
     * @param  string $to_displayname Display name of recipient
     * @param  EMAIL $to_email E-mail address of recipient
     * @param  string $from_displayname Display name of poster
     * @param  boolean $is_starter Whether this is a new topic, just created by the poster
     */
    public function outgoing_message($topic_id, $post_id, $forum_id, $post_url, $topic_title, $post, $to_member_id, $to_displayname, $to_email, $from_displayname, $is_starter = false)
    {
        $this->forum_id = $forum_id;
        $forum_rows = $GLOBALS['FORUM_DB']->query_select('f_forums', array('*'), array('id' => $forum_id), '', 1);
        if (!array_key_exists(0, $forum_rows)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'forum'));
        }
        $this->forum_row = $forum_rows[0];

        $extended_subject = do_lang('MAILING_LIST_SIMPLE_SUBJECT_' . ($is_starter ? 'new' : 'reply'), $topic_title, get_site_name(), array($from_displayname), get_lang($to_member_id));

        $extended_message = '';
        $extended_message .= do_lang('MAILING_LIST_SIMPLE_MAIL_' . ($is_starter ? 'new' : 'reply'), get_site_name(), $post_url, array($from_displayname), get_lang($to_member_id));
        $extended_message .= $post;

        $this->_outgoing_message($extended_subject, $extended_message, $to_member_id, $to_displayname, $to_email, $from_displayname);
    }

    /**
     * Find the e-mail address to send from (From header).
     *
     * @return EMAIL E-mail address
     */
    protected function get_sender_email()
    {
        foreach (array('website_email', null, 'staff_address') as $address) {
            if (($address === null) && ($this->forum_row['f_mail_email_address'] != '')) {
                return $this->forum_row['f_mail_email_address'];
            }

            if (get_option($address) != '') {
                return get_option($address);
            }
        }

        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        return '';
    }

    /**
     * Find the e-mail address for system e-mails (Reply-To header).
     *
     * @return EMAIL E-mail address
     */
    protected function get_system_email()
    {
        foreach (array(null, 'staff_address', 'website_email') as $address) {
            if (($address === null) && ($this->forum_row['f_mail_email_address'] != '')) {
                return $this->forum_row['f_mail_email_address'];
            }

            if (get_option($address) != '') {
                return get_option($address);
            }
        }

        warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
        return '';
    }

    /**
     * Scan for new e-mails in the support inbox.
     */
    public function incoming_scan()
    {
        require_code('cns_forums2');
        $test = cns_has_mailing_list_style();
        if ($test[0] == 0) {
            return; // Possibly due to not being fully configured yet
        }

        $sql = 'SELECT * FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_forums';
        $sql_sup = ' WHERE ' . db_string_not_equal_to('f_mail_username', '') . ' AND ' . db_string_not_equal_to('f_mail_email_address', '');
        $rows = $GLOBALS['FORUM_DB']->query($sql);
        foreach ($rows as $row) {
            $this->forum_id = $row['id'];
            $this->forum_row = $row;

            $type = $row['f_mail_server_type'];
            $host = $row['f_mail_server_host'];
            $port = ($row['f_mail_server_port'] == '') ? null : intval($row['f_mail_server_port']);
            $folder = $row['f_mail_folder'];
            $username = $row['f_mail_username'];
            $password = $row['f_mail_password'];

            $this->_incoming_scan($type, $host, $port, $folder, $username, $password);
        }
    }

    /**
     * Process an e-mail found.
     *
     * @param  EMAIL $from_email From e-mail
     * @param  EMAIL $email_bounce_to E-mail address of sender (usually the same as $email, but not if it was a forwarded e-mail)
     * @param  string $from_name From name
     * @param  string $subject E-mail subject
     * @param  string $body E-mail body
     * @param  array $attachments Map of attachments (name to file data); only populated if $mime_type is appropriate for an attachment
     */
    protected function _process_incoming_message($from_email, $email_bounce_to, $from_name, $subject, $body, $attachments)
    {
        // Try to bind to a from member
        $member_id = $this->find_member_id($from_email);
        if ($member_id === null) {
            $member_id = $this->handle_missing_member($from_email, $email_bounce_to, $this->forum_row['f_mail_nonmatch_policy'], $subject, $body);
        }
        if ($member_id === null) {
            return;
        }

        // Check access
        if (!has_category_access($this->member_id, 'forums', strval($this->forum_id))) {
            $forum_name = get_translated_text($this->forum_row['f_name']);
            $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
            $this->send_bounce_email__access_denied($subject, $body, $from_email, $email_bounce_to, $forum_name, $username);
        }

        // Check there can be no forgery vulnerability
        $member_id_comcode = $this->degrade_member_id_for_comcode($member_id);

        global $LAX_COMCODE, $OVERRIDE_MEMBER_ID_COMCODE;
        $OVERRIDE_MEMBER_ID_COMCODE = $member_id_comcode;
        $LAX_COMCODE = true;

        // Add in attachments
        $attachment_errors = $this->save_attachments($attachments, $member_id, $member_id_comcode, $body);

        // Mark that this was e-mailed in
        $body = static_evaluate_tempcode(do_template('CNS_POST_FROM_MAILING_LIST', array(
            'UNCONFIRMED_MEMBER_NOTICE' => ($this->forum_row['f_mail_unconfirmed_notice'] == 1) && (!is_guest($member_id)),
            'POST' => $body,
            'USERNAME' => $username,
        ), null, false, null, '.txt', 'text'));

        // Try and match to a topic
        $topic_id = null;
        if (substr($subject, 0, 4) == 'Re: ') {
            $title = substr($subject, 4);
            $topic_id = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_topics', 'id', array('t_cache_first_title' => $title, 't_forum_id' => $this->forum_id));
        } else {
            $title = $subject;
        }
        $is_starter = ($topic_id === null);

        if ($is_starter) {
            require_code('cns_topics_action');
            $topic_id = cns_make_topic($this->forum_id);
        }
        require_code('cns_posts_action');
        if (is_guest($member_id)) {
            $poster_name_if_guest = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
        } else {
            $poster_name_if_guest = titleify(str_replace('.', ' ', preg_replace('#[@+].*$#', '', $from_email)));
        }
        $post_id = cns_make_post($topic_id, $title, $body, 0, $is_starter, null, 0, $poster_name_if_guest);

        if (count($attachment_errors) != 0) {
            $post_url = $GLOBALS['FORUM_DRIVER']->post_url($post_id, '');

            $this->send_bounce_email__attachment_errors($subject, $body, $from_email, $email_bounce_to, $attachment_errors, $post_url);
        }
    }

    /**
     * Strip system code from an e-mail body.
     *
     * @param  string $body E-mail body
     * @param  integer $format A STRIP_* constant
     */
    protected function strip_system_code($body, $format)
    {
        switch ($format) {
            case self::STRIP_SUBJECT:
                $strings = array();
                foreach (array_keys(find_all_langs()) as $lang) {
                    $strings[] = do_lang('MAILING_LIST_SIMPLE_SUBJECT_new_regexp', null, null, null, $lang);
                    $strings[] = do_lang('MAILING_LIST_SIMPLE_SUBJECT_reply_regexp', null, null, null, $lang);
                }
                foreach ($strings as $s) {
                    $body = preg_replace('#' . $s . '#', '', $body);
                }
                break;

            case self::STRIP_HTML:
                $strings = array();
                foreach (array_keys(find_all_langs()) as $lang) {
                    $strings[] = do_lang('MAILING_LIST_SIMPLE_MAIL_regexp', null, null, null, $lang);
                }
                foreach ($strings as $s) {
                    $body = preg_replace('#' . str_replace(array("\n", '---'), array("(\n|<br[^<>]*>)*", '<hr[^<>]*>'), $s) . '#i', '', $body);
                }
                break;

            case self::STRIP_TEXT:
                $strings = array();
                foreach (array_keys(find_all_langs()) as $lang) {
                    $strings[] = do_lang('MAILING_LIST_SIMPLE_MAIL_regexp', null, null, null, $lang);
                }
                foreach ($strings as $s) {
                    $body = preg_replace('#' . $s . '#i', '', $body);
                }
                break;
        }
    }

    /**
     * Send out an e-mail about us not recognising an e-mail address for an incoming e-mail.
     *
     * @param  string $subject Subject line of original message
     * @param  string $body Body of original message
     * @param  EMAIL $email E-mail address we tried to bind to
     * @param  EMAIL $email_bounce_to E-mail address of sender (usually the same as $email, but not if it was a forwarded e-mail)
     */
    protected function send_bounce_email__cannot_bind($subject, $body, $email, $email_bounce_to)
    {
        $extended_subject = do_lang('MAILING_LIST_CANNOT_BIND_SUBJECT', $subject, $email, array(get_site_name()), get_site_default_lang());
        $extended_message = do_lang('MAILING_LIST_CANNOT_BIND_MAIL', comcode_to_clean_text($body), $email, array($subject, get_site_name()), get_site_default_lang());

        $this->send_system_email($extended_subject, $extended_message, $email, $email_bounce_to);
    }

    /**
     * Send out an e-mail about us not having access to the forum.
     *
     * @param  string $subject Subject line of original message
     * @param  string $body Body of original message
     * @param  EMAIL $email E-mail address we tried to bind to
     * @param  EMAIL $email_bounce_to E-mail address of sender (usually the same as $email, but not if it was a forwarded e-mail)
     * @param  string $forum_name Forum name
     * @param  string $username Bound username
     */
    protected function send_bounce_email__access_denied($subject, $body, $email, $email_bounce_to, $forum_name, $username)
    {
        $extended_subject = do_lang('MAILING_LIST_ACCESS_DENIED_SUBJECT', $subject, $email, array(get_site_name(), $forum_name, $username), get_site_default_lang());
        $extended_message = do_lang('MAILING_LIST_ACCESS_DENIED_MAIL', comcode_to_clean_text($body), $email, array($subject, get_site_name(), $forum_name, $username), get_site_default_lang());

        $this->send_system_email($extended_subject, $extended_message, $email, $email_bounce_to);
    }
}
