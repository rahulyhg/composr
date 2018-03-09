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

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_wiki extends Resource_fs_base
{
    public $folder_resource_type = 'wiki_page';
    public $file_resource_type = 'wiki_post';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        switch ($resource_type) {
            case 'wiki_post':
                return $GLOBALS['SITE_DB']->query_select_value('wiki_posts', 'COUNT(*)');

            case 'wiki_page':
                return $GLOBALS['SITE_DB']->query_select_value('wiki_pages', 'COUNT(*)');
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
            case 'wiki_post':
                $_ret = $GLOBALS['SITE_DB']->query_select('wiki_posts', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('the_message') => $label), 'ORDER BY id');
                $ret = array();
                foreach ($_ret as $r) {
                    $ret[] = strval($r['id']);
                }
                return $ret;

            case 'wiki_page':
                $_ret = $GLOBALS['SITE_DB']->query_select('wiki_pages', array('id'), array($GLOBALS['SITE_DB']->translate_field_ref('title') => $label), 'ORDER BY id');
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
    public function is_active()
    {
        return addon_installed('wiki');
    }

    /**
     * Standard Commandr-fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error)
     */
    public function folder_add($filename, $path, $properties)
    {
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        if ($category == '') {
            $category = strval(db_get_first_id());
        }/*return false;*/ // Can't create more than one root

        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, $this->folder_resource_type);

        require_code('wiki');

        $parent_id = $this->_integer_category($category);
        $description = $this->_default_property_str($properties, 'description');
        $notes = $this->_default_property_str($properties, 'notes');
        $show_posts = $this->_default_property_int($properties, 'show_posts');
        $member_id = $this->_default_property_member($properties, 'submitter');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_date = $this->_default_property_time_null($properties, 'edit_date');
        $views = $this->_default_property_int($properties, 'views');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');
        $id = wiki_add_page($label, $description, $notes, $show_posts, $member_id, $add_time, $views, $meta_keywords, $meta_description, $edit_date);

        $the_order = $GLOBALS['SITE_DB']->query_select_value('wiki_children', 'MAX(the_order)', array('parent_id' => $parent_id));
        if ($the_order === null) {
            $the_order = -1;
        }
        $the_order++;
        if ($parent_id !== null) {
            $GLOBALS['SITE_DB']->query_insert('wiki_children', array('parent_id' => $parent_id, 'child_id' => $id, 'the_order' => $the_order, 'title' => $label));
        }

        $this->_resource_save_extend($this->folder_resource_type, strval($id), $filename, $label, $properties);

        return strval($id);
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
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('wiki_pages', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        list($meta_keywords, $meta_description) = seo_meta_get_for('wiki_page', strval($row['id']));

        $properties = array(
            'label' => get_translated_text($row['title']),
            'description' => get_translated_text($row['description']),
            'notes' => $row['notes'],
            'show_posts' => $row['show_posts'],
            'submitter' => remap_resource_id_as_portable('member', $row['submitter']),
            'views' => $row['wiki_views'],
            'meta_keywords' => $meta_keywords,
            'meta_description' => $meta_description,
            'add_date' => remap_time_as_portable($row['add_date']),
            'edit_date' => remap_time_as_portable($row['edit_date']),
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
     * @param  boolean $explicit_move Whether we are definitely moving (as opposed to possible having it in multiple positions)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function folder_edit($filename, $path, $properties, $explicit_move = false)
    {
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);
        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, $this->folder_resource_type);

        require_code('wiki');

        $parent_id = $this->_integer_category($category);
        $label = $this->_default_property_str($properties, 'label');
        $description = $this->_default_property_str($properties, 'description');
        $notes = $this->_default_property_str($properties, 'notes');
        $show_posts = $this->_default_property_int($properties, 'show_posts');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_date = $this->_default_property_time($properties, 'edit_date');
        $views = $this->_default_property_int($properties, 'views');
        $meta_keywords = $this->_default_property_str($properties, 'meta_keywords');
        $meta_description = $this->_default_property_str($properties, 'meta_description');

        $id = intval($resource_id);
        wiki_edit_page($id, $label, $description, $notes, $show_posts, $meta_keywords, $meta_description, $submitter, $edit_date, $add_time, $views, true);

        // Move
        $old_path = $this->search($resource_type, $resource_id, false);
        list(, $old_category) = ($old_path == '') ? array('wiki_page', null) : $this->folder_convert_filename_to_id($old_path);
        $old_parent_id = $this->_integer_category($old_category);
        if ($old_parent_id !== $parent_id) {
            $the_order = $GLOBALS['SITE_DB']->query_select_value_if_there('wiki_children', 'the_order', array('child_id' => $id, 'parent_id' => $old_parent_id));
            if ($explicit_move) {
                $GLOBALS['SITE_DB']->query_delete('wiki_children', array('child_id' => $id, 'parent_id' => $old_parent_id));
            }
            if (($the_order === null) || (!$explicit_move)) { // Put on end of existing children
                $the_order = $GLOBALS['SITE_DB']->query_select_value('wiki_children', 'MAX(the_order)', array('parent_id' => $parent_id));
                if ($the_order === null) {
                    $the_order = -1;
                }
                $the_order++;
            }
            if ($parent_id !== null) {
                $GLOBALS['SITE_DB']->query_insert('wiki_children', array('parent_id' => $parent_id, 'child_id' => $id, 'the_order' => $the_order, 'title' => $label));
            }
        }

        $this->_resource_save_extend($this->folder_resource_type, $resource_id, $filename, $label, $properties);

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

        require_code('wiki');
        wiki_delete_page(intval($resource_id));

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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category === null) {
            return false; // Folder not found
        }

        require_code('wiki');

        $page_id = $this->_integer_category($category);
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $member_id = $this->_default_property_member($properties, 'member_id');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_date = $this->_default_property_time_null($properties, 'edit_date');
        $views = $this->_default_property_int($properties, 'views');
        $id = wiki_add_post($page_id, $label, $validated, $member_id, true, $add_time, $views, $edit_date);

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

        $rows = $GLOBALS['SITE_DB']->query_select('wiki_posts', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $properties = array(
            'label' => get_translated_text($row['the_message']),
            'validated' => $row['validated'],
            'views' => $row['wiki_views'],
            'member_id' => remap_resource_id_as_portable('member', $row['member_id']),
            'add_date' => remap_time_as_portable($row['date_and_time']),
            'edit_date' => remap_time_as_portable($row['edit_date']),
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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if ($category === null) {
            return false; // Folder not found
        }

        require_code('wiki');

        $label = $this->_default_property_str($properties, 'label');
        $page_id = $this->_integer_category($category);
        $validated = $this->_default_property_int_null($properties, 'validated');
        if ($validated === null) {
            $validated = 1;
        }
        $member_id = $this->_default_property_member($properties, 'member_id');
        $add_time = $this->_default_property_time($properties, 'add_date');
        $edit_time = $this->_default_property_time($properties, 'edit_date');
        $views = $this->_default_property_int($properties, 'views');

        wiki_edit_post(intval($resource_id), $label, $validated, $member_id, $page_id, $edit_time, $add_time, $views, true);

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

        require_code('wiki');
        wiki_delete_post(intval($resource_id));

        return true;
    }
}
