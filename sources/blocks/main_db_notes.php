<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

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
 * Block class.
 */
class Block_main_db_notes
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
        $info['parameters'] = array('param', 'title', 'scrolls');
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
        $block_id = get_block_id($map);

        $file = empty($map['param']) ? 'admin_notes' : $map['param'];
        $title = array_key_exists('title', $map) ? $map['title'] : do_lang('NOTES');
        $scrolls = array_key_exists('scrolls', $map) ? $map['scrolls'] : '0';

        $new = post_param_string('new', null);
        if ($new !== null) {
            set_value('note_text_' . $file, $new, true);
            log_it('NOTES', $file);

            attach_message(do_lang_tempcode('SUCCESS'), 'inform');
        }

        $contents = get_value('note_text_' . $file, null, true);
        if ($contents === null) {
            $contents = '';
        }

        $post_url = get_self_url();

        $map_comcode = get_block_ajax_submit_map($map);
        return do_template('BLOCK_MAIN_NOTES', array(
            '_GUID' => '2a9e1c512b66600583735552b56e0911',
            'BLOCK_ID' => $block_id,
            'TITLE' => $title,
            'BLOCK_NAME' => 'main_db_notes',
            'MAP' => $map_comcode,
            'SCROLLS' => array_key_exists('scrolls', $map) && ($map['scrolls'] == '1'),
            'CONTENTS' => $contents,
            'URL' => $post_url,
        ));
    }
}
