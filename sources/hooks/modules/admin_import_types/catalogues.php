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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_admin_import_types_catalogues
{
    /**
     * Get a map of valid import types.
     *
     * @return array A map from codename to the language string that names them to the user.
     */
    public function run()
    {
        return array(
            'catalogue_links' => 'DEFAULT_CATALOGUE_LINKS_TITLE',
            'catalogue_projects' => 'DEFAULT_CATALOGUE_PROJECTS_TITLE',
            'catalogue_faqs' => 'DEFAULT_CATALOGUE_FAQS_TITLE',
            'catalogues' => 'CATALOGUES',
            'ephems' => 'EPHEMS',
            'contacts' => 'CONTACTS',
            'reviews' => 'REVIEWS',
        );
    }
}
