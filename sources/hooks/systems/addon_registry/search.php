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
 * @package    search
 */

/**
 * Hook class.
 */
class Hook_addon_registry_search
{
    /**
     * Get a list of file permissions to set
     *
     * @param  boolean $runtime Whether to include wildcards represented runtime-created chmoddable files
     * @return array File permissions to set
     */
    public function get_chmod_array($runtime = false)
    {
        return array();
    }

    /**
     * Get the version of Composr this addon is for
     *
     * @return float Version number
     */
    public function get_version()
    {
        return cms_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string Description of the addon
     */
    public function get_description()
    {
        return 'Multi-content search engine.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_search',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(),
            'recommends' => array(),
            'conflicts_with' => array(),
            'previously_in_addon' => array(
                'search'
            )
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/adminzone/audit/statistics/search.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/audit/statistics/search.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/statistics/search.png',
            'sources/hooks/systems/realtime_rain/search.php',
            'themes/default/templates/TAGS.tpl',
            'sources/blocks/side_tag_cloud.php',
            'sources/hooks/systems/sitemap/search.php',
            'themes/default/templates/BLOCK_SIDE_TAG_CLOUD.tpl',
            'themes/default/templates/SEARCH_RESULT.tpl',
            'themes/default/templates/SEARCH_RESULT_TABLE.tpl',
            'sources/hooks/systems/addon_registry/search.php',
            'sources/hooks/modules/admin_stats/search.php',
            'sources/hooks/modules/admin_setupwizard/search.php',
            'themes/default/templates/SEARCH_ADVANCED.tpl',
            'themes/default/templates/BLOCK_MAIN_SEARCH.tpl',
            'themes/default/templates/BLOCK_TOP_SEARCH.tpl',
            'themes/default/templates/SEARCH_DOMAINS.tpl',
            'themes/default/templates/SEARCH_FORM_SCREEN.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_LIST.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_MULTI_LIST.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_TEXT.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_TICK.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_FLOAT.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_INTEGER.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_DATE.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_DATE.tpl',
            'themes/default/templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_TIME.tpl',
            'themes/default/css/search.css',
            'lang/EN/search.ini',
            'site/pages/modules/search.php',
            'sources/search.php',
            'sources/blocks/main_search.php',
            'sources/blocks/top_search.php',
            'sources/hooks/modules/search/.htaccess',
            'sources_custom/hooks/modules/search/.htaccess',
            'sources/hooks/modules/search/index.html',
            'sources_custom/hooks/modules/search/index.html',
            'themes/default/xml/OPENSEARCH.xml',
            'data/opensearch.php',
            'sources/hooks/systems/config/search_results_per_page.php',
            'sources/hooks/systems/config/search_with_date_range.php',
            'sources/hooks/systems/config/enable_boolean_search.php',
            'sources/hooks/systems/page_groupings/search.php',
            'sources/hooks/systems/config/minimum_autocomplete_length.php',
            'sources/hooks/systems/config/maximum_autocomplete_suggestions.php',
            'sources/hooks/systems/config/minimum_autocomplete_past_search.php',
            'sources/hooks/systems/commandr_fs_extended_member/searches_saved.php',
            'sources/hooks/systems/config/block_top_search.php',
            'themes/default/javascript/search.js',
        );
    }

    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array The mapping
     */
    public function tpl_previews()
    {
        return array(
            'templates/BLOCK_MAIN_SEARCH.tpl' => 'block_main_search',
            'templates/BLOCK_TOP_SEARCH.tpl' => 'block_top_search',
            'templates/BLOCK_SIDE_TAG_CLOUD.tpl' => 'block_side_tag_cloud',
            'templates/TAGS.tpl' => 'tags',
            'xml/OPENSEARCH.xml' => 'opensearch',
            'templates/SEARCH_RESULT.tpl' => 'search_form_screen',
            'templates/SEARCH_RESULT_TABLE.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION.tpl' => 'search_form_screen',
            'templates/SEARCH_ADVANCED.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN.tpl' => 'search_form_screen',
            'templates/SEARCH_DOMAINS.tpl' => 'search_form_screen',
            'templates/SEARCH_FORM_SCREEN.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_LIST.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_MULTI_LIST.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_TEXT.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_TICK.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_FLOAT.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_INTEGER.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_DATE.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_DATE.tpl' => 'search_form_screen',
            'templates/SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_TIME.tpl' => 'search_form_screen',
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_main_search()
    {
        return array(
            lorem_globalise(do_lorem_template('BLOCK_MAIN_SEARCH', array(
                'BLOCK_ID' => lorem_word(),
                'TITLE' => lorem_phrase(),
                'INPUT_FIELDS' => array('a' => array('LABEL' => lorem_phrase(), 'INPUT' => '')),
                'EXTRA' => placeholder_array(),
                'SORT' => lorem_phrase(),
                'AUTHOR' => lorem_phrase(),
                'DAYS' => lorem_phrase(),
                'DIRECTION' => lorem_phrase(),
                'ONLY_TITLES' => '1',
                'ONLY_SEARCH_META' => '1',
                'BOOLEAN_SEARCH' => '1',
                'CONJUNCTIVE_OPERATOR' => 'AND',
                'LIMIT_TO' => placeholder_array(),
                'URL' => placeholder_url(),
                'FULL_SEARCH_URL' => placeholder_url(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_top_search()
    {
        return array(
            lorem_globalise(do_lorem_template('BLOCK_TOP_SEARCH', array(
                'BLOCK_ID' => lorem_word(),
                'TITLE' => lorem_phrase(),
                'EXTRA' => placeholder_array(),
                'SORT' => lorem_phrase(),
                'AUTHOR' => lorem_phrase(),
                'DAYS' => lorem_phrase(),
                'DIRECTION' => lorem_phrase(),
                'ONLY_TITLES' => '1',
                'ONLY_SEARCH_META' => '1',
                'BOOLEAN_SEARCH' => '1',
                'CONJUNCTIVE_OPERATOR' => 'AND',
                'LIMIT_TO' => placeholder_array(),
                'URL' => placeholder_url(),
                'FULL_SEARCH_URL' => placeholder_url(),
            )), null, '', false)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__block_side_tag_cloud()
    {
        $tpl_tags = array();
        $tags = array(
            lorem_word() => 3,
            lorem_word_2() => 5
        );
        foreach ($tags as $tag => $count) {
            $em = 1.0;
            $tpl_tags[] = array(
                'TAG' => $tag,
                'COUNT' => strval($count),
                'EM' => float_to_raw_string($em),
                'LINK' => placeholder_url(),
            );
        }

        return array(
            lorem_globalise(do_lorem_template('BLOCK_SIDE_TAG_CLOUD', array(
                'BLOCK_ID' => lorem_word(),
                'TITLE' => lorem_phrase(),
                'TAGS' => $tpl_tags,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__tags()
    {
        return array(
            lorem_globalise(do_lorem_template('TAGS', array(
                'TAGS' => array(
                    array(
                        'LINK_FULLSCOPE' => placeholder_url(),
                        'TAG' => lorem_word(),
                    ),
                    array(
                        'LINK_FULLSCOPE' => placeholder_url(),
                        'TAG' => lorem_word(),
                    )
                ),
                'TYPE' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__opensearch()
    {
        return array(
            do_lorem_template('OPENSEARCH', array(
                'DESCRIPTION' => lorem_paragraph(),
            ), null, false, null, '.xml', 'xml')
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__search_form_screen()
    {
        require_css('forms');

        require_code('database_search');

        $options = new Tempcode();
        foreach (placeholder_array() as $value) {
            $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION', array(
                'NAME' => placeholder_random_id(),
                'DISPLAY' => lorem_phrase(),
                'SPECIAL' => lorem_phrase(),
                'CHECKED' => lorem_phrase(),
            )));
        }

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_LIST', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => placeholder_options(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_MULTI_LIST', array(
            'NAME' => placeholder_random(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => placeholder_options(),
            'CHECKED' => false
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_TEXT', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_TICK', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_FLOAT', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_INTEGER', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_DATE', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_DATE', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $options->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN_OPTION_JUST_TIME', array(
            'NAME' => placeholder_random_id(),
            'DISPLAY' => lorem_word(),
            'SPECIAL' => lorem_word(),
            'CHECKED' => false,
        )));

        $specialisation = do_lorem_template('SEARCH_ADVANCED', array(
            'AJAX' => lorem_phrase(),
            'OPTIONS' => $options,
            'TREE' => '',
            'UNDERNEATH' => lorem_phrase(),
        ));

        $search_domains = new Tempcode();
        foreach (placeholder_array() as $value) {
            $search_domains->attach(do_lorem_template('SEARCH_FOR_SEARCH_DOMAIN', array(
                'ADVANCED_ONLY' => lorem_phrase(),
                'CHECKED' => lorem_phrase(),
                'OPTIONS_URL' => placeholder_url(),
                'LANG' => lorem_phrase(),
                'NAME' => placeholder_random_id(),
            )));
        }

        $specialisation->attach(do_lorem_template('SEARCH_DOMAINS', array(
            'SEARCH_DOMAINS' => $search_domains,
        )));

        $result = new Tempcode();
        $result->attach(do_lorem_template('SEARCH_RESULT', array(
            'CONTENT' => lorem_paragraph_html(),
            'TYPE' => placeholder_id(),
            'ID' => placeholder_id(),
        )));

        $types_results = array();
        foreach (placeholder_array() as $i => $r) {
            $types_results[$i] = array(
                'R' => placeholder_array(),
            );
        }

        $result->attach(do_lorem_template('SEARCH_RESULT_TABLE', array(
            'HEADERS' => placeholder_array(),
            'ROWS' => $types_results,
        )));

        require_lang('catalogues');
        $result->attach(do_lorem_template('SEARCH_RESULT_CATALOGUE_ENTRIES', array(
            'BUILDUP' => lorem_phrase(),
            'NAME' => lorem_word(),
            'TITLE' => lorem_word_2(),
        )));

        return array(
            lorem_globalise(do_lorem_template('SEARCH_FORM_SCREEN', array(
                'SEARCH_TERM' => lorem_word_2(),
                'NUM_RESULTS' => placeholder_number(),
                'EXTRA_SORT_FIELDS' => placeholder_array(0),
                'USER_LABEL' => lorem_word(),
                'DAYS_LABEL' => lorem_word(),
                'BOOLEAN_SEARCH' => false,
                'AND' => false,
                'ONLY_TITLES' => true,
                'DAYS' => placeholder_id(),
                'SORT' => 'relevance',
                'DIRECTION' => 'DESC',
                'CONTENT' => lorem_phrase(),
                'RESULTS' => null,
                'PAGINATION' => '',
                'HAS_FULLTEXT_SEARCH' => true,
                'TITLE' => lorem_title(),
                'AUTHOR' => lorem_phrase(),
                'SPECIALISATION' => $specialisation,
                'URL' => placeholder_url(),
                'HAS_TEMPLATE_SEARCH' => true,
            )), null, '', true)
        );
    }
}
