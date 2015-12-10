<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    newsletter
 */

require_code('resource_fs');

/**
 * Hook class.
 */
class Hook_commandr_fs_periodic_newsletters extends Resource_fs_base
{
    public $file_resource_type = 'periodic_newsletter';

    /**
     * Standard commandr_fs function for seeing how many resources are. Useful for determining whether to do a full rebuild.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @return integer How many resources there are
     */
    public function get_resources_count($resource_type)
    {
        return $GLOBALS['SITE_DB']->query_select_value('newsletter_periodic', 'COUNT(*)');
    }

    /**
     * Standard commandr_fs function for searching for a resource by label.
     *
     * @param  ID_TEXT $resource_type The resource type
     * @param  LONG_TEXT $label The resource label
     * @return array A list of resource IDs
     */
    public function find_resource_by_label($resource_type, $label)
    {
        $_ret = $GLOBALS['SITE_DB']->query_select('newsletter_periodic', array('id'), array('np_subject' => $label));
        $ret = array();
        foreach ($_ret as $r) {
            $ret[] = strval($r['id']);
        }
        return $ret;
    }

    /**
     * Standard commandr_fs add function for resource-fs hooks. Adds some resource with the given label and properties.
     *
     * @param  LONG_TEXT $filename Filename OR Resource label
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_add($filename, $path, $properties)
    {
        list($properties, $label) = $this->_file_magic_filter($filename, $path, $properties);

        $message = $this->_default_property_str($properties, 'message');
        $lang = $this->_default_property_str($properties, 'lang');
        $send_details = $this->_default_property_str($properties, 'send_details');
        $html_only = $this->_default_property_int($properties, 'html_only');
        $from_email = $this->_default_property_str($properties, 'from_email');
        $from_name = $this->_default_property_str($properties, 'from_name');
        $priority = $this->_default_property_int($properties, 'priority');
        $csv_data = $this->_default_property_str($properties, 'csv_data');
        $frequency = $this->_default_property_str($properties, 'frequency');
        $day = $this->_default_property_int($properties, 'day');
        $in_full = $this->_default_property_int($properties, 'in_full');
        $template = $this->_default_property_str($properties, 'template');
        $last_sent = $this->_default_property_time($properties, 'last_sent');

        require_code('newsletter');

        $id = add_periodic_newsletter($label, $message, $lang, $send_details, $html_only, $from_email, $from_name, $priority, $csv_data, $frequency, $day, $in_full, $template, $last_sent);

        return strval($id);
    }

    /**
     * Standard commandr_fs load function for resource-fs hooks. Finds the properties for some resource.
     *
     * @param  SHORT_TEXT $filename Filename
     * @param  string $path The path (blank: root / not applicable). It may be a wildcarded path, as the path is used for content-type identification only. Filenames are globally unique across a hook; you can calculate the path using ->search.
     * @return ~array Details of the resource (false: error)
     */
    public function file_load($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        $rows = $GLOBALS['SITE_DB']->query_select('newsletter_periodic', array('*'), array('id' => intval($resource_id)), '', 1);
        if (!array_key_exists(0, $rows)) {
            return false;
        }
        $row = $rows[0];

        return array(
            'label' => $row['np_subject'],
            'message' => $row['np_message'],
            'lang' => $row['np_lang'],
            'send_details' => $row['np_send_details'],
            'html_only' => $row['np_html_only'],
            'from_email' => $row['np_from_email'],
            'from_name' => $row['np_from_name'],
            'priority' => $row['np_priority'],
            'csv_data' => $row['np_csv_data'],
            'frequency' => $row['np_frequency'],
            'day' => $row['np_day'],
            'in_full' => $row['np_in_full'],
            'template' => $row['np_template'],
            'last_sent' => $row['np_last_sent'],
        );
    }

    /**
     * Standard commandr_fs edit function for resource-fs hooks. Edits the resource to the given properties.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @param  array $properties Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
     * @return ~ID_TEXT The resource ID (false: error, could not create via these properties / here)
     */
    public function file_edit($filename, $path, $properties)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);
        list($properties,) = $this->_file_magic_filter($filename, $path, $properties);

        $label = $this->_default_property_str($properties, 'label');
        $message = $this->_default_property_str($properties, 'message');
        $lang = $this->_default_property_str($properties, 'lang');
        $send_details = $this->_default_property_str($properties, 'send_details');
        $html_only = $this->_default_property_int($properties, 'html_only');
        $from_email = $this->_default_property_str($properties, 'from_email');
        $from_name = $this->_default_property_str($properties, 'from_name');
        $priority = $this->_default_property_int($properties, 'priority');
        $csv_data = $this->_default_property_str($properties, 'csv_data');
        $frequency = $this->_default_property_str($properties, 'frequency');
        $day = $this->_default_property_int($properties, 'day');
        $in_full = $this->_default_property_int($properties, 'in_full');
        $template = $this->_default_property_str($properties, 'template');
        $last_sent = $this->_default_property_time($properties, 'last_sent');

        require_code('newsletter');

        edit_periodic_newsletter(intval($resource_id), $label, $message, $lang, $send_details, $html_only, $from_email, $from_name, $priority, $csv_data, $frequency, $day, $in_full, $template, $last_sent);

        return $resource_id;
    }

    /**
     * Standard commandr_fs delete function for resource-fs hooks. Deletes the resource.
     *
     * @param  ID_TEXT $filename The filename
     * @param  string $path The path (blank: root / not applicable)
     * @return boolean Success status
     */
    public function file_delete($filename, $path)
    {
        list($resource_type, $resource_id) = $this->file_convert_filename_to_id($filename);

        require_code('newsletter');

        delete_periodic_newsletter(intval($resource_id));

        return true;
    }
}
