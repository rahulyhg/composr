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
 * @package    core_cleanup_tools
 */

/**
 * Hook class.
 */
class Hook_cleanup_lost_disk_content
{
    /**
     * Find details about this cleanup hook.
     *
     * @return ?array Map of cleanup hook info (null: hook is disabled)
     */
    public function info()
    {
        $info = array();
        $info['title'] = do_lang_tempcode('LOST_DISK_CONTENT');
        $info['description'] = do_lang_tempcode('DESCRIPTION_LOST_DISK_CONTENT');
        $info['type'] = 'optimise';

        return $info;
    }

    /**
     * Run the cleanup hook action.
     *
     * @return Tempcode Results
     */
    public function run()
    {
        require_code('zones3');

        // Cleanout zone records from dead zones
        $to_delete = array();
        $start = 0;
        $max = 100;
        do {
            $zones = $GLOBALS['SITE_DB']->query_select('zones', array('*'), array(), '', $max, $start);
            foreach ($zones as $zone) {
                if ((!is_file(get_custom_file_base() . '/' . $zone['zone_name'] . '/index.php')) && (!is_file(get_file_base() . '/' . $zone['zone_name'] . '/index.php'))) {
                    $to_delete[] = $zone['zone_name'];
                }
            }
            $start += $max;
        }
        while (count($zones) == $max);
        foreach ($to_delete as $zone_name) {
            actual_delete_zone_lite($zone_name);
        }

        // Cleanout Comcode page records from dead Comcode pages
        if ((!multi_lang()) || ($GLOBALS['DEV_MODE'])) {
            $to_delete = array();
            $start = 0;
            $max = 100;
            do {
                $pages = $GLOBALS['SITE_DB']->query_select('comcode_pages', array('the_zone', 'the_page'), array(), '', $max, $start);
                foreach ($pages as $page) {
                    if (
                        (!is_file(get_custom_file_base() . '/' . $page['the_zone'] . '/pages/comcode_custom/' . get_site_default_lang() . '/' . $page['the_page'] . '.txt')) &&
                        (!is_file(get_file_base() . '/' . $page['the_zone'] . '/pages/comcode_custom/' . get_site_default_lang() . '/' . $page['the_page'] . '.txt'))
                    ) {
                        $to_delete[] = array($page['the_zone'], $page['the_page']);
                    }
                }
                $start += $max;
            }
            while (count($zones) == $max);
            foreach ($to_delete as $page_details) {
                delete_cms_page($page_details[0], $page_details[1], 'comcode_custom');
                $GLOBALS['SITE_DB']->query_delete('comcode_pages', array('the_zone' => $page_details[0], 'the_page' => $page_details[1]), '', 1);
            }
        }

        return new Tempcode();
    }
}
