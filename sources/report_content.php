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
 * @package    tickets
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__report_content()
{
    require_lang('report_content');
    require_lang('tickets');

    require_code('tickets');
    require_code('tickets2');
}

/**
 * Check the current user has post reporting access.
 */
function check_report_content_access()
{
    if ((!has_privilege(get_member(), 'may_report_content')) || (!addon_installed('tickets'))) {
        access_denied('I_ERROR');
    }

    get_ticket_forum_id(); // Ensures forum exists
}

/**
 * Find the ticket type for reported content.
 *
 * @return AUTO_LINK The ticket type ID
 */
function find_reported_content_ticket_type()
{
    static $ticket_type_id = null;
    if ($ticket_type_id === null) {
        $ticket_type_id = $GLOBALS['SITE_DB']->query_select_value_if_there('ticket_types t', 't.id', array($GLOBALS['SITE_DB']->translate_field_ref('ticket_type_name') => do_lang('TT_REPORTED_CONTENT')));
        if ($ticket_type_id === null) {
            $ticket_type_id = post_param_integer('ticket_type_id', null);
        }
    }
    return $ticket_type_id;
}

/**
 * The UI to report content.
 *
 * @param  Tempcode $title Screen title
 * @param  ID_TEXT $content_type The content type being reported
 * @param  ID_TEXT $content_id The content ID being reported
 * @return Tempcode The UI
 */
function report_content_form($title, $content_type, $content_id)
{
    check_report_content_access();

    require_code('content');
    list($content_title, $content_member_id, $cma_info, $content_row, $content_url) = content_get_details($content_type, $content_id);

    $report_post = post_param_string('post', '');

    require_code('form_templates');

    url_default_parameters__enable();

    $ticket_id = ticket_generate_new_id(get_member(), $content_type . '_' . $content_id);

    $text = paragraph(do_lang_tempcode(
        'DESCRIPTION_REPORT_CONTENT',
        escape_html($content_title),
        escape_html(integer_format(intval(get_option('reported_times')))),
        ticket_allow_anonymous_posts() ? do_lang('REPORT_OR_ANONYMOUS') : ''
    ));
    report_content_append_text($text, $ticket_id);

    $hidden_fields = build_keep_form_fields('', true);
    $specialisation = report_content_form_fields($hidden_fields);

    $post_url = build_url(array('page' => 'report_content', 'type' => 'actual'), get_page_zone('report_content'));

    $posting_form = get_posting_form(do_lang('REPORT_CONTENT'), 'buttons/send', $report_post, $post_url, $hidden_fields, $specialisation, '', '', null, null, array(), null, true, false, false);

    url_default_parameters__disable();

    return do_template('POSTING_SCREEN', array(
        '_GUID' => '92a0a35a7c07edd0d3f8a960710de608',
        'TITLE' => $title,
        'JS_FUNCTION_CALLS' => (function_exists('captcha_ajax_check_function')) && (captcha_ajax_check_function() != '') ? array(captcha_ajax_check_function()) : array(),
        'TEXT' => $text,
        'POSTING_FORM' => $posting_form,
    ));
}

/**
 * The UI to report a post.
 *
 * @param  Tempcode $title Screen title
 * @param  AUTO_LINK $post_id The post ID
 * @param  array $js_function_calls JavaScript code to include
 * @param  ?array $topic_info The topic row (returned by reference) (null: )
 * @param  ?array $post_info The topic row (returned by reference) (null: )
 * @return Tempcode The UI
 */
