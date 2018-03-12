<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    booking
 */

// TODO (can optionally take a filter of what bookables to allow choosing from) - for date ranges

class Block_side_book_date_range
{
    public function run()
    {
        $error_msg = new Tempcode();
        if (!addon_installed__autoinstall('booking', $error_msg)) {
            return $error_msg;
        }

        if (!addon_installed('calendar')) {
            return paragraph(do_lang_tempcode('MISSING_ADDON', escape_html('calendar')), '3o9nkf4sbm0yzo8jmdrmvqctvcu6njds', 'red-alert');
        }
        if (!addon_installed('ecommerce')) {
            return paragraph(do_lang_tempcode('MISSING_ADDON', escape_html('ecommerce')), 'zslwc37lxcpsluxrjvydbyiecdk6bq3m', 'red-alert');
        }

        return new Tempcode();
    }
}
