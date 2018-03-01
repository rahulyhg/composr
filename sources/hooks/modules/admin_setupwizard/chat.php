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
 * @package    chat
 */

/**
 * Hook class.
 */
class Hook_sw_chat
{
    /**
     * Run function for blocks in the setup wizard.
     *
     * @return array Map of block names, to display types
     */
    public function get_blocks()
    {
        if (!addon_installed('chat')) {
            return array();
        }

        return array(array(), array('side_shoutbox' => array('PANEL_NONE', 'PANEL_RIGHT')));
    }
}
