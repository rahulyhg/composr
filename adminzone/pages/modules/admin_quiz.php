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
 * @package    quizzes
 */

/**
 * Module page class.
 */
class Module_admin_quiz
{
    /**
     * Find details of the module.
     *
     * @return ?array Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean $check_perms Whether to check permissions.
     * @param  ?MEMBER $member_id The member to check permissions as (null: current user).
     * @param  boolean $support_crosslinks Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean $be_deferential Whether to avoid any entry-point (or even return null to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "browse" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        return array(
            'browse' => array('MANAGE_QUIZZES', 'menu/rich_content/quiz'),
            'find_winner' => array('FIND_WINNER', 'menu/cms/quiz/find_winners'),
            'quiz_results' => array('QUIZ_RESULTS', 'menu/cms/quiz/quiz_results'),
            'export' => array('EXPORT_QUIZ', 'menu/_generic_admin/export'),
        );
    }

    public $title;
    public $row;

    /**
     * Module pre-run function. Allows us to know metadata for <head> before we start streaming output.
     *
     * @return ?Tempcode Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param_string('type', 'browse');

        require_lang('quiz');
        require_css('quizzes');

        set_helper_panel_tutorial('tut_quizzes');

        if ($type == 'browse') {
            $also_url = build_url(array('page' => 'cms_quiz'), get_module_zone('cms_quiz'));
            attach_message(do_lang_tempcode('menus:ALSO_SEE_CMS', escape_html($also_url->evaluate())), 'inform', true);
        }

        if ($type == 'find_winner') {
            breadcrumb_set_self(do_lang_tempcode('CHOOSE'));
            breadcrumb_set_parents(array(array('_SELF:_SELF', do_lang_tempcode('MANAGE_QUIZZES'))));
        }

        if ($type == '_find_winner') {
            breadcrumb_set_parents(array(array('_SELF:_SELF', do_lang_tempcode('MANAGE_QUIZZES')), array('_SELF:_SELF:find_winner', do_lang_tempcode('CHOOSE'))));
        }

        if ($type == 'quiz_results') {
            breadcrumb_set_self(do_lang_tempcode('CHOOSE'));
        }

        if ($type == '_quiz_results') {
            breadcrumb_set_parents(array(array('_SELF:_SELF', do_lang_tempcode('MANAGE_QUIZZES'))));
        }

        if ($type == '__quiz_results') {
            $id = get_param_integer('id'); // entry ID
            $rows = $GLOBALS['SITE_DB']->query_select('quiz_entries', array('*'), array('id' => $id), '', 1);
            if (!array_key_exists(0, $rows)) {
                warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
            }
            $row = $rows[0];

            breadcrumb_set_parents(array(array('_SELF:_SELF', do_lang_tempcode('MANAGE_QUIZZES')), array('_SELF:_SELF:_quiz_results:' . strval($row['q_quiz']), do_lang_tempcode('QUIZ_RESULTS'))));
            breadcrumb_set_self(do_lang_tempcode('RESULT'));

            $this->row = $row;
        }

        if ($type == 'export') {
            $this->title = get_screen_title('EXPORT_QUIZ');
        }

        if ($type == '_export') {
            $GLOBALS['OUTPUT_STREAMING'] = false;
        }

        if ($type == 'find_winner' || $type == '_find_winner') {
            $this->title = get_screen_title('FIND_WINNERS');
        }

        if ($type == 'quiz_results' || $type == '_quiz_results' || $type == '__quiz_results') {
            $this->title = get_screen_title('QUIZ_RESULTS');
        }

        if ($type == 'delete_quiz_results') {
            $this->title = get_screen_title('DELETE_QUIZ_RESULTS');
        }

        return null;
    }

    /**
     * Execute the module.
     *
     * @return Tempcode The result of execution.
     */
    public function run()
    {
        require_code('quiz');

        $type = get_param_string('type', 'browse');

        if ($type == 'browse') {
            return $this->browse();
        }
        if ($type == 'find_winner') {
            return $this->find_winner();
        }
        if ($type == '_find_winner') {
            return $this->_find_winner();
        }
        if ($type == 'quiz_results') {
            return $this->quiz_results();
        }
        if ($type == '_quiz_results') {
            return $this->_quiz_results();
        }
        if ($type == '__quiz_results') {
            return $this->__quiz_results();
        }
        if ($type == 'export') {
            return $this->export_quiz();
        }
        if ($type == '_export') {
            $this->_export_quiz(); // Does not return
        }
        if ($type == 'delete_quiz_results') {
            return $this->delete_quiz_results();
        }

        return new Tempcode();
    }

