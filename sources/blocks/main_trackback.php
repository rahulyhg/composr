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
 * @package    core_feedback_features
 */

/**
 * Block class.
 */
class Block_main_trackback
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Philip Withnall';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'page', 'id');
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
        if (!array_key_exists('page', $map)) {
            $map['page'] = get_page_name();
        }

        if (array_key_exists('id', $map)) {
            $id = $map['id'];
        } else {
            $id = get_param_string('id', '0');
        }

        require_code('feedback');

        actualise_post_trackback(get_option('is_on_trackbacks') == '1', $map['page'], $id);

        return get_trackbacks($map['page'], $id, get_option('is_on_trackbacks') == '1');
    }
}
