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
 * @package    setupwizard
 */

/**
 * Hook class.
 */
class Hook_preview_setupwizard_blocks
{
    /**
     * Find whether this preview hook applies.
     *
     * @return array Triplet: Whether it applies, the attachment ID type (may be null), whether the forum DB is used [optional]
     */
    public function applies()
    {
        $applies = (get_page_name() == 'admin_setupwizard') && (get_param_string('type', '') == 'step6');
        return array($applies, null, false);
    }

    /**
     * Run function for preview hooks.
     *
     * @return array A pair: The preview, the updated post Comcode (may be null)
     */
    public function run()
    {
        require_code('setupwizard');

        $collapse_zones = post_param_integer('single_public_zone', 0) == 1;

        $installprofile = post_param_string('installprofile', '');
        if ($installprofile != '') {
            require_code('hooks/modules/admin_setupwizard_installprofiles/' . filter_naughty_harsh($installprofile));
            $object = object_factory('Hook_admin_setupwizard_installprofiles_' . filter_naughty_harsh($installprofile));
            $installprofileblocks = $object->default_blocks();
            $block_options = $object->block_options();
        } else {
            $installprofileblocks = array();
            $block_options = array();
        }

        $page_structure = _get_zone_pages($installprofileblocks, $block_options, $collapse_zones, $installprofile);

        $zone_structure = array_pop($page_structure);

        $preview = do_template('SETUPWIZARD_BLOCK_PREVIEW', array('_GUID' => '77c2952691ead0a834a18fccfb6319d9', 'LEFT' => $zone_structure['left'], 'RIGHT' => $zone_structure['right'], 'START' => $zone_structure['start']));

        return array($preview, null);
    }
}
