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
 * @package    sms
 */

/**
 * Hook class.
 */
class Hook_cns_cpf_filter_sms
{
    /**
     * Find which special CPFs to enable.
     *
     * @return array A list of CPFs to enable
     */
    public function to_enable()
    {
        if (!addon_installed('sms')) {
            return array();
        }

        $cpf = array();
        if (get_option('sms_username') != '') {
            $cpf['mobile_phone_number'] = true;
        }
        return $cpf;
    }
}
