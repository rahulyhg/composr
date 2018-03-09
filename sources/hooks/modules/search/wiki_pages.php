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
 * @package    wiki
 */

/**
 * Hook class.
 */
class Hook_search_wiki_pages extends FieldsSearchHook
{
    /**
     * Find details for this search hook.
     *
     * @param  boolean $check_permissions Whether to check permissions
     * @return ?array Map of search hook details (null: hook is disabled)
     */
    public function info($check_permissions = true)
    {
        if (!addon_installed('wiki')) {
            return null;
        }

        if ($check_permissions) {
            if (!has_actual_page_access(get_member(), 'wiki')) {
                return null;
            }
        }

        if ($GLOBALS['SITE_DB']->query_select_value('wiki_pages', 'COUNT(*)') <= 1) {
            return null;
        }

        require_lang('wiki');

        $info = array();
        $info['lang'] = do_lang_tempcode('WIKI_PAGES');
        $info['default'] = (get_option('search_wiki_pages') == '1');
        $info['extra_sort_fields'] = $this->_get_extra_sort_fields('_wiki_page');

        $info['permissions'] = array(
            array(
                'type' => 'zone',
                'zone_name' => get_module_zone('wiki'),
            ),
            array(
                'type' => 'page',
                'zone_name' => get_module_zone('wiki'),
                'page_name' => 'wiki',
            ),
        );

        return $info;
    }

    /**
     * Get a list of extra fields to ask for.
     *
     * @return ?array A list of maps specifying extra fields (null: no tree)
     */
    public function get_fields()
    {
        return $this->_get_fields('_wiki_page');
    }

    /**
     * Run function for search results.
     *
     * @param  string $content Search string
     * @param  boolean $only_search_meta Whether to only do a META (tags) search
     * @param  ID_TEXT $direction Order direction
     * @param  integer $max Start position in total results
     * @param  integer $start Maximum results to return in total
     * @param  boolean $only_titles Whether only to search titles (as opposed to both titles and content)
     * @param  string $content_where Where clause that selects the content according to the main search string (SQL query fragment) (blank: full-text search)
     * @param  SHORT_TEXT $author Username/Author to match for
     * @param  ?MEMBER $author_id Member-ID to match for (null: unknown)
     * @param  mixed $cutoff Cutoff date (TIME or a pair representing the range)
     * @param  string $sort The sort type (gets remapped to a field in this function)
     * @set    title add_date
     * @param  integer $limit_to Limit to this number of results
     * @param  string $boolean_operator What kind of boolean search to do
     * @set    or and
     * @param  string $where_clause Where constraints known by the main search code (SQL query fragment)
     * @param  string $search_under Comma-separated list of categories to search under
     * @param  boolean $boolean_search Whether it is a boolean search
     * @return array List of maps (template, orderer)
     */
    public function run($content, $only_search_meta, $direction, $max, $start, $only_titles, $content_where, $author, $author_id, $cutoff, $sort, $limit_to, $boolean_operator, $where_clause, $search_under, $boolean_search)
    {
        $remapped_orderer = '';
        switch ($sort) {
            case 'title':
                $remapped_orderer = 'title';
                break;

            case 'add_date':
                $remapped_orderer = 'add_date';
                break;
        }

        require_code('wiki');
        require_lang('wiki');

        // Calculate our where clause (search)
        if ($author != '') {
            return array();
        }
        $this->_handle_date_check($cutoff, 'add_date', $where_clause);

        $table = 'wiki_pages r';
        $trans_fields = array('r.title' => 'SHORT_TRANS', 'r.description' => 'LONG_TRANS__COMCODE');
        $nontrans_fields = array();
        $this->_get_search_parameterisation_advanced_for_content_type('_wiki_page', $table, $where_clause, $trans_fields, $nontrans_fields);

        // Calculate and perform query
        $rows = get_search_rows('wiki_page', 'id', $content, $boolean_search, $boolean_operator, $only_search_meta, $direction, $max, $start, $only_titles, $table, $trans_fields, $where_clause, $content_where, $remapped_orderer, 'r.*', $nontrans_fields, 'wiki_page', 'id');

        $out = array();
        foreach ($rows as $i => $row) {
            $out[$i]['data'] = $row;
            unset($rows[$i]);
            if (($remapped_orderer != '') && (array_key_exists($remapped_orderer, $row))) {
                $out[$i]['orderer'] = $row[$remapped_orderer];
            } elseif (strpos($remapped_orderer, '_rating:') !== false) {
                $out[$i]['orderer'] = $row[$remapped_orderer];
            }
        }

        return $out;
    }

    /**
     * Run function for rendering a search result.
     *
     * @param  array $row The data row stored when we retrieved the result
     * @return Tempcode The output
     */
    public function render($row)
    {
        global $SEARCH__CONTENT_BITS;
        $highlight_bits = ($SEARCH__CONTENT_BITS === null) ? array() : $SEARCH__CONTENT_BITS;
        push_lax_comcode(true);
        $summary = get_translated_text($row['description']);
        $text_summary_h = comcode_to_tempcode($summary, null, false, null, null, COMCODE_NORMAL, $highlight_bits);
        pop_lax_comcode();
        $text_summary = generate_text_summary($text_summary_h->evaluate(), $highlight_bits);

        return render_wiki_page_box($row, '_SEARCH', true, true, null, '', protect_from_escaping($text_summary));
    }
}
