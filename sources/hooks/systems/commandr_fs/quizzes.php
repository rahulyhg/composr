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

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_quizzes extends Resource_fs_base
{
    public $file_resource_type = 'quiz';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        return $GLOBALS['SITE_DB']->query_select_value('quizzes', 'COUNT(*)');
    }

    /**
     * Standard Commandr-fs function for searching for a resource by label.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  LONG_TEXT $label The resource label
     * @return array A list of resource IDs
     */
    public function find_resource_by_label($resource_type, $label)
    {
        $_ret = $GLOBALS['SITE_DB']->query_select('quizzes', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('q_name') => $label), 'ORDER BY id');
        $ret = array();
        foreach ($_ret as $r) {
            $ret[] = strval($r['id']);
        }
        return $ret;
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_file_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_QUIZ') . ' OR ' . db_string_equal_to('the_type', 'EDIT_QUIZ') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
    }

    /**
     * Standard Commandr-fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_add($filename, $path, $properties)
    {
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        require_code('quiz2');

        $timeout = $this->_default_property_int($properties, 'timeout');
        $start_text = $this->_default_property_str($properties, 'start_text');
        $end_text = $this->_default_property_str($properties, 'end_text');
        $end_text_fail = $this->_default_property_str($properties, 'end_text_fail');
        $notes = $this->_default_property_str($properties, 'notes');
        $percentage = $this->_default_property_int($properties, 'percentage');
        $open_time = $this->_default_property_time_null($properties, 'open_time');
        if ($open_time === null) {
            $open_time = time();
        }
        $close_time = $this->_default_property_time_null($properties, 'close_time');
        $num_winners = $this->_default_property_int($properties, 'num_winners');
        $redo_time = $this->_default_property_int($properties, 'redo_time');
        $type = $this->_default_property_str($properties, 'type');
        if ($type == '') {
            $type = 'SURVEY';
        }
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $text = $this->_default_property_str($properties, 'text');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $points_for_passing = $this->_default_property_int($properties, 'points_for_passing');
        $tied_newsletter = $this->_default_property_int_null($properties, 'tied_newsletter');
        $reveal_answers = $this->_default_property_int($properties, 'reveal_answers');
        $shuffle_questions = $this->_default_property_int($properties, 'shuffle_questions');
        $shuffle_answers = $this->_default_property_int($properties, 'shuffle_answers');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');

        $id = add_quiz($label, $timeout, $start_text, $end_text, $end_text_fail, $notes, $percentage, $open_time, $close_time, $num_winners, $redo_time, $type, $validated, $text, $submitter, $points_for_passing, $tied_newsletter, $reveal_answers, $shuffle_questions, $shuffle_answers, $add_time, $meta_keywords, $meta_description);

        if (isset($properties['winners'])) {
            table_from_portable_rows('quiz_winner', $properties['winners'], array('q_entry' => $id), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
        }

        $this->add_quiz_entries($properties, $id);

        $this->_resource_save_extend($this->file_resource_type, strval($id), $filename, $label, $properties);

        return strval($id);
    }

    /**
     * Custom import code for quiz entries.
     *
     * @param  array $properties The properties
     * @param  AUTO_LINK $quiz_id The quiz
     */
    private function add_quiz_entries($properties, $quiz_id)
    {
        if (isset($properties['entries'])) {
            $GLOBALS['SITE_DB']->query_delete('quiz_entries', array('q_quiz' => $quiz_id));
            foreach ($properties['entries'] as $entry) {
                $answers = $entry['answers'];
                unset($entry['answers']);
                $entry_id = $GLOBALS['SITE_DB']->query_insert('quiz_entries', $entry + array('q_quiz' => $quiz_id), true);
                foreach ($answers as $answer) {
                    $GLOBALS['SITE_DB']->query_insert('quiz_entry_answer', $answer + array('q_entry' => $entry_id));
                }
            }
        }
    }

    /**
     * Standard Commandr-fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function file_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('quizzes', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        require_code('quiz2');
        $text = load_quiz_questions_to_string(intval($resource_id));

        list($meta_keywords, $meta_description) = seo_meta_get_for('quiz', strval($row['id']));

        $entries = table_to_portable_rows('quiz_entries', /*skip*/array(), array('q_quiz' => intval($resource_id)));
        foreach ($entries as &$entry) {
            $entry['answers'] = table_to_portable_rows('quiz_entry_answer', /*skip*/array('id'), array('q_entry' => $entry['id']));
            unset($entry['id']);
        }

        $properties = array(
            'label' => get_translated_text($row['q_name']),
            'timeout' => $row['q_timeout'],
            'start_text' => $row['q_start_text'],
            'end_text' => $row['q_end_text'],
            'end_text_fail' => $row['q_end_text_fail'],
            'notes' => $row['q_notes'],
            'percentage' => $row['q_percentage'],
            'open_time' => remap_time_as_portable($row['q_open_time']),
            'close_time' => remap_time_as_portable($row['q_close_time']),
            'num_winners' => $row['q_num_winners'],
            'redo_time' => $row['q_redo_time'],
            'type' => $row['q_type'],
            'validated' => $row['q_validated'],
            'text' => $text,
            'submitter' => remap_resource_id_as_portable('member', $row['q_submitter']),
            'points_for_passing' => $row['q_points_for_passing'],
            'tied_newsletter' => $row['q_tied_newsletter'],
            'reveal_answers' => $row['q_reveal_answers'],
            'shuffle_questions' => $row['q_shuffle_questions'],
            'shuffle_answers' => $row['q_shuffle_answers'],
            'add_date' => remap_time_as_portable($row['q_add_date']),
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'winners' => table_to_portable_rows('quiz_winner', /*skip*/array(), array('q_entry' => intval($resource_id))),
            'entries' => $entries,
        );
        $this->_resource_load_extend($resource_type, $resource_id, $properties, $filename, $path);
        return $properties;
    }

    /**
     * Standard Commandr-fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        require_code('quiz2');

        $label = $this->_default_property_str($properties, 'label');
        $timeout = $this->_default_property_int($properties, 'timeout');
        $start_text = $this->_default_property_str($properties, 'start_text');
        $end_text = $this->_default_property_str($properties, 'end_text');
        $end_text_fail = $this->_default_property_str($properties, 'end_text_fail');
        $notes = $this->_default_property_str($properties, 'notes');
        $percentage = $this->_default_property_int($properties, 'percentage');
        $open_time = $this->_default_property_time_null($properties, 'open_time');
        if ($open_time === null) {
            $open_time = time();
        }
        $close_time = $this->_default_property_time_null($properties, 'close_time');
        $num_winners = $this->_default_property_int($properties, 'num_winners');
        $redo_time = $this->_default_property_int($properties, 'redo_time');
        $type = $this->_default_property_str($properties, 'type');
        if ($type == '') {
            $type = 'SURVEY';
        }
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $text = $this->_default_property_str($properties, 'text');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $points_for_passing = $this->_default_property_int($properties, 'points_for_passing');
        $tied_newsletter = $this->_default_property_int_null($properties, 'tied_newsletter');
        $reveal_answers = $this->_default_property_int($properties, 'reveal_answers');
        $shuffle_questions = $this->_default_property_int($properties, 'shuffle_questions');
        $shuffle_answers = $this->_default_property_int($properties, 'shuffle_answers');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');

        edit_quiz(intval($resource_id), $label, $timeout, $start_text, $end_text, $end_text_fail, $notes, $percentage, $open_time, $close_time, $num_winners, $redo_time, $type, $validated, $text, $meta_keywords, $meta_description, $points_for_passing, $tied_newsletter, $reveal_answers, $shuffle_questions, $shuffle_answers, $add_time, $submitter, true);

        if (isset($properties['winners'])) {
            table_from_portable_rows('quiz_winner', $properties['winners'], array('q_entry' => intval($resource_id)), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
        }

        $this->add_quiz_entries($properties, intval($resource_id));

        $this->_resource_save_extend($this->file_resource_type, $resource_id, $filename, $label, $properties);

        return $resource_id;
    }

    /**
     * Standard Commandr-fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function file_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        require_code('quiz2');
        delete_quiz(intval($resource_id));

        return true;
    }
}
