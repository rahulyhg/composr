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
 * @package    stats
 */

/**
 * Hook class.
 */
class Hook_sw_stats
{
    /**
     * Run function for features in the setup wizard.
     *
     * @return array Current settings
     */
    public function get_current_settings()
    {
        $settings = array();
        $settings['stats_store_time'] = get_option('stats_store_time');
        return $settings;
    }

    /**
     * Run function for features in the setup wizard.
     *
     * @param  array $field_defaults Default values for the fields, from the install-profile
     * @return array A pair: Input fields, Hidden fields
     */
    public function get_fields($field_defaults)
    {
        if (!addon_installed('stats') || post_param_integer('addon_stats', null) === 0) {
            return array(new Tempcode(), new Tempcode());
        }

        $field_defaults += $this->get_current_settings(); // $field_defaults will take precedence, due to how "+" operator works in PHP

        $stats_store_time = $field_defaults['stats_store_time'];

        require_lang('stats');
        $fields = new Tempcode();
        $fields->attach(form_input_integer(do_lang_tempcode('STORE_TIME'), do_lang_tempcode('CONFIG_OPTION_stats_store_time'), 'stats_store_time', intval($stats_store_time), true));

        return array($fields, new Tempcode());
    }

    /**
     * Run function for setting features from the setup wizard.
     */
    public function set_fields()
    {
        if (!addon_installed('stats') || post_param_integer('addon_stats', null) === 0) {
            return;
        }

        set_option('stats_store_time', post_param_string('stats_store_time'));
    }
}