    /**
     * The do-next manager for before setup management.
     *
     * @return Tempcode The UI
     */
    public function browse()
    {
        require_lang('quiz');

        require_code('templates_donext');
        return do_next_manager(get_screen_title('MANAGE_QUIZZES'), comcode_lang_string('DOC_QUIZZES'),
            array(
                array('menu/cms/quiz/find_winners', array('_SELF', array('type' => 'find_winner'), '_SELF'), do_lang('FIND_WINNERS')),
                array('menu/cms/quiz/quiz_results', array('_SELF', array('type' => 'quiz_results'), '_SELF'), do_lang('QUIZ_RESULTS')),
                array('menu/_generic_admin/export', array('_SELF', array('type' => 'export'), '_SELF'), do_lang('EXPORT_QUIZ')),
            ),
            do_lang('MANAGE_QUIZZES')
        );
    }

    /**
     * Standard crud_module list function.
     *
     * @return Tempcode The selection list
     */
    public function create_selection_list_entries()
    {
        require_code('form_templates');

        $_m = $GLOBALS['SITE_DB']->query_select('quizzes', array('id', 'q_name'), null, 'ORDER BY q_add_date DESC', intval(get_option('general_safety_listing_limit')));
        $entries = new Tempcode();
        foreach ($_m as $m) {
            $entries->attach(form_input_list_entry(strval($m['id']), false, get_translated_text($m['q_name'])));
        }

        return $entries;
    }

    /**
     * Standard crud_module delete actualiser.
     *
     * @return Tempcode The UI
     */
    public function export_quiz()
    {
        $fields = new Tempcode();
        $quiz_list = $this->create_selection_list_entries();

        $fields->attach(form_input_list(do_lang_tempcode('QUIZ'), do_lang_tempcode('DESCRIPTION_QUIZZES_EXPORT'), 'quiz_id', $quiz_list));

        $post_url = build_url(array('page' => '_SELF', 'type' => '_export'), '_SELF');
        $submit_name = do_lang_tempcode('EXPORT_QUIZ');

        return do_template('FORM_SCREEN', array('_GUID' => '3110ee0e917e2e0f83a41ab27ec7eafe', 'TITLE' => $this->title, 'TEXT' => do_lang_tempcode('EXPORT_QUIZ_TEXT'), 'HIDDEN' => '', 'FIELDS' => $fields, 'SUBMIT_ICON' => 'menu___generic_admin__export', 'SUBMIT_NAME' => $submit_name, 'URL' => $post_url, 'POST' => true));
    }

    /**
     * Standard crud_module delete actualiser.
     */
    public function _export_quiz()
    {
        require_code('files2');
        $quiz_id = post_param_integer('quiz_id');
        $data = get_quiz_data_for_csv($quiz_id);
        make_csv($data, 'quiz.csv');
    }

