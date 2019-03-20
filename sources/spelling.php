<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: pspell\_.+|enchant\_.+*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_form_interfaces
 */

/**
 * Standard code module initialisation function.
 *
 * @ignore
 */
function init__spelling()
{
    if (!defined('WORD_REGEXP')) {
        define('WORD_REGEXP', '#([\w\'\-]{1,200})#' . ((get_charset() == 'utf-8') ? 'u' : ''));
    }
}

/**
 * Find all the words in some text.
 *
 * @param string $text Text to scan for words in (should be plain text, not be HTML text)
 * @return array List of words
 */
function find_words($text)
{
    $words = array();

    $_words = array();
    $num_matches = preg_match_all(WORD_REGEXP, $text, $_words);

    for ($i = 0; $i < $num_matches; $i++) {
        $word = $_words[1][$i];

        if (strtoupper($word) == $word) { // Full caps means acronym
            continue;
        }
        if (strlen($word) == 1) { // Too short for a word
            continue;
        }

        $words[trim($word, "'")] = true;
    }

    return array_keys($words);
}

/**
 * Find the active spell checker.
 *
 * @return ?string Spell checker (null: none)
 */
function find_spell_checker()
{
    $spell_checker = get_value('force_spell_checker');
    if ($spell_checker !== null) {
        return $spell_checker;
    }

    if (function_exists('pspell_check')) {
        return 'pspell';
    }

    if (function_exists('enchant_dict_check')) {
        return 'enchant';
    }

    return null;
}

/**
 * Fix spellings in input string.
 *
 * @param string $text Input string
 * @return string Fixed input string
 */
