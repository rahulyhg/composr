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
 * @package    tickets
 */

/**
 * Hook class.
 */
class Hook_preview_ticket
{
    /**
     * Find whether this preview hook applies.
     *
     * @return array Quartet: Whether it applies, the attachment ID type (may be null), whether the forum DB is used [optional], list of fields to limit to [optional]
     */
    public function applies()
    {
        $applies = (get_page_name() == 'tickets');
        return array($applies, 'cns_post', false, array('post'));
    }

    /**
     * Run function for preview hooks.
     *
     * @return array A pair: The preview, the updated post Comcode (may be null)
     */
    public function run()
    {
        return array(null, null, false);
    }
}
