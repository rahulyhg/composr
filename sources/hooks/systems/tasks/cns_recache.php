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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_task_cns_recache
{
    /**
     * Run the task hook.
     *
     * @return ?array A tuple of at least 2: Return mime-type, content (either Tempcode, or a string, or a filename and file-path pair to a temporary file), map of HTTP headers if transferring immediately, map of ini_set commands if transferring immediately (null: show standard success message)
     */
    public function run()
    {
        cns_require_all_forum_stuff();

        // Forums
        require_code('cns_posts_action2');
        $start = 0;
        do {
            $forums = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id'), array(), '', 100, $start);
            foreach ($forums as $i => $forum) {
                task_log($this, 'Recaching forum', $i, count($forums));

                cns_force_update_forum_caching($forum['id']);
            }
            $start += 100;
        } while ($forums != array());

        return null;
    }
}