function spell_correct_phrase($text)
{
    if (find_spell_checker() === null) {
        return $text;
    }

    $errors = run_spellcheck($text);

    $parts = preg_split(WORD_REGEXP, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    $out = '';
    foreach ($parts as $part) {
        if ((isset($errors[cms_mb_strtolower($part)])) && (count($errors[cms_mb_strtolower($part)]) != 0)) {
            $out .= $errors[cms_mb_strtolower($part)][0];
        } else {
            $out .= $part;
        }
    }

    return $out;
}

/**
 * Run a spellcheck on some text.
 *
 * @param string $text Text to scan for words in (should be plain text, not be HTML text)
 * @param ?ID_TEXT $lang Language to check in (null: current language)
 * @param boolean $skip_known_words_in_db Whether to avoid spellchecking known keywords etc
 * @return array A map of misspellings, lower case bad word => array of corrections
 */
function run_spellcheck($text, $lang = null, $skip_known_words_in_db = true)
{
    if (find_spell_checker() === null) {
        return array();
    }

    $words = find_words($text);

    if (count($words) == 0) {
        return array();
    }

    // Filter out what might be misspelled but we know actually is a proper word
    if ($skip_known_words_in_db) {
        $non_words = array(
            // Some common Composr terms that should not be corrected
            'comcode',
            'tempcode',
            'selectcode',
            'filtercode',
        );

        $or_list = '';
        foreach ($words as $word) {
            if ($or_list != '') {
                $or_list .= ' OR ';
            }
            $or_list .= db_string_equal_to('w_replacement', $word);
        }
        if (addon_installed('wordfilter')) {
            $_non_words = $GLOBALS['SITE_DB']->query_select('wordfilter', array('w_replacement'), null, 'WHERE ' . $or_list);
            foreach ($_non_words as $_non_word) {
                if ($_non_word['w_replacement'] != '') {
                    $non_words[] = $_non_word['w_replacement'];
                }
            }
        }

        if (multi_lang_content()) {
            $or_list = '';
            foreach ($words as $word) {
                if ($or_list != '') {
                    $or_list .= ' OR ';
                }
                $or_list .= db_string_equal_to('text_original', $word);
            }
            $_non_words = $GLOBALS['SITE_DB']->query_select('seo_meta_keywords k JOIN ' . get_table_prefix() . 'translate t ON k.meta_keyword=t.id', array('DISTINCT text_original AS meta_keyword'), null, 'WHERE ' . $or_list);
        } else {
            $or_list = '';
            foreach ($words as $word) {
                if ($or_list != '') {
                    $or_list .= ' OR ';
                }
                $or_list .= db_string_equal_to('meta_keyword', $word);
            }
            $_non_words = $GLOBALS['SITE_DB']->query_select('seo_meta_keywords', array('DISTINCT meta_keyword'), null, 'WHERE ' . $or_list);
        }
        foreach ($_non_words as $_non_word) {
            if ($_non_word['meta_keyword'] != '') {
                $non_words[] = $_non_word['meta_keyword'];
            }
        }

        $words = array_diff($words, $non_words);
    }

    $errors = array();

    // Run checks
    $spell_link = _spellcheck_initialise($lang);
    if (find_spell_checker() === 'pspell') {
        if ($spell_link !== null) {
            foreach ($words as $word) {
                if (!pspell_check($spell_link, $word)) {
                    $corrections = pspell_suggest($spell_link, $word);
                    $errors[cms_mb_strtolower($word)] = $corrections;
                }
            }
        }
    }
    if (find_spell_checker() === 'enchant') {
        list($broker, $dict, $personal_dict) = $spell_link;

        if ($dict !== null) {
            foreach ($words as $word) {
                $corrections = array();
                if (!enchant_dict_quick_check($dict, $word, $corrections)) {
                    $errors[cms_mb_strtolower($word)] = $corrections;
                }
            }

            //enchant_broker_free($broker); Seems to crash on some PHP versions
        }
    }

    return $errors;
}

/**
 * Add words to the spellchecker.
 *
 * @param array $words List of words
 */
function add_spellchecker_words($words)
{
    $spell_link = _spellcheck_initialise();

    if (find_spell_checker() === 'pspell') {
        if ($spell_link !== null) {
            foreach ($words as $word) {
                pspell_add_to_personal($spell_link, $word);
            }

            pspell_save_wordlist($spell_link);
        }
    }
    if (find_spell_checker() === 'enchant') {
        list($broker, $dict, $personal_dict) = $spell_link;

        if ($dict !== null) {
            foreach ($words as $word) {
                enchant_dict_add_to_personal($personal_dict, $word);
            }

            enchant_broker_free($broker);
        }
    }
}

/**
 * Initialise the spellcheck engine.
 *
 * @param ?ID_TEXT $lang Language to check in (null: current language)
 * @return ?mixed Spellchecker (null: error)
 *
 * @ignore
 */
function _spellcheck_initialise($lang = null)
{
    if (is_null($lang)) {
        $lang = user_lang();
    }
    $lang = function_exists('do_lang') ? do_lang('dictionary') : 'en_GB'; // Default to UK English (as per Composr)

    $charset = function_exists('do_lang') ? do_lang('charset') : 'utf-8';

    $spelling = function_exists('do_lang') ? do_lang('dictionary_variant') : 'british';
    if ($spelling == $lang) {
        $spelling = '';
    }

    $p_dict_path = sl_get_custom_file_base() . '/data_custom/spelling/personal_dicts';

    if (find_spell_checker() === 'pspell') {
        $charset = str_replace('ISO-', 'iso', str_replace('iso-', 'iso', $charset));

        $pspell_config = @pspell_config_create($lang, $spelling, '', $charset);
        if ($pspell_config === false) { // Fallback
            $pspell_config = @pspell_config_create('en', $spelling, '', $charset);
            if ($pspell_config === false) {
                return null;
            }
        }
        pspell_config_personal($pspell_config, $p_dict_path . '/' . $lang . '.pws');
        $spell_link = @pspell_new_config($pspell_config);

        if ($spell_link === false) { // Fallback: Might be that we had a late fail on initialising that language
            $pspell_config = @pspell_config_create('en', $spelling, '', $charset);
            if ($pspell_config === false) {
                return null;
            }
            pspell_config_personal($pspell_config, $p_dict_path . '/' . $lang . '.pws');
            $spell_link = @pspell_new_config($pspell_config);
            if ($spell_link === false) {
                return null;
            }
        }
    }
    if (find_spell_checker() === 'enchant') {
        $broker = enchant_broker_init();

        if (!enchant_broker_dict_exists($broker, $lang)) {
            $lang = 'en';
        }

        $dict = enchant_broker_request_dict($broker, $lang);

        $personal_dict = enchant_broker_request_pwl_dict($broker, $p_dict_path . '/' . user_lang() . '.pwl');

        $spell_link = array($broker, $dict, $personal_dict);
    }

    return $spell_link;
}

/**
 * Find the path to where data is stored.
 *
 * @return string Relative path
 */
function sl_get_custom_file_base()
{
    if (function_exists('get_custom_file_base')) {
        return get_custom_file_base();
    }

    return dirname(dirname(__FILE));
}