function report_post_form($title, $post_id, $js_function_calls, &$topic_info = null, &$post_info = null)
{
    check_report_content_access();

    $_post_info = $GLOBALS['FORUM_DB']->query_select('f_posts', array('*'), array('id' => $post_id), '', 1);
    if (!array_key_exists(0, $_post_info)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'post'));
    }
    $post_info = $_post_info[0];

    $topic_id = $post_info['p_topic_id'];

    $_topic_info = $GLOBALS['FORUM_DB']->query_select('f_topics', array('*'), array('id' => $topic_id), '', 1);
    if (!array_key_exists(0, $_topic_info)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'topic'));
    }
    $topic_info = $_topic_info[0];

    $topic_title = $topic_info['t_cache_first_title'];

    $report_post = post_param_string('post', '');

    $ticket_id = ticket_generate_new_id(get_member(), 'post' . '_' . strval($post_id));

    require_code('form_templates');

    url_default_parameters__enable();

    $text = paragraph(do_lang_tempcode(
        'DESCRIPTION_REPORT_POST',
        escape_html($topic_title),
        escape_html(integer_format(intval(get_option('reported_times')))),
        ticket_allow_anonymous_posts() ? do_lang('REPORT_OR_ANONYMOUS') : ''
    ));
    report_content_append_text($text, $ticket_id);

    $hidden = new Tempcode();

    $specialisation = report_content_form_fields($hidden);

    $hidden->attach(form_input_hidden('post_id', strval($post_id)));

    $post_url = build_url(array('page' => 'topics', 'type' => '_report_post'), get_page_zone('topics'));

    $posting_form = get_posting_form(do_lang('REPORT_POST'), 'buttons/report', $report_post, $post_url, $hidden, $specialisation, '', '', null, null, array(), null, true, false, false);

    url_default_parameters__disable();

    if ((function_exists('captcha_ajax_check_function')) && (captcha_ajax_check_function() != '')) {
        $js_function_calls[] = captcha_ajax_check_function();
    }

    return do_template('POSTING_SCREEN', array(
        '_GUID' => 'eee64757e66fed702f74fecf8d595260',
        'TITLE' => $title,
        'JS_FUNCTION_CALLS' => $js_function_calls,
        'TEXT' => $text,
        'POSTING_FORM' => $posting_form,
    ));
}

/**
 * Get a member content link in Comcode format.
 *
 * @param  ?MEMBER $content_member_id Member ID of the original content (null: unknown)
 * @param  ?string $content_poster_name_if_guest Member name if a guest (null: unknown)
 * @return string Member link
 */
function report_content_member_link($content_member_id, $content_poster_name_if_guest)
{
    if ($content_poster_name_if_guest === null) {
        $content_poster_name_if_guest = do_lang('UNKNOWN');
    }
    if (($content_member_id !== null) && (!is_guest($content_member_id))) {
        if (!is_guest($content_member_id)) {
            $content_poster_name = $GLOBALS['FORUM_DRIVER']->get_username($content_member_id, true);
            $content_member = '[page="_SEARCH:members:view:' . strval($content_member_id) . '"]' . $content_poster_name . '[/page]';
        } else {
            $content_member = $content_poster_name_if_guest;
        }
    } else {
        $content_member = $content_poster_name_if_guest;
    }
    return $content_member;
}

/**
 * Get form fields (apart from main posting field) for report form.
 *
 * @param  Tempcode $hidden Hidden fields (returned by reference)
 * @return Tempcode Form fields
 */
function report_content_form_fields(&$hidden)
{
    require_code('form_templates');

    $specialisation = new Tempcode();

    if (addon_installed('captcha')) {
        require_code('captcha');
        if (use_captcha()) {
            $specialisation->attach(form_input_captcha($hidden));
        }
    }

    if ((!is_guest()) && (ticket_allow_anonymous_posts())) {
        $options = array();
        $options[] = array(do_lang_tempcode('REPORT_ANONYMOUS'), 'anonymous', false, do_lang_tempcode('DESCRIPTION_REPORT_ANONYMOUS'));
        $field = form_input_various_ticks($options, '');
        $specialisation->attach($field);
    }

    if (is_guest()) {
        // If the reporter is a guest user, ask for, but do not require, an e-mail address for further communication.
        $field = form_input_email(do_lang('EMAIL_ADDRESS'), do_lang('DESCRIPTION_REPORT_EMAIL'), 'email', null, false, null);
        $specialisation->attach($field);
    }

    $ticket_type_id = find_reported_content_ticket_type();
    if ($ticket_type_id === null) {
        // There is no specific ticket type for reports. Build a list of, and ask for, ticket type.
        $types = build_types_list(db_get_first_id());
        $list_entries = new Tempcode();
        foreach ($types as $type) {
            $list_entries->attach(form_input_list_entry($type['TICKET_TYPE_ID'], $type['SELECTED'], $type['NAME']));
        }
        $field = form_input_list(do_lang('TICKET_TYPE'), '', 'ticket_type_id', $list_entries);
        $specialisation->attach($field);
    }

    return $specialisation;
}

