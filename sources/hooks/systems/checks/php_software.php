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
 * Hook class.
 */
class Hook_check_php_software
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $warning = array();

        if ((!is_maintained('platform_hhvm')) && (HHVM)) {
            $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('HHVM'), escape_html(get_brand_base_url()), escape_html('platform_hhvm'));
        }

        if ((!is_maintained('platform_gae')) && (GOOGLE_APPENGINE)) {
            $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('Google App Engine'), escape_html(get_brand_base_url()), escape_html('platform_gae'));
        }

        if ((!is_maintained('platform_phalanger')) && (defined('PHALANGER'))) {
            $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('Phalanger'), escape_html(get_brand_base_url()), escape_html('platform_phalanger'));
        }

        return $warning;
    }
}
