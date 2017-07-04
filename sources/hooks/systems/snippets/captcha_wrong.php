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
 * @package    captcha
 */

/**
 * Hook class.
 */
class Hook_snippet_captcha_wrong
{
    /**
     * Run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
     *
     * @return Tempcode The snippet
     */
    public function run()
    {
        $val = get_param_string('name');

        require_code('captcha');
        if (check_captcha($val)) {
            return new Tempcode();
        }

        return make_string_tempcode(strip_html(do_lang('INVALID_SECURITY_CODE_ENTERED')));
    }
}