/**
 * Get standard text for a report form.
 *
 * @param  Tempcode $text Append the text here
 * @param  ID_TEXT $ticket_id Ticket ID
 */
function report_content_append_text(&$text, $ticket_id)
{
    if (ticket_exists($ticket_id)) {
        // If session already reported this content and the associated ticket is still open, tell the user that report will go as another post in same ticket
        $text->attach(paragraph(do_lang_tempcode('DUPLICATE_REPORT')));
    }

    if (addon_installed('captcha')) {
        require_code('captcha');
        if (use_captcha()) {
            $text->attach(paragraph(do_lang_tempcode('FORM_TIME_SECURITY')));
        }
    }

    if (addon_installed('points')) {
        $login_url = build_url(array('page' => 'login', 'type' => 'browse', 'redirect' => protect_url_parameter(SELF_REDIRECT_RIP)), get_module_zone('login'));
        $_login_url = escape_html($login_url->evaluate());
        if ((is_guest()) && ((get_forum_type() != 'cns') || (has_actual_page_access(get_member(), 'join')))) {
            $text->attach(paragraph(do_lang_tempcode('NOT_LOGGED_IN_NO_CREDIT', $_login_url)));
        }
    }
}

/**
 * The actualiser to report content.
 *
 * @param  ID_TEXT $content_type Post ID being reported
 * @param  ID_TEXT $content_id Post ID being reported
 * @param  string $report_post Report post
 * @param  BINARY $anonymous Anonymous
 * @param  BINARY $open Report is open
 * @param  ?TIME $time Report time (null: now)
 * @param  ?MEMBER $member_id Reporting member (null: current member)
 * @return object URL to content
 */
function report_content($content_type, $content_id, $report_post, $anonymous = 0, $open = 1, $time = null, $member_id = null)
{
    require_code('content');
    list($content_title, $content_member_id, $cma_info, $content_row, $content_url) = content_get_details($content_type, $content_id);
    $ob = get_content_object($content_type);
    $content_rendered = $ob->run($content_row, get_module_zone($cma_info['module']));

    $content_member = $GLOBALS['FORUM_DRIVER']->get_username($content_member_id, true);

    check_report_content_access();

    $report_title = do_lang('REPORTED_CONTENT_TITLE', $content_title);

    $ticket_id = ticket_generate_new_id($member_id, $content_type . '_' . $content_id);

    if (ticket_exists($ticket_id)) {
        // If there is already an open ticket for this report, let's make a post inside that ticket instead of making a new ticket and report
        $_report_post = do_lang('REPORTED_CONTENT_EXTRA', $report_post);
    } else {
        $content_member_link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($content_member_id, $content_member, true, false);

        // Post will have extra information added around it
        $_report_post = static_evaluate_tempcode(do_template('REPORTED_CONTENT_FCOMCODE', array(
            '_GUID' => 'cb40aa1900eefcd24a0786b9d980fef6',
            'CONTENT_URL' => $content_url,
            'CONTENT_TYPE' => $content_type,
            'CONTENT_ID' => $content_id,
            'CONTENT_MEMBER' => $content_member,
            'CONTENT_MEMBER_ID' => strval($content_member_id),
            'CONTENT_MEMBER_LINK' => $content_member_link,
            'CONTENT_TITLE' => $content_title,
            'CONTENT_RENDERED' => $content_rendered,
            'REPORT_POST' => $report_post,
        ), null, false, null, '.txt', 'text'));
    }

    $email = trim(post_param_string('email', ''));
    $_report_post = ticket_wrap_with_email_address($_report_post, $email);

    return _report_content($content_type, $content_id, $report_title, $_report_post, $anonymous, $open, $time, $member_id);
}

