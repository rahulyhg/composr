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
 * @package    calendar
 */

/**
 * Hook class.
 */
class Hook_snippet_calendar_recurrence_suggest
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return Tempcode The snippet
     */
    public function run()
    {
        if (!addon_installed('calendar')) {
            return new Tempcode();
        }

        require_code('calendar');

        $date = post_param_date('date', true, false);

        $day_of_month = intval(gmdate('d', $date));
        $month = intval(gmdate('m', $date));
        $year = intval(gmdate('Y', $date));
        $hour = intval(gmdate('H', $date));
        $minute = intval(gmdate('i', $date));

        $do_timezone_conv = get_param_integer('do_timezone_conv');
        $all_day_event = get_param_integer('all_day_event');

        $default_monthly_spec_type = get_param_string('monthly_spec_type');

        return monthly_spec_type_chooser($day_of_month, $month, $year, $default_monthly_spec_type);
    }
}
