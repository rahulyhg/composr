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
 * @package    core
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__mail_forms()
{
    require_code('mail');
}

/**
 * Entry script to process a form that needs to be emailed.
 */
function form_to_email_entry_script()
{
    form_to_email();

    global $PAGE_NAME_CACHE;
    $PAGE_NAME_CACHE = '_form_to_email';

    $title = get_screen_title('MAIL_SENT');

    $text = do_lang_tempcode('MAIL_SENT_TEXT', escape_html(post_param_string('to_written_name', get_site_name())));

    $redirect = get_param_string('redirect', null, INPUT_FILTER_URL_INTERNAL);
    if ($redirect !== null) {
        require_code('site2');
        $tpl = redirect_screen($title, $redirect, $text);
    } else {
        $tpl = do_template('INFORM_SCREEN', array('_GUID' => 'e577a4df79eefd9064c14240cc99e947', 'TITLE' => $title, 'TEXT' => $text));
    }

    $echo = globalise($tpl, null, '', true);
    $echo->evaluate_echo();
}

/**
 * Send the POSTed form over e-mail to the staff address.
 *
 * @param  ?string $subject The subject of the e-mail (null: from POSTed/tagged subject parameter).
 * @param  string $subject_prefix The prefix text to the e-mail subject (blank: none).
 * @param  string $subject_suffix The suffix text to the e-mail subject (blank: none).
 * @param  string $body_prefix The prefix text to the e-mail body (blank: none).
 * @param  string $body_suffix The suffix text to the e-mail body (blank: none).
 * @param  ?array $fields A map of fields to field titles to transmit. (null: all POSTed fields, except subject and e-mail)
 * @param  ?string $to_email E-mail address to send to (null: look from POST environment [if allowed] / staff address).
 * @param  boolean $is_via_post Whether $fields refers to some POSTed fields, as opposed to a direct field->value map.
 */
function form_to_email($subject = null, $subject_prefix = '', $subject_suffix = '', $body_prefix = '', $body_suffix = '', $fields = null, $to_email = null, $is_via_post = true)
{
    // Data
    $details = _form_to_email(array(), $subject, $subject_prefix, $subject_suffix, $body_prefix, $body_suffix, $fields, $to_email, $is_via_post);
    list($subject, $body, $to_email, $to_name, $from_email, $from_name, $attachments, $body_parts) = $details;

    // Check CAPTCHA
    if (addon_installed('captcha')) {
        if (post_param_integer('_security', 0) == 1) {
            require_code('captcha');
            enforce_captcha();
        }
    }

    // User metadata
    if (addon_installed('securitylogging')) {
        require_code('lookup');
        $user_metadata_path = save_user_metadata();
        $attachments[$user_metadata_path] = 'user_metadata.txt';
    }

    // Do we actually send the message somewhere else rather than e-mailing it? E.g. to a CRM
    $block_email = false;
    $hooks = find_all_hook_obs('systems', 'contact_forms', 'Hook_contact_forms_');
    foreach ($hooks as $ob) {
        $block_email |= $ob->dispatch($subject, $body, $to_email, $to_name, $from_email, $from_name, $attachments, $body_parts, $body_prefix, $body_suffix);
    }

    // Send e-mail
    if (!$block_email) {
        dispatch_mail($subject, $body, ($to_email === null) ? null : array($to_email), $to_name, $from_email, $from_name, array('attachments' => $attachments));
    }

    // Send standard confirmation email to current user
    if ($from_email != '' && get_option('message_received_emails') == '1') {
        dispatch_mail(do_lang('YOUR_MESSAGE_WAS_SENT_SUBJECT', $subject), do_lang('YOUR_MESSAGE_WAS_SENT_BODY', $from_email), array($from_email), null, '', '', array('as' => get_member()));
    }
}

/**
 * Worker funtion for form_to_email.
 *
 * @param  array $extra_boring_fields Fields to skip in addition to the normal skipped ones
 * @param  ?string $subject The subject of the e-mail (null: from POSTed/tagged subject parameter).
 * @param  string $subject_prefix The prefix text to the e-mail subject (blank: none).
 * @param  string $subject_suffix The suffix text to the e-mail subject (blank: none).
 * @param  string $body_prefix The prefix text to the e-mail body (blank: none).
 * @param  string $body_suffix The suffix text to the e-mail body (blank: none).
 * @param  ?array $fields A map of field names to field titles to transmit. (null: all POSTed fields, except certain standardised ones)
 * @param  ?string $to_email E-mail address to send to (null: look from POST environment [if allowed] / staff address).
 * @param  boolean $is_via_post Whether $fields refers to some POSTed fields, as opposed to a direct field->value map.
 * @return array A tuple: subject, message, to e-mail, to name, from e-mail, from name, attachments, body parts (if calling code wants partials instead of a single $message)
 *
 * @ignore
 */
