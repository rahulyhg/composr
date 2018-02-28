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
 * @package    banners
 */

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_banners extends Resource_fs_base
{
    public $folder_resource_type = 'banner_type';
    public $file_resource_type = 'banner';

    /**
     * Standard Commandr-fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        switch ($resource_type) {
            case 'banner':
                return $GLOBALS['SITE_DB']->query_select_value('banners', 'COUNT(*)');

            case 'banner_type':
                return $GLOBALS['SITE_DB']->query_select_value('banner_types', 'COUNT(*)');
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
            case 'banner':
                $ret = $GLOBALS['SITE_DB']->query_select('banners', array('name'), array('name' => $label));
                return collapse_1d_complexity('name', $ret);

            case 'banner_type':
                if ($label == 'untitled') {
                    $label = '';
                }
                $ret = $GLOBALS['SITE_DB']->query_select('banner_types', array('id'), array('id' => $label));
                return collapse_1d_complexity('id', $ret);
        }
        return array();
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_folder_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', $row['id']) . ' AND  (' . db_string_equal_to('the_type', 'ADD_BANNER_TYPE') . ' OR ' . db_string_equal_to('the_type', 'EDIT_BANNER_TYPE') . ')';
        return $GLOBALS['SITE_DB']->query_value_if_there($query);
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
        if ($path != '') {
            return false; // Only one depth allowed for this resource type
        }

        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, $this->folder_resource_type);

        require_code('banners2');

        $is_textual = $this->_default_property_int($properties, 'is_textual');
        $image_width = $this->_default_property_int_null($properties, 'image_width');
        if ($image_width === null) {
            $image_width = 300;
        }
        $image_height = $this->_default_property_int_null($properties, 'image_height');
        if ($image_height === null) {
            $image_height = 250;
        }
        $max_file_size = $this->_default_property_int_null($properties, 'max_file_size');
        if ($max_file_size === null) {
            $max_file_size = 100 * 1024;
        }
        $comcode_inline = $this->_default_property_int($properties, 'comcode_inline');
        $name = ($label == '') ? ''/*blank names allowed*/ : $this->_create_name_from_label($label);

        $name = add_banner_type($name, $is_textual, $image_width, $image_height, $max_file_size, $comcode_inline, true);

        $this->_resource_save_extend($this->folder_resource_type, $name, $filename, $label, $properties);

        return $name;
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

        $rows = $GLOBALS['SITE_DB']->query_select('banner_types', array('*'), array('id' => $resource_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $properties = array(
            'label' => $row['id'],
            'is_textual' => $row['t_is_textual'],
            'image_width' => $row['t_image_width'],
            'image_height' => $row['t_image_height'],
            'max_file_size' => $row['t_max_file_size'],
            'comcode_inline' => $row['t_comcode_inline'],
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
    public function folder_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->folder_convert_filename_to_id($filename);
        list($properties, $label) = $this->_folder_magic_filter($filename, $path, $properties, $this->folder_resource_type);

        require_code('banners2');

        $label = $this->_default_property_str($properties, 'label');
        $is_textual = $this->_default_property_int($properties, 'is_textual');
        $image_width = $this->_default_property_int_null($properties, 'image_width');
        if ($image_width === null) {
            $image_width = 300;
        }
        $image_height = $this->_default_property_int_null($properties, 'image_height');
        if ($image_height === null) {
            $image_height = 250;
        }
        $max_file_size = $this->_default_property_int_null($properties, 'max_file_size');
        if ($max_file_size === null) {
            $max_file_size = 100 * 1024;
        }
        $comcode_inline = $this->_default_property_int($properties, 'comcode_inline');
        $name = ($label == '') ? ''/*blank names allowed*/ : $this->_create_name_from_label($label);

        $name = edit_banner_type($resource_id, $name, $is_textual, $image_width, $image_height, $max_file_size, $comcode_inline, true);

        $this->_resource_save_extend($this->folder_resource_type, $name, $filename, $label, $properties);

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

        require_code('banners2');
        delete_banner_type($resource_id);

        return true;
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
        if ($resource_id == '') {
            return 'untitled';
        }

        return parent::folder_convert_id_to_filename($resource_type, $resource_id);
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

        if ($filename == 'untitled') {
            return array('banner_type', '');
        }

        return parent::folder_convert_filename_to_id($filename, 'topic');
    }

    /**
     * Standard Commandr-fs date fetch function for resource-fs hooks. Defined when getting an edit date is not easy.
     *
     * @param  array $row Resource row (not full, but does contain the ID)
     * @return ?TIME The edit date or add date, whichever is higher (null: could not find one)
     */
    protected function _get_file_edit_date($row)
    {
        $query = 'SELECT MAX(date_and_time) FROM ' . get_table_prefix() . 'actionlogs WHERE ' . db_string_equal_to('param_a', $row['name']) . ' AND  (' . db_string_equal_to('the_type', 'ADD_BANNER') . ' OR ' . db_string_equal_to('the_type', 'EDIT_BANNER') . ')';
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
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if (is_null($category)) {
            return false; // Folder not found
        }

        require_code('banners2');

        $name = $this->_create_name_from_label($label);
        $img_url = $this->_default_property_urlpath($properties, 'img_url');
        $title_text = $this->_default_property_str($properties, 'title_text');
        $direct_code = $this->_default_property_str($properties, 'direct_code');
        $campaignremaining = $this->_default_property_int($properties, 'campaignremaining');
        $site_url = $this->_default_property_str($properties, 'site_url');
        $importancemodulus = $this->_default_property_int($properties, 'importancemodulus');
        $notes = $this->_default_property_str($properties, 'notes');
        $the_type = $this->_default_property_int($properties, 'the_type');
        $expiry_date = $this->_default_property_time_null($properties, 'expiry_date');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if (is_null($validated)) {
            $validated = 1;
        }
        $b_type = $category;
        $b_types = empty($properties['b_types']) ? array() : $properties['b_types'];
        $regions = empty($properties['regions']) ? array() : $properties['regions'];
        $notes = $this->_default_property_str($properties, 'notes');
        $time = $this->_default_property_time($properties, 'add_date');
        $hits_from = $this->_default_property_int($properties, 'hits_from');
        $hits_to = $this->_default_property_int($properties, 'hits_to');
        $views_from = $this->_default_property_int($properties, 'views_from');
        $views_to = $this->_default_property_int($properties, 'views_to');
        $edit_date = $this->_default_property_time_null($properties, 'edit_date');

        $name = add_banner($name, $img_url, $title_text, $label, $direct_code, $campaignremaining, $site_url, $importancemodulus, $notes, $the_type, $expiry_date, $submitter, $validated, $b_type, $b_types, $regions, $time, $hits_from, $hits_to, $views_from, $views_to, $edit_date, true);

        $this->_resource_save_extend($this->file_resource_type, $name, $filename, $label, $properties);

        return $name;
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

        $rows = $GLOBALS['SITE_DB']->query_select('banners', array('*'), array('name' => $resource_id), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        $properties = array(
            'label' => $row['name'],
            'img_url' => remap_urlpath_as_portable($row['img_url']),
            'title_text' => $row['b_title_text'],
            'direct_code' => $row['b_direct_code'],
            'campaignremaining' => $row['campaign_remaining'],
            'site_url' => $row['site_url'],
            'importancemodulus' => $row['importance_modulus'],
            'notes' => $row['notes'],
            'the_type' => $row['the_type'],
            'expiry_date' => remap_time_as_portable($row['expiry_date']),
            'validated' => $row['validated'],
            'b_types' => collapse_1d_complexity('b_type', $GLOBALS['SITE_DB']->query_select('banners_types', array('b_type'), array('name' => $row['name']))),
            'regions' => collapse_1d_complexity('region', $GLOBALS['SITE_DB']->query_select('content_regions', array('region'), array('content_type' => 'banner', 'content_id' => $row['name']))),
            'hits_from' => $row['hits_from'],
            'hits_to' => $row['hits_to'],
            'views_from' => $row['views_from'],
            'views_to' => $row['views_to'],
            'submitter' => remap_resource_id_as_portable('member', $row['submitter']),
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
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);
        list($category_resource_type, $category) = $this->folder_convert_filename_to_id($path);
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties, $this->file_resource_type);

        if (is_null($category)) {
            return false; // Folder not found
        }

        require_code('banners2');

        $label = $this->_default_property_str($properties, 'label');
        $name = $this->_create_name_from_label($label);
        $img_url = $this->_default_property_urlpath($properties, 'img_url', true);
        $title_text = $this->_default_property_str($properties, 'title_text');
        $direct_code = $this->_default_property_str($properties, 'direct_code');
        $campaignremaining = $this->_default_property_int($properties, 'campaignremaining');
        $site_url = $this->_default_property_str($properties, 'site_url');
        $importancemodulus = $this->_default_property_int($properties, 'importancemodulus');
        $notes = $this->_default_property_str($properties, 'notes');
        $the_type = $this->_default_property_int($properties, 'the_type');
        $expiry_date = $this->_default_property_time_null($properties, 'expiry_date');
        $submitter = $this->_default_property_member($properties, 'submitter');
        $validated = $this->_default_property_int_null($properties, 'validated');
        if (is_null($validated)) {
            $validated = 1;
        }
        $b_type = $category;
        $b_types = empty($properties['b_types']) ? array() : $properties['b_types'];
        $regions = empty($properties['regions']) ? array() : $properties['regions'];
        $add_time = $this->_default_property_time($properties, 'add_date');
        $hits_from = $this->_default_property_int($properties, 'hits_from');
        $hits_to = $this->_default_property_int($properties, 'hits_to');
        $views_from = $this->_default_property_int($properties, 'views_from');
        $views_to = $this->_default_property_int($properties, 'views_to');
        $edit_date = $this->_default_property_time($properties, 'edit_date');

        $name = edit_banner($resource_id, $name, $img_url, $title_text, $label, $direct_code, $campaignremaining, $site_url, $importancemodulus, $notes, $the_type, $expiry_date, $submitter, $validated, $b_type, $b_types, $regions, $edit_date, $add_time, true, true);

        $this->_resource_save_extend($this->file_resource_type, $name, $filename, $label, $properties);

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

        require_code('banners2');
        delete_banner($resource_id);

        return true;
    }
}
