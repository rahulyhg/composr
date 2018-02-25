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
 * @package    cns_multi_moderations
 */

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_multi_moderations extends Resource_fs_base
{
    public $file_resource_type = 'multi_moderation';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        return $GLOBALS['FORUM_DB']->query_select_value('f_multi_moderations', 'COUNT(*)');
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
        $_ret = $GLOBALS['FORUM_DB']->query_select('f_multi_moderations', array('id'), array($GLOBALS['FORUM_DB']->translate_field_ref('mm_name') => $label), 'ORDER BY id');
        $ret = array();
        foreach ($_ret as $r) {
            $ret[] = strval($r['id']);
        }
        return $ret;
    }

    /**
     * Whether the filesystem hook is active.
     *
     * @return boolean Whether it is
     */
    public function is_active()
    {
        return (get_forum_type() == 'cns') && (!is_on_multi_site_network());
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_file_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', strval($row['id'])) . ' AND  (' . db_string_equal_to('the_type', 'ADD_MULTI_MODERATION') . ' OR ' . db_string_equal_to('the_type', 'EDIT_MULTI_MODERATION') . ')';
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

        require_code('cns_moderation_action');

        $post_text = $this->_default_property_str($properties, 'post_text');
        $move_to = $this->_default_property_int($properties, 'move_to');
        $pin_state = $this->_default_property_int($properties, 'pin_state');
        $open_state = $this->_default_property_int($properties, 'open_state');
        $forum_multi_code = $this->_default_property_str($properties, 'forum_multi_code');
        $title_suffix = $this->_default_property_str($properties, 'title_suffix');

        $id = cns_make_multi_moderation($label, $post_text, $move_to, $pin_state, $open_state, $forum_multi_code, $title_suffix);

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

        $rows = $GLOBALS['FORUM_DB']->query_select('f_multi_moderations', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $properties = array(
            'label' => get_translated_text($row['mm_name'], $GLOBALS['FORUM_DB']),
            'post_text' => $row['mm_post_text'],
            'move_to' => $row['mm_move_to'],
            'pin_state' => $row['mm_pin_state'],
            'open_state' => $row['mm_open_state'],
            'forum_multi_code' => $row['mm_forum_multi_code'],
            'title_suffix' => $row['mm_title_suffix'],
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

        require_code('cns_moderation_action2');

        $label = $this->_default_property_str($properties, 'label');
        $post_text = $this->_default_property_str($properties, 'post_text');
        $move_to = $this->_default_property_int($properties, 'move_to');
        $pin_state = $this->_default_property_int($properties, 'pin_state');
        $open_state = $this->_default_property_int($properties, 'open_state');
        $forum_multi_code = $this->_default_property_str($properties, 'forum_multi_code');
        $title_suffix = $this->_default_property_str($properties, 'title_suffix');

        cns_edit_multi_moderation(intval($resource_id), $label, $post_text, $move_to, $pin_state, $open_state, $forum_multi_code, $title_suffix);

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

        require_code('cns_moderation_action2');
        cns_delete_multi_moderation(intval($resource_id));

        return true;
    }
}