    /**
     * UI: find quiz winner.
     *
     * @return Tempcode The result of execution.
     */
    public function find_winner()
    {
        require_code('form_templates');

        $_m = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), array('q_type' => 'COMPETITION'), 'ORDER BY q_validated DESC,q_add_date DESC', intval(get_option('general_safety_listing_limit')));
        $entries = new Tempcode();
        foreach ($_m as $m) {
            $entries->attach(form_input_list_entry(strval($m['id']), false, get_translated_text($m['q_name'])));
        }
        if ($entries->is_empty()) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        $fields = new Tempcode();
        $fields->attach(form_input_list(do_lang_tempcode('QUIZ'), '', 'id', $entries, null, true));

        $post_url = build_url(array('page' => '_SELF', 'type' => '_find_winner'), '_SELF');
        $submit_name = do_lang_tempcode('PROCEED');
        $text = do_lang_tempcode('CHOOSE_WINNERS');

        return do_template('FORM_SCREEN', array('_GUID' => '830097b15c232b10a8204cfed86082de', 'HIDDEN' => '', 'SKIP_WEBSTANDARDS' => true, 'TITLE' => $this->title, 'TEXT' => $text, 'URL' => $post_url, 'FIELDS' => $fields, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name));
    }

    /**
     * Actualiser: find quiz winner.
     *
     * @return Tempcode The result of execution.
     */
    public function _find_winner()
    {
        $id = post_param_integer('id');

        // Test to see if we have not yet chosen winners
        $winners = $GLOBALS['SITE_DB']->query_select('quiz_winner', array('q_entry'), array('q_quiz' => $id));
        if (!array_key_exists(0, $winners)) {
            // Close competition
            $close_time = $GLOBALS['SITE_DB']->query_select_value('quizzes', 'q_close_time', array('id' => $id));
            if ($close_time === null) {
                $GLOBALS['SITE_DB']->query_update('quizzes', array('q_close_time' => time()), array('id' => $id), '', 1);
            }

            // Choose all entries
            $entries = $GLOBALS['SITE_DB']->query('SELECT id,q_member,q_results FROM ' . get_table_prefix() . 'quiz_entries WHERE q_quiz=' . strval($id) . ' AND q_member<>' . strval($GLOBALS['FORUM_DRIVER']->get_guest_id()) . ' ORDER BY q_results DESC');

            // Choose the maximum number of rows we'll need who could potentially win
            $num_winners = $GLOBALS['SITE_DB']->query_select_value('quizzes', 'q_num_winners', array('id' => $id));
            if ($num_winners == 0) {
                $num_winners = 3; // Having 0 helps nobody, and having more than 0 if zero set hurts nobody
            }
            if ($num_winners < 0) {
                inform_exit(do_lang_tempcode('NO_ENTRIES'));
            }
            if ($num_winners >= count($entries)) {
                $min = 0;
            } else {
                $min = $entries[$num_winners]['q_results'];
            }
            $filtered_entries = array();
            foreach ($entries as $entry) {
                if ($entry['q_results'] >= $min) {
                    if (!array_key_exists($entry['q_results'], $filtered_entries)) {
                        $filtered_entries[$entry['q_results']] = array();
                    }

                    // Shuffle around this level
                    $temp = $filtered_entries[$entry['q_results']];
                    $temp[] = $entry;
                    shuffle($temp);
                    $filtered_entries[$entry['q_results']] = $temp;
                }
            }

            if (count($filtered_entries) == 0) {
                warn_exit(do_lang_tempcode('NO_POSSIBLE_WINNERS'));
            }

            // Pick winners: store
            for ($i = 0; $i < $num_winners; $i++) {
                $k = array_keys($filtered_entries);
                rsort($k);
                $temp = $filtered_entries[$k[0]];
                $_entry = array_shift($temp);
                if ($_entry !== null) {
                    $filtered_entries[$k[0]] = $temp;
                    $winners[] = array('q_entry' => $_entry['id']);

                    $GLOBALS['SITE_DB']->query_insert('quiz_winner', array(
                        'q_quiz' => $id,
                        'q_entry' => $_entry['id'],
                        'q_winner_level' => $i
                    ));
                } else {
                    break;
                }
            }
        }

        $_winners = new Tempcode();
        foreach ($winners as $i => $winner) {
            $member_id = $GLOBALS['SITE_DB']->query_select_value('quiz_entries', 'q_member', array('id' => $winner['q_entry']));
            $url = $GLOBALS['FORUM_DRIVER']->member_profile_url($member_id, true);
            switch ($i) {
                case 0:
                    $name = do_lang_tempcode('WINNER_FIRST', escape_html(integer_format($i + 1)), $GLOBALS['FORUM_DRIVER']->get_username($member_id));
                    break;
                case 1:
                    $name = do_lang_tempcode('WINNER_SECOND', escape_html(integer_format($i + 1)), $GLOBALS['FORUM_DRIVER']->get_username($member_id));
                    break;
                case 2:
                    $name = do_lang_tempcode('WINNER_THIRD', escape_html(integer_format($i + 1)), $GLOBALS['FORUM_DRIVER']->get_username($member_id));
                    break;
                default:
                    $name = do_lang_tempcode('WINNER', escape_html(integer_format($i + 1)), $GLOBALS['FORUM_DRIVER']->get_username($member_id));
                    break;
            }
            $_winners->attach(do_template('INDEX_SCREEN_ENTRY', array('_GUID' => '85f558c8dc99b027dbf4de821de0e419', 'URL' => $url, 'NAME' => $name, 'TARGET' => '_blank')));
        }

        // Show the winners
        return do_template('INDEX_SCREEN', array('_GUID' => 'd427ec7300a325ee4f00020ea59468e2', 'TITLE' => $this->title, 'CONTENT' => $_winners, 'PRE' => do_lang_tempcode('WINNERS_FOUND_AS_FOLLOWS'), 'POST' => do_lang_tempcode('WINNERS_HANDLING')));
    }

    /**
     * Choose quiz to view results of.
     *
     * @return Tempcode The result of execution.
     */
    public function quiz_results()
    {
        require_code('form_templates');

        $where = array();
        $type = get_param_string('q_type', null);
        if ($type !== null) {
            $where['q_type'] = $type;
        }

        $_m = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), $where, 'ORDER BY q_validated DESC,q_add_date DESC', intval(get_option('general_safety_listing_limit')));
        $entries = new Tempcode();
        foreach ($_m as $m) {
            $entries->attach(form_input_list_entry(strval($m['id']), false, get_translated_text($m['q_name']) . ' (' . do_lang($m['q_type']) . ')'));
        }
        if ($entries->is_empty()) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        $fields = new Tempcode();
        $fields->attach(form_input_list(do_lang_tempcode('QUIZ'), '', 'id', $entries, null, true));

        $post_url = build_url(array('page' => '_SELF', 'type' => '_quiz_results'), '_SELF', null, false, true);
        $submit_name = do_lang_tempcode('QUIZ_RESULTS');

        return do_template('FORM_SCREEN', array('_GUID' => '03f611727000c1cb1c40780773bb8ebd', 'SKIP_WEBSTANDARDS' => true, 'HIDDEN' => '', 'GET' => true, 'TITLE' => $this->title, 'TEXT' => '', 'URL' => $post_url, 'FIELDS' => $fields, 'SUBMIT_ICON' => 'buttons__proceed', 'SUBMIT_NAME' => $submit_name));
    }

    /**
     * View quiz results.
     *
     * @return Tempcode The result of execution.
     */
    public function _quiz_results()
    {
        $id = get_param_integer('id', null); // quiz ID

        $fields = new Tempcode();

        require_code('templates_results_table');
        require_code('templates_map_table');

        // Show summary
        if ($id !== null) {
            $question_rows = $GLOBALS['SITE_DB']->query_select('quiz_questions', array('*'), array('q_quiz' => $id), 'ORDER BY id');
            foreach ($question_rows as $q) {
                $question = get_translated_text($q['q_question_text']);

                $answers = new Tempcode();
                $answer_rows = $GLOBALS['SITE_DB']->query_select('quiz_question_answers', array('*'), array('q_question' => $q['id']), 'ORDER BY id');
                $all_answers = array();
                foreach ($answer_rows as $i => $a) {
                    $answer = get_translated_text($a['q_answer_text']);
                    $count = $GLOBALS['SITE_DB']->query_select_value('quiz_entry_answer', 'COUNT(*)', array('q_answer' => strval($a['id'])));

                    $all_answers[serialize(array($answer, $i))] = $count;
                }
                arsort($all_answers);
                foreach ($all_answers as $bits => $count) {
                    list($answer, $i) = unserialize($bits);

                    $answers->attach(paragraph(do_lang_tempcode('QUIZ_ANSWER_RESULT', escape_html($answer), escape_html(integer_format($count)), escape_html(integer_format($i + 1)))));
                }
                if ($answers->is_empty()) {
                    $answers = do_lang_tempcode('FREE_ENTRY_ANSWER');
                }

                $fields->attach(map_table_field($question, $answers, true));
            }
            $summary = do_template('MAP_TABLE', array('_GUID' => '2b0c2ba0070ba810c5e4b5b4aedcb15f', 'WIDTH' => '300', 'FIELDS' => $fields));
        } else {
            $summary = new Tempcode();
        }

        // Show results table
        $start = get_param_integer('start', 0);
        $max = get_param_integer('max', 50);
        $sortables = array('q_time' => do_lang_tempcode('DATE'));
        $test = explode(' ', get_param_string('sort', 'q_time DESC'), 2);
        if (count($test) == 1) {
            $test[1] = 'DESC';
        }
        list($sortable, $sort_order) = $test;
        if (((strtoupper($sort_order) != 'ASC') && (strtoupper($sort_order) != 'DESC')) || (!array_key_exists($sortable, $sortables))) {
            log_hack_attack_and_exit('ORDERBY_HACK');
        }
        $where = array();
        if ($id !== null) {
            $where['q_quiz'] = $id;
        }
        $member_id = get_param_integer('member_id', null);
        if ($member_id !== null) {
            $where['q_member'] = $member_id;
        }
        $max_rows = $GLOBALS['SITE_DB']->query_select_value('quiz_entries', 'COUNT(*)', $where);
        $rows = $GLOBALS['SITE_DB']->query_select('quiz_entries e JOIN ' . get_table_prefix() . 'quizzes q ON q.id=e.q_quiz', array('e.id AS e_id', 'e.q_time', 'e.q_member', 'e.q_results', 'q.*'), $where, 'ORDER BY ' . $sortable . ' ' . $sort_order, $max, $start);
        if (count($rows) == 0) {
            return inform_screen($this->title, do_lang_tempcode('NO_ENTRIES'));
        }
        $fields = new Tempcode();
        $_fields_title = array();
        $_fields_title[] = do_lang_tempcode('DATE');
        if ($id === null) {
            $_fields_title[] = do_lang_tempcode('NAME');
            $_fields_title[] = do_lang_tempcode('TYPE');
        } else {
            $_fields_title[] = do_lang_tempcode('USERNAME');
        }
        $_fields_title[] = do_lang_tempcode('MARKS');
        $fields_title = results_field_title($_fields_title, $sortables, 'sort', $sortable . ' ' . $sort_order);
        foreach ($rows as $myrow) {
            $results_entry = array();

            $date_link = hyperlink(build_url(array('page' => '_SELF', 'type' => '__quiz_results', 'id' => $myrow['e_id']), '_SELF'), get_timezoned_date_time($myrow['q_time']), false, true);
            $results_entry[] = $date_link;

            if ($id === null) {
                $results_entry[] = get_translated_text($myrow['q_name']);
                $results_entry[] = do_lang_tempcode($myrow['q_type']);
            } else {
                $member_link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($myrow['q_member'], '', false);
                $results_entry[] = $member_link;
            }
            $results_entry[] = ($myrow['q_type'] == 'SURVEY') ? '' : integer_format($myrow['q_results']);

            $fields->attach(results_entry($results_entry, true));
        }
        if ($fields->is_empty()) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }
        $results = results_table(do_lang_tempcode('QUIZ_RESULTS'), $start, 'start', $max, 'max', $max_rows, $fields_title, $fields, $sortables, $sortable, $sort_order, 'sort');

        $tpl = do_template('QUIZ_RESULTS_SCREEN', array(
            '_GUID' => '3f38ac1b94fb4de8219b8f7108c7b0a3',
            'TITLE' => $this->title,
            'SUMMARY' => $summary,
            'RESULTS' => $results,
        ));

        require_code('templates_internalise_screen');
        return internalise_own_screen($tpl);
    }

    /**
     * View a single filled-in quiz.
     *
     * @return Tempcode The result of execution.
     */
    public function __quiz_results()
    {
        $id = get_param_integer('id'); // entry ID

        $row = $this->row;

        $quizzes = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), array('id' => $row['q_quiz']), '', 1);
        if (!array_key_exists(0, $quizzes)) {
            warn_exit(do_lang_tempcode('MISSING_RESOURCE', 'quiz'));
        }
        $quiz = $quizzes[0];

        $member_id = $row['q_member'];
        $username = $GLOBALS['FORUM_DRIVER']->get_username($member_id);
        if ($username === null) {
            $username = do_lang('UNKNOWN');
        }
        $member_url = mixed();
        $member_url = get_base_url();
        if (!is_guest($member_id)) {
            $member_url = $GLOBALS['FORUM_DRIVER']->member_profile_url($member_id, true);
            if (is_object($member_url)) {
                $member_url = $member_url->evaluate();
            }
        }

        $date = get_timezoned_date_time($row['q_time']);

        list(
            $marks,
            $potential_extra_marks,
            $out_of,
            $given_answers,
            ,
            ,
            ,
            ,
            ,
            $marks_range,
            $percentage_range,
            ,
            ,
            ,
            ,
            ,
            $passed,
            ) = score_quiz($id, null, null, null, true);

        return do_template('QUIZ_RESULT_SCREEN', array(
            '_GUID' => 'f59cbda2bb6b6f0ad6fa149591d94c90',
            'TITLE' => $this->title,
            'USERNAME' => $username,
            'MEMBER_URL' => $member_url,
            'DATE' => $date,
            '_DATE' => strval($row['q_time']),
            'ENTRY_ID' => strval($id),
            'QUIZ_NAME' => get_translated_text($quiz['q_name']),
            'GIVEN_ANSWERS_ARR' => $given_answers,
            'PASSED' => $passed,
            'TYPE' => do_lang($quiz['q_type']),
            '_TYPE' => $quiz['q_type'],
            'MARKS' => strval($marks),
            'POTENTIAL_EXTRA_MARKS' => strval($potential_extra_marks),
            'OUT_OF' => strval($out_of),
            'MARKS_RANGE' => $marks_range,
            'PERCENTAGE_RANGE' => $percentage_range,
        ));
    }

    /**
     * Delete some quiz results.
     *
     * @return Tempcode The result of execution.
     */
    public function delete_quiz_results()
    {
        $to_delete = array();

        foreach (array_keys($_POST) as $key) {
            $matches = array();
            if (preg_match('#^delete_(\d+)$#', $key, $matches) != 0) {
                if (post_param_integer($key) == 1) {
                    $to_delete[] = intval($matches[1]);
                }
            }
        }

        if (count($to_delete) == 0) {
            warn_exit(do_lang_tempcode('NOTHING_SELECTED'));
        }

        foreach ($to_delete as $result_id) {
            $entry_rows = $GLOBALS['SITE_DB']->query_select('quiz_entries', array('q_quiz', 'q_member'), array('id' => $result_id), '', 1);
            if (isset($entry_rows[0])) {
                $to_delete_sub = collapse_1d_complexity('id', $GLOBALS['SITE_DB']->query_select('quiz_entries', array('id'), $entry_rows[0]));
                foreach ($to_delete_sub as $_result_id) {
                    $GLOBALS['SITE_DB']->query_delete('quiz_entries', array('id' => $_result_id), '', 1);
                    $GLOBALS['SITE_DB']->query_delete('quiz_entry_answer', array('q_entry' => $_result_id));
                }
            }
        }

        log_it('DELETE_QUIZ_RESULTS');

        return inform_screen($this->title, do_lang_tempcode('SUCCESS'));
    }
}
