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
 * @package    core
 */

/**
 * Block class.
 */
class Block_bottom_about_us
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Salman Abbas';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 1;
        $info['locked'] = false;
        $info['parameters'] = array('facebook', 'twitter', 'instagram', 'youtube');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled)
     */
    public function caching_environment()
    {
        $info = array();
        $info['cache_on'] = <<<'PHP'
            array(
                !empty($map['facebook']) ? $map['facebook'] : '',
                !empty($map['twitter']) ? $map['twitter'] : '',
                !empty($map['instagram']) ? $map['instagram'] : '',
                !empty($map['youtube']) ? $map['youtube'] : '',
            )
PHP;
        $info['ttl'] = (get_value('disable_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters
     * @return Tempcode The result of execution
     */
    public function run($map)
    {
        $site_description = get_option('description');

        $parameters = array(
            '_GUID' => '31dda95820ad401f972909931830eeac',
            'SITE_DESCRIPTION' => $site_description,
        );

        if (!empty($map['facebook'])) {
            $parameters['FACEBOOK_URL'] = 'https://facebook.com/' . $map['facebook'];
        }

        if (!empty($map['twitter'])) {
            $parameters['TWITTER_URL'] = 'https://twitter.com/' . $map['twitter'];
        }

        if (!empty($map['instagram'])) {
            $parameters['INSTAGRAM_URL'] = 'https://instagram.com/' . $map['instagram'];
        }

        if (!empty($map['youtube'])) {
            $parameters['YOUTUBE_URL'] = 'https://youtube.com/' . $map['youtube'];
        }

        return do_template('BLOCK_BOTTOM_ABOUT_US', $parameters);
    }
}