/**
 * The actualiser to report a post.
 *
 * @param  AUTO_LINK $post_id Post ID being reported
 * @param  string $report_post Report post
 * @param  BINARY $anonymous Anonymous
 * @param  BINARY $open Report is open
 * @param  ?TIME $time Report time (null: now)
 * @param  ?MEMBER $member_id Reporting member (null: current member)
 * @return object URL to content
 */
function report_post($post_id, $report_post, $anonymous = 0, $open = 1, $time = null, $member_id = null)
{
    check_report_content_access();

    $table_prefix = $GLOBALS['FORUM_DB']->get_table_prefix();
    $_post_info = $GLOBALS['FORUM_DB']->query_select('f_posts p JOIN ' . $table_prefix . 'f_topics t on t.id=p.p_topic_id', array('*', 'p.id AS post_id', 't.id AS topic_id'), array('p.id' => $post_id), '', 1);
    if (!array_key_exists(0, $_post_info)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'post'));
    }
    $post_info = $_post_info[0];

    $topic_title = $post_info['t_cache_first_title'];

    $content_member_id = $post_info['p_poster'];
    $content_member = report_content_member_link($content_member_id, $post_info['p_poster_name_if_guest']);

    $ticket_id = ticket_generate_new_id($member_id, 'post' . '_' . strval($post_id));

    if (ticket_exists($ticket_id)) {
        // If there is already an open ticket for this report, let's make a post inside that ticket instead of making a new ticket and report
        $_report_post = do_lang('REPORTED_CONTENT_EXTRA', $report_post);
    } else {
        $post = preg_replace('#\[staff_note\].*\[/staff_note\]#Us', '', get_translated_text($post_info['p_post'], $GLOBALS['FORUM_DB']));
        $_report_post = static_evaluate_tempcode(do_template('CNS_REPORTED_POST_FCOMCODE', array(
            '_GUID' => '6e9a43a3503c357b52b724e11d3d4eef',
            'POST_ID' => strval($post_id),
            'POST_MEMBER' => $content_member,
            'POST_MEMBER_ID' => strval($content_member_id),
            'TOPIC_TITLE' => $topic_title,
            'POST' => $post,
            'REPORT_POST' => $report_post,
        ), null, false, null, '.txt', 'text'));
    }

    $_title = $post_info['p_title'];
    if ($_title == '') {
        $_title = $post_info['t_cache_first_title'];
    }
    $report_title = do_lang('REPORTED_POST_TITLE', $_title);

    $email = trim(post_param_string('email', ''));
    $_report_post = ticket_wrap_with_email_address($_report_post, $email);

    $content_type = 'post';
    $content_id = strval($post_id);
    return _report_content($content_type, $content_id, $report_title, $_report_post, $anonymous, $open, $time, $member_id);
}

/**
 * The shared backend to report content/posts.
 *
 * @param  ID_TEXT $content_type The content type being reported
 * @param  ID_TEXT $content_id The content ID being reported
 * @param  string $report_title Report title
 * @param  string $report_post Report post
 * @param  BINARY $anonymous Anonymous
 * @param  BINARY $open Report is open
 * @param  ?TIME $time Report time (null: now)
 * @param  ?MEMBER $member_id Reporting member (null: current member)
 * @return object URL to content
 */
