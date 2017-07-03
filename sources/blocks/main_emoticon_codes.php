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
 * @package    core_rich_media
 */

/**
 * Block class.
 */
class Block_main_emoticon_codes
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
        $info['parameters'] = array('num_columns');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = 'array(array_key_exists(\'num_columns\', $map) ? intval($map[\'num_columns\']) : 5)';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS; // Due to special emoticon codes privilege
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 2;
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
        require_code('comcode_compiler');
        require_code('comcode_renderer');

        $block_id = get_block_id($map);

        $num_columns = array_key_exists('num_columns', $map) ? intval($map['num_columns']) : 4;

        global $EMOTICON_LEVELS;

        $_emoticons = $GLOBALS['FORUM_DRIVER']->find_emoticons(get_member());
        $emoticons = array();

        foreach ($_emoticons as $code => $imgcode) {
            if ((is_null($EMOTICON_LEVELS)) || ($EMOTICON_LEVELS[$code] < 3)) { // If within a displayable level
                $emoticons[] = array(
                    'CODE' => $code,
                    'TPL' => do_emoticon($imgcode),
                );
            }
        }

        return do_template('BLOCK_MAIN_EMOTICON_CODES', array(
            '_GUID' => '56c12281d7e3662b13a7ad7d9958a65c',
            'BLOCK_ID' => $block_id,
            'EMOTICONS' => $emoticons,
            'NUM_COLUMNS' => strval($num_columns),
        ));
    }
}
