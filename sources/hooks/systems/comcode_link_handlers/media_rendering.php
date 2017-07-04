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
 * @package    core_rich_media
 */

/**
 * Hook class.
 */
class Hook_comcode_link_handler_media_rendering
{
    /**
     * Bind function for Comcode link handler hooks. They see if they can bind a pasted URL to a lump of handler Tempcode.
     *
     * @param  URLPATH $url Link to use or reject
     * @param  boolean $comcode_dangerous Whether we are allowed to proceed even if this tag is marked as 'dangerous'
     * @param  string $pass_id A special identifier to mark where the resultant Tempcode is going to end up (e.g. the ID of a post)
     * @param  integer $pos The position this tag occurred at in the Comcode
     * @param  MEMBER $source_member The member who is responsible for this Comcode
     * @param  boolean $as_admin Whether to check as arbitrary admin
     * @param  object $db The database connector to use
     * @param  string $comcode The whole chunk of Comcode
     * @param  boolean $structure_sweep Whether this is only a structure sweep
     * @param  boolean $semiparse_mode Whether we are in semi-parse-mode (some tags might convert differently)
     * @param  array $highlight_bits A list of words to highlight
     * @return ?Tempcode Handled link (null: reject due to inappropriate link pattern)
     */
    public function bind($url, $comcode_dangerous, $pass_id, $pos, $source_member, $as_admin, $db, $comcode, $structure_sweep, $semiparse_mode, $highlight_bits)
    {
        require_code('media_renderer');
        $ret = render_media_url($url, $url, array('context' => 'comcode_link', 'likely_not_framed' => '1'), $as_admin, $source_member);
        return $ret;
    }
}