function _report_content($content_type, $content_id, $report_title, $report_post, $anonymous = 0, $open = 1, $time = null, $member_id = null)
{
    if ($member_id === null) {
        $member_id = get_member();
    }
    if ($anonymous == 1) {
        $member_id = $GLOBALS['FORUM_DRIVER']->get_guest_id();
        $email = '';
    } else {
        $email = trim(post_param_string('email', $GLOBALS['FORUM_DRIVER']->get_member_email_address($member_id)));
    }

    $forum_id = get_ticket_forum_id();

    $ticket_id = ticket_generate_new_id($member_id, $content_type . '_' . $content_id);

    $ticket_type_id = find_reported_content_ticket_type();
    if ($ticket_type_id === null) {
        $ticket_type_id = post_param_integer('ticket_type_id'); // Force error message
    }

    $ticket_url = ticket_add_post($ticket_id, $ticket_type_id, $report_title, $report_post, false, $member_id, $time);

    // Auto monitor this ticket for all support operators if auto assign is enabled
    if ((has_privilege(get_member(), 'support_operator')) && (get_option('ticket_auto_assign') == '1')) {
        require_code('notifications');
        enable_notifications('ticket_assigned_staff', $ticket_id);
    }

    // Find true ticket title
    list($ticket_title, $topic_id) = get_ticket_meta_details($ticket_id);

    // Send e-mail, if e-mail address was provided either by guest field or logged in user
    if ($email != '') {
        send_ticket_email($ticket_id, $ticket_title, $report_post, $ticket_url, $email, $ticket_type_id, null);
    }

    if (get_forum_type() == 'cns') {
        if ($anonymous == 1) {
            $post_id = $GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_cache_last_post_id', array('id' => $topic_id));
            log_it('MAKE_ANONYMOUS_POST', strval($post_id), $report_title);
        }
    }

    delete_cache_entry('main_staff_checklist');

    // If a report topic was closed then we will block any further reports from this member counting towards non-validation
    $counts_for_unvalidation = true;
    if (get_forum_type() == 'cns') {
        $topic_id = null;
        if (ticket_exists($ticket_id, $topic_id)) {
            if ($GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_is_open', array('id' => $topic_id)) == 0) {
                $counts_for_unvalidation = false;
            }
        }
    }

    // Add to reported_content table
    $GLOBALS['SITE_DB']->query_delete('reported_content', array(
        'r_session_id' => get_session_id(),
        'r_content_type' => $content_type,
        'r_content_id' => $content_id,
    ), '', 1);
    $GLOBALS['SITE_DB']->query_insert('reported_content', array(
        'r_session_id' => get_session_id(),
        'r_content_type' => $content_type,
        'r_content_id' => $content_id,
        'r_counts' => ($counts_for_unvalidation ? 1 : 0),
    ));

    require_code('content');
    list(, , $cma_info, , $content_url) = content_get_details($content_type, $content_id);

    // If hit threshold, mark down r_counts and non-validate the content
    $count = $GLOBALS['SITE_DB']->query_select_value('reported_content', 'COUNT(*)', array(
        'r_content_type' => $content_type,
        'r_content_id' => $content_id,
        'r_counts' => 1, // All those not already counted to a de-validation
    ));
    if ($count >= intval(get_option('reported_times'))) {
        // Mark as non-validated
        if (($cma_info['validated_field'] !== null) && (strpos($cma_info['table'], '(') === false)) {
            $db = get_db_for($cma_info['table']);
            $db->query_update($cma_info['table'], array($cma_info['validated_field'] => 0), get_content_where_for_str_id($content_id, $cma_info));
        }

        // Reset all those that made it non-validated
        $GLOBALS['SITE_DB']->query_update('reported_content', array('r_counts' => 0), array(
            'r_content_type' => $content_type,
            'r_content_id' => $content_id,
        ));
    }

    return $content_url;
}

/**
 * Find if a particular support ticket ID exists.
 *
 * @param  ID_TEXT $ticket_id Ticket ID
 * @param  ?AUTO_LINK $topic_id Topic ID (null: )
 * @return boolean Whether it exists
 */
function ticket_exists($ticket_id, &$topic_id = null)
{
    $details = get_ticket_meta_details($ticket_id, false);
    if ($details !== null) {
        $topic_id = $details[1];
        return true;
    }

    $topic_id = null;
    return false;
}

/**
 * Whether anonymous posts are allowed.
 *
 * @return boolean Whether anonymous posts are allowed
 */
function ticket_allow_anonymous_posts()
{
    require_code('cns_forums');
    $forum_id = get_ticket_forum_id();
    return (get_forum_type() == 'cns') && (cns_forum_allows_anonymous_posts($forum_id));
}
