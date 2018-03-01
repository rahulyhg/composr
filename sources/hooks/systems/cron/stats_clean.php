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
class Hook_cron_stats_clean
{
    /**
     * Get info from this hook.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     * @param  boolean $calculate_num_queued Calculate the number of items queued, if possible
     * @return ?array Return a map of info about the hook (null: disabled)
     */
    public function info($last_run, $calculate_num_queued)
    {
        return array(
            'label' => 'Clean out old statistics',
            'num_queued' => null,
            'minutes_between_runs' => 0, // Regularly to reduce lock contention
        );
    }

    /**
     * Run function for system scheduler scripts. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run($last_run)
    {
        if (!$GLOBALS['SITE_DB']->table_is_locked('stats')) {
            $GLOBALS['SITE_DB']->query('DELETE FROM ' . get_table_prefix() . 'stats WHERE date_and_time<' . strval(time() - 60 * 60 * 24 * intval(get_option('stats_store_time'))), 500/*to reduce lock times*/);
        }
    }
}
