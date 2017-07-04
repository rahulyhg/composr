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
 * @package    core_notifications
 */

/**
 * Hook class.
 */
class Hook_startup_notification_poller_init
{
    /**
     * Run startup code.
     */
    public function run()
    {
        if ((running_script('index')) && (!is_guest()) && (get_option('notification_poll_frequency') != '') && (get_option('notification_poll_frequency') != '0')) {
            require_javascript('core_notifications');
            attach_to_screen_footer(do_template('NOTIFICATION_POLLER'));
        }
    }
}
