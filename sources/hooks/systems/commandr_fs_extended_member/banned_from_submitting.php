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
 * @package    securitylogging
 */

/**
 * Hook class.
 */
class Hook_commandr_fs_extended_member__banned_from_submitting
{
    /**
     * Whether the filesystem hook is active.
     *
     * @return boolean Whether it is
     */
    public function is_active()
    {
        return addon_installed('securitylogging');
    }

    /**
     * Read a virtual property for a member file.
     *
     * @param  MEMBER $member_id The member ID
     * @return mixed The data
     */
    public function read_property($member_id)
    {
        return ($GLOBALS['SITE_DB']->query_select_value_if_there('usersubmitban_member', 'the_member', array('the_member' => $member_id)) !== null);
    }

    /**
     * Read a virtual property for a member file.
     *
     * @param  MEMBER $member_id The member ID
     * @param  mixed $data The data
     */
    public function write_property($member_id, $data)
    {
        $GLOBALS['SITE_DB']->query_delete('usersubmitban_member', array('the_member' => $member_id), '', 1);
        if ($data === true) {
            $GLOBALS['SITE_DB']->query_insert('usersubmitban_member', array('the_member' => $member_id));
        }
    }
}
