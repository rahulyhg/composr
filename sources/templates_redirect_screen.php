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
 * @package    core_abstract_interfaces
 */

/**
 * Redirect the user - transparently, storing a message that will be shown on their destination page.
 *
 * @param  ?Tempcode $title Title to display on redirect page (null: standard redirection title)
 * @param  mixed $url Destination URL (may be Tempcode)
 * @param  ?mixed $text Message (may be Tempcode) to show in the HTML of the redirect screen (which is usually never seen) and also after the redirect (null: standard redirection message which will only show in the HTML of the redirect screen and nothing after)
 * @param  boolean $intermediary_hop For intermediary hops, don't mark so as to read status messages - save them up for the next hop (which will not be intermediary)
 * @param  ID_TEXT $msg_type Code of message type to show
 * @set warn inform fatal
 * @return Tempcode Redirection message (likely to not actually be seen due to instant redirection)
 * @ignore
 */
function _redirect_screen($title, $url, $text = null, $intermediary_hop = false, $msg_type = 'inform')
{
    if (is_object($url)) {
        $url = $url->evaluate();
    }

    global $ATTACHED_MESSAGES_RAW;

    foreach ($ATTACHED_MESSAGES_RAW as $message) {
        $_message = is_object($message[0]) ? $message[0]->evaluate() : escape_html($message[0]);
        if (($_message != '') && ($_message != do_lang('_REDIRECTING')) && (strpos($_message, 'cancel_sw_warn') === false)) {
            $GLOBALS['SITE_DB']->query_insert('messages_to_render', array(
                'r_session_id' => get_session_id(),
                'r_message' => $_message,
                'r_type' => $message[1],
                'r_time' => time(),
            ));
        }
    }

    // Even if we have $FORCE_META_REFRESH we want to relay $text if provided --- our delay may be as low zero so it won't always be read in time
    if ($text !== null) {
        $_message = is_object($text) ? $text->evaluate() : escape_html($text);
        if (($_message != '') && ($_message != do_lang('_REDIRECTING')) && (strpos($_message, 'cancel_sw_warn') === false)) {
            $GLOBALS['SITE_DB']->query_insert('messages_to_render', array(
                'r_session_id' => get_session_id(),
                'r_message' => $_message,
                'r_type' => $msg_type,
                'r_time' => time(),
            ));
        }
    }

    if (!$intermediary_hop) {
        $hash_pos = strpos($url, '#');
        if ($hash_pos !== false) {
            $hash_bit = substr($url, $hash_pos);
            $url = substr($url, 0, $hash_pos);
        } else {
            $hash_bit = '';
        }
        extend_url($url, 'redirected=1');
        $url .= $hash_bit;
    }

    if ($title === null) {
        $title = get_screen_title('REDIRECTING');
    }

    if ($text === null) {
        $text = do_lang_tempcode('_REDIRECTING');
    }

    require_code('site2');
    assign_refresh($url, 0.0);
    return do_template('REDIRECT_SCREEN', array('_GUID' => '44ce3d1ffc6536b299ed0944e8ca7253', 'URL' => $url, 'TITLE' => $title, 'TEXT' => $text));
}
