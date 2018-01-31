<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    users_online_block
 */

/**
 * Hook class.
 */
class Hook_addon_registry_users_online_block
{
    /**
     * Get a list of file permissions to set.
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for.
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon.
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'A block to show which users who are currently visiting the website, and birthdays.';
    }

    /**
     * Get a list of tutorials that apply to this addon.
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_members',
        );
    }

    /**
     * Get a mapping of dependency types.
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used.
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/menu/social/users_online.svg';
    }

    /**
     * Get a list of files that belong to this addon.
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'sources/hooks/systems/config/usersonline_show_birthdays.php',
            'sources/hooks/systems/config/usersonline_show_newest_member.php',
            'sources/hooks/systems/addon_registry/users_online_block.php',
            'themes/default/templates/BLOCK_SIDE_USERS_ONLINE.tpl',
            'sources/blocks/side_users_online.php',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them.
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/BLOCK_SIDE_USERS_ONLINE.tpl' => 'block_side_users_online',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_side_users_online()
    {
        $online = array();
        foreach (placeholder_array() as $k => $v) {
            $online[] = array(
                'URL' => placeholder_url(),
                'USERNAME' => lorem_phrase(),
                'COLOUR' => lorem_word(),
                'MEMBER_ID' => placeholder_id(),
                'AVATAR_URL' => placeholder_image_url(),
            );
        }

        $newest = new Tempcode();

        $birthdays = array();
        foreach (placeholder_array() as $k => $v) {
            $birthdays[] = array(
                'AGE' => placeholder_number(),
                'PROFILE_URL' => placeholder_url(),
                'USERNAME' => lorem_word(),
                'BIRTHDAY_URL' => placeholder_url(),
            );
        }

        return array(
            lorem_globalise(do_lorem_template('BLOCK_SIDE_USERS_ONLINE', array(
                'BLOCK_ID' => lorem_word(),
                'ONLINE' => $online,
                'GUESTS' => placeholder_number(),
                'MEMBERS' => placeholder_number(),
                '_GUESTS' => lorem_phrase(),
                '_MEMBERS' => lorem_phrase(),
                'BIRTHDAYS' => $birthdays,
                'NEWEST' => $newest,
            )), null, '', true)
        );
    }
}
