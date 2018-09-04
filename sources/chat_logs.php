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
 * @package    chat
 */

/**
 * Pass out chat log files.
 */
function chat_logs_script()
{
    if (!addon_installed('chat')) {
        warn_exit(do_lang_tempcode('MISSING_ADDON', escape_html('chat')));
    }

    header('X-Robots-Tag: noindex');

    // Closed site
    $site_closed = get_option('site_closed');
    if (($site_closed == '1') && (!has_privilege(get_member(), 'access_closed_site')) && (!$GLOBALS['IS_ACTUALLY_ADMIN'])) {
        http_response_code(503);
        header('Content-type: text/plain; charset=' . get_charset());
        @exit(get_option('closed'));
    }

    // Check we are allowed here
    if (!has_actual_page_access(get_member(), 'chat')) {
        access_denied('PAGE_ACCESS');
    }

    require_lang('chat');
    require_code('chat');

    $room = get_param_integer('room', 1);
    $start = get_param_integer('start', 0);
    $finish = get_param_integer('finish', time());

    $start_date_seed = getdate($start);
    $finish_date_seed = getdate($finish);

    $room_check = $GLOBALS['SITE_DB']->query_select('chat_rooms', array('id', 'is_im', 'allow_list', 'allow_list_groups', 'disallow_list', 'disallow_list_groups', 'room_owner'), array('id' => $room), '', 1);
    if (!array_key_exists(0, $room_check)) {
        warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'chat'));
    }
    check_chatroom_access($room_check[0]);

    $start_date = ($start == 0) ? '' : strval($start_date_seed['year']) . '-' . strval($start_date_seed['mon']) . '-' . strval($start_date_seed['mday']) . ',' . strval($start_date_seed['hours']) . ':' . strval($start_date_seed['minutes']);
    $finish_date = strval($finish_date_seed['year']) . '-' . strval($finish_date_seed['mon']) . '-' . strval($finish_date_seed['mday']) . ',' . strval($finish_date_seed['hours']) . ':' . strval($finish_date_seed['minutes']);

    $messages = chat_get_room_content($room, $room_check, null, false, true, intval($start), intval($finish), null, get_param_string('zone', get_module_zone('chat')));

    if (($messages === null) || (count($messages) == 0)) {
        // There are no messages
        warn_exit(do_lang_tempcode('NO_ENTRIES'));
    }

    // Build the text file
    $message_contents = new Tempcode();
    foreach ($messages as $_message) {
        $message_contents->attach(do_template('CHAT_MESSAGE', array(
            '_GUID' => 'ff22f181850feaba2a062b7edf71e332',
            'STAFF' => false,
            'OLD_MESSAGES' => true,
            'SYSTEM_MESSAGE' => strval($_message['system_message']),
            'AVATAR_URL' => '',
            'STAFF_ACTIONS' => '',
            'MEMBER' => escape_html($_message['username']),
            'MESSAGE' => $_message['the_message'],
            'DATE' => $_message['date_and_time_nice'],
            '_TIME' => strval($_message['date_and_time']),
            'FONT_COLOUR' => $_message['text_colour'],
            'FONT_FACE' => $_message['font_name'],
        )));
    }

    // Send header
    $room_name = get_chatroom_name($messages[0]['room_id']);
    $filename = 'chatlog-' . str_replace(' ', '', $room_name) . '-' . str_replace(':', '-', $start_date) . '-' . str_replace(':', '-', $finish_date) . '.html';
    header('Content-Type: application/octet-stream');
    if ((strpos($room_name, "\n") !== false) || (strpos($room_name, "\r") !== false)) {
        log_hack_attack_and_exit('HEADER_SPLIT_HACK');
    }
    header('Content-Disposition: attachment; filename="' . escape_header($filename, true) . '"');

    if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
        return;
    }

    $message_contents = do_template('BASIC_HTML_WRAP', array(
        '_GUID' => 'ff052ede2357f894a219c27a3ec75642',
        'TITLE' => do_lang('CHAT_LOGS', escape_html(get_site_name()), escape_html($room_name), array(escape_html($start_date), escape_html($finish_date))),
        'CONTENT' => $message_contents,
    ));

    echo $message_contents->evaluate();
}
