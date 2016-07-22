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
 * @package    cns_forum
 */

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_forums extends Resource_fs_base
{
    public $folder_resource_type = array('forum', 'topic');
    public $file_resource_type = 'post';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        switch ($resource_type) {
            case 'post':
                return $GLOBALS['FORUM_DB']->query_select_value('f_posts', 'COUNT(*)');

            case 'topic':
                return $GLOBALS['FORUM_DB']->query_select_value('f_topics', 'COUNT(*)');

            case 'forum':
                return $GLOBALS['FORUM_DB']->query_select_value('f_forums', 'COUNT(*)');
        }
        return 0;
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
        switch ($resource_type) {
            case 'post':
                $_ret = $GLOBALS['FORUM_DB']->query_select('f_posts', array('id'), array('p_title' => $label), 'ORDER BY id');
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;

            case 'topic':
                $_ret = $GLOBALS['FORUM_DB']->query_select('f_topics', array('id'), array('t_cache_first_title' => $label), 'ORDER BY id');
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;

            case 'forum':
                $_ret = $GLOBALS['FORUM_DB']->query_select('f_forums', array('id'), array('f_name' => $label), 'ORDER BY id');
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;
        }
        return array();
    }

    /**
     * Whether the filesystem hook is active.
     *
     * @return boolean Whether it is
     */
    protected function _is_active()
    {
        return (get_forum_type() == 'cns') && (!is_cns_satellite_site());
    }

    /**
     * Find whether a kind of resource handled by this hook (folder or file) can be under a particular kind of folder.
     *
     * @param  ?ID_TEXT $above Folder resource type (null: root)
     * @param  ID_TEXT $under Resource type (may be file or folder)
     * @return ?array A map: The parent referencing field, the table it is in, and the ID field of that table (null: cannot be under)
     */
    protected function _has_parent_child_relationship($above, $under)
    {
        if ($above === null) {
            $above = '';
        }
        switch ($above) {
            case '':
            case 'forum':
                if (($under == 'topic') && (empty($above))) {
                    return null;
                }

                if ($under == 'topic') {
                    return array(
                        'cat_field' => 't_forum_id',
                        'linker_table' => null,
                        'id_field' => 'id',
                        'id_field_linker' => 'id',
                        'cat_field_numeric' => true,
                    );
                }

                if ($under == 'forum') {
                    return array(
                        'cat_field' => 'f_parent_forum',
                        'linker_table' => 'f_forums',
                        'id_field' => 'id',
                        'id_field_linker' => 'id',
                        'cat_field_numeric' => true,
                    );
                }
                break;

            case 'topic':
                if ($under == 'post') {
                    return array(
                        'cat_field' => 'p_topic_id',
                        'linker_table' => 'f_posts',
                        'id_field' => 'id',
                        'id_field_linker' => 'id',
                        'cat_field_numeric' => true,
                    );
                }
                break;
        }
        return null;
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @param  ID_TEXT $category Parent category (blank: root / not applicable)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_folder_edit_date($row, $category)
    {
        if (substr($category, 0, 6) == 'FORUM-') {
            $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_FORUM') . ' OR ' . db_string_equal_to('the_type', 'EDIT_FORUM') . ')';
            return $GLOBALS['SITE_DB']->query_value_if_there($query);
        }

        return null; // Will be picked up naturally from t_cache_first_time/t_cache_last_time
    }

    /**
     * Get the filename for a resource ID. Note that filenames are unique across all folders in a filesystem.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  ID_TEXT $resource_id The resource ID
     * @return ?ID_TEXT The filename (null: could not find)
     */
    public function folder_convert_id_to_filename($resource_type, $resource_id)
    {
        if ($resource_type == 'forum') {
            $f = parent::folder_convert_id_to_filename('forum', $resource_id);
            if ($f === null) {
                return null;
            }
            return 'FORUM-' . $f;
        }

        return parent::folder_convert_id_to_filename('topic', $resource_id);
    }

    /**
     * Get the resource ID for a filename. Note that filenames are unique across all folders in a filesystem.
     *
     * @param  ID_TEXT $filename The filename, or filepath
     * @param  ?ID_TEXT $resource_type The resource type (null: assumption of only one folder resource type for this hook; only passed as non-null from overridden functions within hooks that are calling this as a helper function)
     * @return array A pair: The resource type, the resource ID
     */
    public function folder_convert_filename_to_id($filename, $resource_type = null)
    {
        $filename = preg_replace('#^.*/#', '', $filename); // Paths not needed, as filenames are globally unique; paths would not be in alternative_ids table

        if (substr($filename, 0, 6) == 'FORUM-') { // Must be defined first, to ensure prefix stripped
            return parent::folder_convert_filename_to_id(substr($filename, 6), 'forum');
        }

        if ($resource_type !== null) {
            return parent::folder_convert_filename_to_id($filename, $resource_type);
        }

        return parent::folder_convert_filename_to_id($filename, 'topic');
    }

    /**
     * Convert properties to variables for adding/editing forums.
     *
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return array Properties
     */
    protected function __folder_read_in_properties_forum($path, $properties)
    {
        $description = $this->_default_property_str($properties, 'description');
        $forum_grouping_id = $this->_default_property_resource_id_null('forum_grouping', $properties, 'forum_grouping_id');
        if ($forum_grouping_id === null) {
            $forum_grouping_id = $GLOBALS['FORUM_DB']->query_select_value('f_forum_groupings', 'MIN(id)');
        }
        $access_mapping = array();
        $position = $this->_default_property_int_null($properties, 'position');
        if ($position === null) {
            $position = 1;
        }
        $post_count_increment = $this->_default_property_int_null($properties, 'post_count_increment');
        if ($post_count_increment === null) {
            $post_count_increment = 1;
        }
        $order_sub_alpha = $this->_default_property_int($properties, 'order_sub_alpha');
        $intro_question = $this->_default_property_str($properties, 'intro_question');
        $intro_answer = $this->_default_property_str($properties, 'intro_answer');
        $redirection = $this->_default_property_str($properties, 'redirection');
        $order = $this->_default_property_str($properties, 'order');
        if ($order == '') {
            $order = 'last_post';
        }
        $is_threaded = $this->_default_property_int($properties, 'is_threaded');

        return array($description, $forum_grouping_id, $access_mapping, $position, $post_count_increment, $order_sub_alpha, $intro_question, $intro_answer, $redirection, $order, $is_threaded);
    }

    /**
     * Convert properties to variables for adding/editing topics.
     *
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return array Properties
     */
    protected function __folder_read_in_properties_topic($path, $properties)
    {
        $description = $this->_default_property_str($properties, 'description');
        $emoticon = $this->_default_property_str($properties, 'emoticon');
        $validated = $this->_default_property_int($properties, 'validated');
        $open = $this->_default_property_int($properties, 'open');
        $pinned = $this->_default_property_int($properties, 'pinned');
        $cascading = $this->_default_property_int($properties, 'cascading');
        $pt_from = $this->_default_property_member_null($properties, 'pt_from');
        $pt_to = $this->_default_property_member_null($properties, 'pt_to');
        $num_views = $this->_default_property_int($properties, 'views');
        $description_link = $this->_default_property_str($properties, 'description_link');

        return array($description, $emoticon, $validated, $open, $pinned, $cascading, $pt_from, $pt_to, $num_views, $description_link);
    }

    /**
     * Standard Commandr-fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @param  ?ID_TEXT $force_type Resource type to try to force (null: do not force)
     * @return ~ID_TEXT The resource ID (false: error)
     */
    public function folder_add($filename, $path, $properties, $force_type = null)
    {
        if ((($path == '') || (substr($filename, 0, 6) == 'FORUM-') || ($force_type === 'forum')) && ($force_type !== 'topic')) {
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'forum');
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');

            if ($category_resource_type != 'forum') {
                return false;
            }
            if ($category == '') {
                $category = strval(db_get_first_id());
            }/*return false;*/ // Can't create more than one root

            require_code('cns_forums_action');

            list($description, $forum_grouping_id, $access_mapping, $position, $post_count_increment, $order_sub_alpha, $intro_question, $intro_answer, $redirection, $order, $is_threaded) = $this->__folder_read_in_properties_forum($path, $properties);

            $parent_forum = $this->_integer_category($category);

            $id = cns_make_forum($label, $description, $forum_grouping_id, $access_mapping, $parent_forum, $position, $post_count_increment, $order_sub_alpha, $intro_question, $intro_answer, $redirection, $order, $is_threaded);

            $this->_resource_save_extend('forum', strval($id), $filename, $label, $properties);
        } else {
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'topic');
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');

            if ($category_resource_type != 'forum') {
                return false;
            }
            if ($category == '') {
                return false;
            }

            require_code('cns_topics_action');

            $forum_id = $this->_integer_category($category);

            list($description, $emoticon, $validated, $open, $pinned, $cascading, $pt_from, $pt_to, $num_views, $description_link) = $this->__folder_read_in_properties_topic($path, $properties);

            $id = cns_make_topic($forum_id, $description, $emoticon, $validated, $open, $pinned, $cascading, $pt_from, $pt_to, false, $num_views, null, $description_link);
            $GLOBALS['FORUM_DB']->query_update('f_topics', array('t_cache_first_title' => $label), array('id' => $id), '', 1);
            generate_resource_fs_moniker('topic', strval($id));
            if ((array_key_exists('poll', $properties)) && (!empty($properties['poll']))) {
                require_code('cns_polls_action');

                $poll_data = $properties['poll'];

                $question = $poll_data['question'];
                $is_private = $poll_data['is_private'];
                $is_open = $poll_data['is_open'];
                $minimum_selections = $poll_data['minimum_selections'];
                $maximum_selections = $poll_data['maximum_selections'];
                $requires_reply = $poll_data['requires_reply'];
                $answers = $poll_data['answers']; // A list of pairs of the potential voteable answers and the cached number of votes.

                $poll_id = cns_make_poll($id, $question, $is_private, $is_open, $minimum_selections, $maximum_selections, $requires_reply, $answers, false);

                $votes = $poll_data['votes'];
                table_from_portable_rows('f_poll_votes', $properties['votes'], array('pv_poll_id' => $poll_id), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
            }

            if (isset($properties['special_pt_access'])) {
                table_from_portable_rows('f_special_pt_access', $properties['special_pt_access'], array('s_topic_id' => $id), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
            }

            $this->save_ticket_associations($properties, $id);

            $this->_resource_save_extend('topic', strval($id), $filename, $label, $properties);
        }

        return strval($id);
    }

    /**
     * Save ticket associations.
     *
     * @param  array $properties Properties
     * @param  AUTO_LINK $topic_id The topic ID
     */
    private function save_ticket_associations($properties, $topic_id)
    {
        if (addon_installed('tickets')) {
            if (isset($properties['ticket_associations'])) {
                $GLOBALS['SITE_DB']->query_delete('tickets', array('topic_id' => $topic_id));
                foreach ($properties['ticket_associations'] as $ticket_association) {
                    $extra_access = $ticket_association['extra_access'];
                    unset($ticket_association['extra_access']);

                    $GLOBALS['SITE_DB']->query_delete('ticket_extra_access', array('ticket_id' => $ticket_association['ticket_id']));

                    foreach ($extra_access as $_extra_access) {
                        $GLOBALS['SITE_DB']->query_insert('ticket_extra_access', $_extra_access + array('ticket_id' => $ticket_association['ticket_id']));
                    }

                    $GLOBALS['SITE_DB']->query_insert('tickets', $ticket_association + array('topic_id' => $topic_id));
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
    public function folder_load($filename, $path)
    {
        if (substr($filename, 0, 6) == 'FORUM-') {
            list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename, 'forum');
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');

            $rows = $GLOBALS['FORUM_DB']->query_select('f_forums', array('*'), array('id' => intval($resource_id)), '', 1);
            if (!array_key_exists(0, $rows)) {
                return false;
            }
            $row = $rows[0];

            $properties = array(
                'label' => $row['f_name'],
                'description' => get_translated_text($row['f_description']),
                'forum_grouping_id' => remap_resource_id_as_portable('forum_grouping', $row['f_forum_grouping_id']),
                'position' => $row['f_position'],
                'post_count_increment' => $row['f_post_count_increment'],
                'order_sub_alpha' => $row['f_order_sub_alpha'],
                'intro_question' => get_translated_text($row['f_intro_question']),
                'intro_answer' => $row['f_intro_answer'],
                'redirection' => $row['f_redirection'],
                'order' => $row['f_order'],
                'is_threaded' => $row['f_is_threaded'],
            );
            $this->_resource_load_extend($resource_type, $resource_id, $properties, $filename, $path);
            return $properties;
        }

        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename, 'topic');
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');

        $rows = $GLOBALS['FORUM_DB']->query_select('f_topics', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $rows = $GLOBALS['FORUM_DB']->query_select('f_polls', array('*'), array('id' => intval($row['t_poll_id'])), '', 1);
        if (array_key_exists(0, $rows)) {
            $answers = $GLOBALS['FORUM_DB']->query_select('f_poll_answers', array('pa_answer', 'pa_cache_num_votes'), array('pa_poll_id' => $row['t_poll_id']));
            $_answers = array();
            foreach ($answers as $a) {
                $_answers[] = array($a['pa_answer'], $a['pa_cache_num_votes']);
            }
            $poll_data = array(
                'question' => $rows[0]['po_question'],
                'is_private' => $rows[0]['po_is_private'],
                'is_open' => $rows[0]['po_is_open'],
                'minimum_selections' => $rows[0]['po_minimum_selections'],
                'maximum_selections' => $rows[0]['po_maximum_selections'],
                'requires_reply' => $rows[0]['po_requires_reply'],
                'answers' => $_answers,
                'votes' => table_to_portable_rows('f_poll_votes', /*skip*/array('id'), array('pv_poll_id' => $row['t_poll_id'])),
            );
        } else {
            $poll_data = null;
        }

        $properties = array(
            'label' => $row['t_cache_first_title'],
            'description' => $row['t_description'],
            'emoticon' => $row['t_emoticon'],
            'validated' => $row['t_validated'],
            'open' => $row['t_is_open'],
            'pinned' => $row['t_pinned'],
            'cascading' => $row['t_cascading'],
            'pt_from' => remap_resource_id_as_portable('member', $row['t_pt_from']),
            'pt_to' => remap_resource_id_as_portable('member', $row['t_pt_to']),
            'views' => $row['t_num_views'],
            'description_link' => $row['t_description_link'],
            'poll' => $poll_data,
        );
        $this->_resource_load_extend($resource_type, $resource_id, $properties, $filename, $path);

        if ($row['t_forum_id'] !== null) {
            $properties['special_pt_access'] = table_to_portable_rows('f_special_pt_access', /*skip*/array(), array('s_topic_id' => intval($resource_id)));
        }

        if (addon_installed('tickets')) {
            $ticket_associations = table_to_portable_rows('tickets', /*skip*/array(), array('topic_id' => intval($resource_id)));
            foreach ($ticket_associations as &$ticket_association) {
                $ticket_association['extra_access'] = table_to_portable_rows('ticket_extra_access', /*skip*/array(), array('ticket_id' => $ticket_association['ticket_id']));
            }
            $properties['ticket_associations'] = $ticket_associations;
        }

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
    public function folder_edit($filename, $path, $properties)
    {
        if (substr($filename, 0, 6) == 'FORUM-') {
            list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename, 'forum');
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'forum');

            require_code('cns_forums_action2');

            $label = $this->_default_property_str($properties, 'label');
            list($description, $forum_grouping_id, $access_mapping, $position, $post_count_increment, $order_sub_alpha, $intro_question, $intro_answer, $redirection, $order, $is_threaded) = $this->__folder_read_in_properties_forum($path, $properties);

            $parent_forum = $this->_integer_category($category);

            cns_edit_forum(intval($resource_id), $label, $description, $forum_grouping_id, $parent_forum, $position, $post_count_increment, $order_sub_alpha, $intro_question, $intro_answer, $redirection, $order, $is_threaded);

            $this->_resource_save_extend('forum', $resource_id, $filename, $label, $properties);
        } else {
            list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename, 'topic');
            list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'forum');
            list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, 'topic');

            require_code('cns_topics_action2');

            if ($category == '') {
                return false;
            }

            $label = $this->_default_property_str($properties, 'label');
            list($description, $emoticon, $validated, $open, $pinned, $cascading, $pt_from, $pt_to, $num_views, $description_link) = $this->__folder_read_in_properties_topic($path, $properties);

            cns_edit_topic(intval($resource_id), $description, $emoticon, $validated, $open, $pinned, $cascading, '', $label, $description_link, false, $num_views, true);

            $poll_id = $GLOBALS['FORUM_DB']->query_select_value('f_topics', 't_poll_id', array('id' => intval($resource_id)));

            if ((array_key_exists('poll', $properties)) && (!empty($properties['poll']))) {
                $poll_data = $properties['poll'];

                $question = $poll_data['question'];
                $is_private = $poll_data['is_private'];
                $is_open = $poll_data['is_open'];
                $minimum_selections = $poll_data['minimum_selections'];
                $maximum_selections = $poll_data['maximum_selections'];
                $requires_reply = $poll_data['requires_reply'];
                $answers = $poll_data['answers']; // A list of pairs of the potential voteable answers and the number of votes.

                if ($poll_id === null) {
                    require_code('cns_polls_action');
                    $poll_id = cns_make_poll(intval($resource_id), $question, $is_private, $is_open, $minimum_selections, $maximum_selections, $requires_reply, $answers, false);
                } else {
                    require_code('cns_polls_action2');
                    cns_edit_poll($poll_id, $question, $is_private, $is_open, $minimum_selections, $maximum_selections, $requires_reply, $answers);
                }

                $votes = $poll_data['votes'];
                table_from_portable_rows('f_poll_votes', $properties['votes'], array('pv_poll_id' => $poll_id), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
            } else {
                if ($poll_id !== null) {
                    require_code('cns_polls_action2');
                    cns_delete_poll($poll_id);
                }
            }

            if (isset($properties['special_pt_access'])) {
                table_from_portable_rows('f_special_pt_access', $properties['special_pt_access'], array('s_topic_id' => intval($resource_id)), TABLE_REPLACE_MODE_BY_EXTRA_FIELD_DATA);
            }

            $this->save_ticket_associations($properties, intval($resource_id));

            $this->_resource_save_extend('topic', $resource_id, $filename, $label, $properties);
        }

        return $resource_id;
    }

    /**
     * Standard Commandr-fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function folder_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        if ($resource_type == 'forum') {
            require_code('cns_forums_action2');
            cns_delete_forum(intval($resource_id), null, 1);
        } else {
            require_code('cns_topics_action2');
            cns_delete_topic(intval($resource_id), '', null, false);
        }

        return true;
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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'topic');
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category == '') {
            return false;
        }
        if ($category_resource_type != 'topic') {
            return false;
        }

        require_code('cns_posts_action');

        $topic_id = $this->_integer_category($category);
        $post = $this->_default_property_str($properties, 'post');
        $skip_sig = $this->_default_property_int($properties, 'skip_sig');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $is_emphasised = $this->_default_property_int($properties, 'is_emphasised');
        $poster_name_if_guest = $this->_default_property_str($properties, 'poster_name_if_guest');
        $ip_address = $this->_default_property_str_null($properties, 'ip_address');
        $time = $this->_default_property_time($properties, 'add_date');
        $poster = $this->_default_property_member($properties, 'poster');
        $intended_solely_for = $this->_default_property_member_null($properties, 'intended_solely_for');
        $last_edit_time = $this->_default_property_time_null($properties, 'edit_date');
        $last_edit_by = $this->_default_property_member_null($properties, 'last_edit_by');
        $parent_id = $this->_default_property_resource_id_null('post', $properties, 'parent_id');
        $id = cns_make_post($topic_id, $label, $post, $skip_sig, null, $validated, $is_emphasised, $poster_name_if_guest, $ip_address, $time, $poster, $intended_solely_for, $last_edit_time, $last_edit_by, false, true, null, false, null, null, false, true, null, false, $parent_id);

        $this->_resource_save_extend($this->file_resource_type, strval($id), $filename, $label, $properties);

        return strval($id);
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

        $rows = $GLOBALS['FORUM_DB']->query_select('f_posts', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $properties = array(
            'label' => $row['p_title'],
            'post' => get_translated_text($row['p_post'], $GLOBALS['FORUM_DB']),
            'skip_sig' => $row['p_skip_sig'],
            'validated' => $row['p_validated'],
            'is_emphasised' => $row['p_is_emphasised'],
            'poster_name_if_guest' => $row['p_poster_name_if_guest'],
            'ip_address' => $row['p_ip_address'],
            'intended_solely_for' => $row['p_intended_solely_for'],
            'parent_id' => remap_resource_id_as_portable('post', $row['p_parent_id']),
            'poster' => remap_resource_id_as_portable('member', $row['p_poster']),
            'last_edit_by' => $row['p_last_edit_by'],
            'add_date' => remap_time_as_portable($row['p_time']),
            'edit_date' => remap_time_as_portable($row['p_last_edit_time']),
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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'topic');
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category == '') {
            return false;
        }

        require_code('cns_posts_action3');

        $label = $this->_default_property_str($properties, 'label');
        $topic_id = $this->_integer_category($category);
        $post = $this->_default_property_str($properties, 'post');
        $skip_sig = $this->_default_property_int($properties, 'skip_sig');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $is_emphasised = $this->_default_property_int($properties, 'is_emphasised');
        $poster_name_if_guest = $this->_default_property_str($properties, 'poster_name_if_guest');
        $ip_address = $this->_default_property_str_null($properties, 'ip_address');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $poster = $this->_default_property_member($properties, 'poster');
        $intended_solely_for = $this->_default_property_member_null($properties, 'intended_solely_for');
        $last_edit_time = $this->_default_property_time($properties, 'edit_date');
        $last_edit_by = $this->_default_property_member($properties, 'last_edit_by');
        $parent_id = $this->_default_property_resource_id_null('post', $properties, 'parent_id');

        cns_edit_post(intval($resource_id), $validated, $label, $post, $skip_sig, $is_emphasised, $intended_solely_for, true, false, '', false, $last_edit_time, $add_time, $poster, true, false);

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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path, 'topic');

        $topic_id = $this->_integer_category($category);

        require_code('cns_posts_action3');
        cns_delete_posts_topic($topic_id, array(intval($resource_id)), '', false, false);

        return true;
    }
}