function _form_to_email($extra_boring_fields = array(), $subject = null, $subject_prefix = '', $subject_suffix = '', $body_prefix = '', $body_suffix = '', $fields = null, $to_email = null, $is_via_post = true)
{
    // Find subject...

    if (empty($subject)) {
        $subject = post_param_string('subject', get_param_string('title', get_site_name()));
    }

    // Decide fields...

    if ($fields === null) {
        $fields = array();
        foreach (array_keys($_POST) as $key) {
            if (is_control_field($key, true, false, $extra_boring_fields)) {
                continue;
            }

            $label = post_param_string('label_for__' . $key, titleify($key));
            $description = post_param_string('description_for__' . $key, '');
            $_label = $label . (($description == '') ? '' : (' (' . $description . ')'));

            if ($is_via_post) {
                $fields[$key] = $_label;
            } else {
                $fields[$label] = post_param_string($key, null);
            }
        }
    }

    // Find from details if simple...

    $from_email = trim(post_param_string('email', ''));
    $from_name = trim(post_param_string('name', post_param_string('poster_name_if_guest', '')));

    // Find body...

    $body = '';
    $body_parts = array();

    if ($body_prefix != '') {
        $body .= $body_prefix . "\n\n------------\n\n";
    }

    if ($is_via_post) {
        foreach ($fields as $field_name => $field_title) {
            $field_val = post_param_string($field_name, null);
            if ($field_val !== null) {
                // Tie in to tagging
                if (post_param_string('field_tagged__' . $field_name, '') == 'email') {
                    $from_email = $field_val;
                    continue;
                }
                if (post_param_string('field_tagged__' . $field_name, '') == 'name') {
                    $from_name = $field_val;
                    continue;
                }
                if (post_param_string('field_tagged__' . $field_name, '') == 'subject') {
                    $subject = $field_val;
                    continue;
                }

                _append_form_to_email($body, post_param_integer('tick_on_form__' . $field_name, null) !== null, $field_name, $field_title, $field_val, count($fields), $body_parts);
            }
        }
    } else {
        foreach ($fields as $field_title => $field_val) {
            if ($field_val !== null) {
                _append_form_to_email($body, false, $field_name, $field_title, $field_val, count($fields), $body_parts);
            }
        }
    }

    if ($body_suffix != '') {
        $body .= "\n\n------------\n\n" . $body_suffix;
    }

    // Find from details if complex...

    if ($from_email == '') {
        $from_email = $GLOBALS['FORUM_DRIVER']->get_member_email_address(get_member());
    }
    if ($from_name == '') {
        $from_name = $GLOBALS['FORUM_DRIVER']->get_username(get_member(), true);
    }

    // Find to details if enabled...

    $to_name = mixed();
    if (($to_email === null) && (get_value('allow_member_mail_relay') !== null)) {
        $to = post_param_integer('to_members_email', null);
        if ($to !== null) {
            $to_email = $GLOBALS['FORUM_DRIVER']->get_member_email_address($to);
            $to_name = $GLOBALS['FORUM_DRIVER']->get_username($to, true);
        }
    }

    // Find attachments...

    $attachments = array();
    require_code('uploads');
    is_plupload(true);
    foreach ($_FILES as $file) {
        $attachments[$file['tmp_name']] = $file['name'];
    }

    // ---

    return array($subject_prefix . $subject . $subject_suffix, $body, $to_email, $to_name, $from_email, $from_name, $attachments, $body_parts);
}

/**
 * Append a value to a text e-mail.
 *
 * @param  string $body Text-email (altered by reference).
 * @param  boolean $is_tick Whether it is a tick field.
 * @param  string $field_name Field name.
 * @param  string $field_title Field title.
 * @param  string $field_val Field value.
 * @param  integer $num_fields Number of fields for e-mail.
 * @param  array $body_parts Body parts (returned by reference).
 *
 * @ignore
 */
function _append_form_to_email(&$body, $is_tick, $field_name, $field_title, $field_val, $num_fields, &$body_parts)
{
    $prefix = '';
    if ($num_fields != 1) {
        $prefix .= '[b]' . $field_title . '[/b]:';
        if (strpos($prefix, "\n") !== false || strpos($field_title, ' (') !== false) {
            $prefix .= "\n";
        } else {
            $prefix .= " ";
        }
    }

    $cleaned_field_val = $field_val;

    if ($is_tick && in_array($field_val, array('', '0', '1'))) {
        $cleaned_field_val = ($field_val == '1') ? do_lang('YES') : do_lang('NO');
    } else {
        if ($field_val == '') {
            return; // We won't show blank values, gets long
        }

        if ($field_val == '') {
            $cleaned_field_val = '(' . do_lang('EMPTY') . ')';
        }
    }

    $body .= $prefix;
    $body .= $cleaned_field_val;

    $body .= "\n\n";

    $body_parts[$field_name] = $cleaned_field_val;
}
