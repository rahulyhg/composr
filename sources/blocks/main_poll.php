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
 * @package    polls
 */

/**
 * Block class.
 */
class Block_main_poll
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'zone');
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters.
     * @return Tempcode The result of execution.
     */
    public function run($map)
    {
        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('polls');

        require_code('polls');
        require_css('polls');
        require_lang('polls');

        $block_id = get_block_id($map);

        // Action links
        if ((has_actual_page_access(null, 'cms_polls', null, null)) && (has_submit_permission('mid', get_member(), get_ip_address(), 'cms_polls'))) {
            $submit_url = build_url(array('page' => 'cms_polls', 'type' => 'add', 'redirect' => get_self_url(true, false)), get_module_zone('cms_polls'));
        } else {
            $submit_url = new Tempcode();
        }

        // Lookup poll row
        $poll_id = mixed();
        if (array_key_exists('param', $map)) {
            $poll_id = intval($map['param']);
        }
        if ($poll_id === null) {
            $rows = persistent_cache_get('POLL');
            if ($rows === null) {
                $rows = $GLOBALS['SITE_DB']->query_select('poll', array('*'), array('is_current' => 1), 'ORDER BY id DESC', 1);
                persistent_cache_set('POLL', $rows);
            }
        } else {
            $rows = $GLOBALS['SITE_DB']->query_select('poll', array('*'), array('id' => $poll_id), '', 1);
        }
        if (!array_key_exists(0, $rows)) {
            return do_template('BLOCK_NO_ENTRIES', array(
                '_GUID' => 'fdc85bb2e14bdf00830347e52f25cdac',
                'BLOCK_ID' => $block_id,
                'HIGH' => true,
                'TITLE' => do_lang_tempcode('POLL'),
                'MESSAGE' => do_lang_tempcode('NO_ENTRIES', 'poll'),
                'ADD_NAME' => do_lang_tempcode('ADD_POLL'),
                'SUBMIT_URL' => $submit_url,
            ));
        }
        $myrow = $rows[0];
        $poll_id = $myrow['id'];

        // Show the poll
        $show_poll_results = get_param_integer('show_poll_results_' . strval($poll_id), 0);
        if ($show_poll_results == 0) {
            $content = render_poll_box(false, $myrow, $zone, true, false);
        } else {
            // Vote
            $cast = post_param_integer('cast_' . strval($poll_id), null);
            $myrow = vote_in_poll($poll_id, $cast, $myrow); // Either an active vote, or a forfeited vote (viewing results)

            // Show poll, with results
            $content = render_poll_box(true, $myrow, $zone, true, false);
        }

        // Render block wrapper template around poll
        return do_template('BLOCK_MAIN_POLL', array(
            '_GUID' => '06a5b384015504a6a57fc4ddedbe91a7',
            'BLOCK_ID' => $block_id,
            'BLOCK_PARAMS' => block_params_arr_to_str($map),
            'CONTENT' => $content,
        ));
    }
}
