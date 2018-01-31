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
 * @package    cns_forum
 */

/**
 * Hook class.
 */
class Hook_admin_themewizard_cns_forum
{
    /**
     * Find details of images to include/exclude in the Theme Wizard.
     *
     * @return array A pair: List of theme image patterns to include, List of theme image patterns to exclude
     */
    public function run()
    {
        return array(array('icons/cns_general/no_new_posts_redirect', 'icons/cns_general/new_posts_redirect', 'icons/cns_general/no_new_posts', 'icons/cns_general/new_posts', 'icons/cns_topic_modifiers/involved',), array('pageitem/warn',));
    }
}
