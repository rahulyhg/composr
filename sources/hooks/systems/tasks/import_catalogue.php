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
 * @package    catalogues
 */

/**
 * Hook class.
 */
class Hook_task_import_catalogue
{
    /**
     * Run the task hook.
     *
     * @param  ID_TEXT $catalogue_name The name of the catalogue that was used
     * @param  string $key_field The title of the key field (blank: none)
     * @param  ID_TEXT $new_handling New handling method
     * @set skip add
     * @param  ID_TEXT $delete_handling Delete handling method
     * @set delete leave
     * @param  ID_TEXT $update_handling Update handling method
     * @set overwrite freshen skip delete
     * @param  ID_TEXT $meta_keywords_field Meta keywords field (blank: none)
     * @param  ID_TEXT $meta_description_field Meta description field (blank: none)
     * @param  ID_TEXT $notes_field Notes field (blank: none)
     * @param  boolean $allow_rating Whether rating is allowed for this resource
     * @param  boolean $allow_comments Whether comments are allowed for this resource
     * @param  boolean $allow_trackbacks Whether trackbacks are allowed for this resource
     * @param  PATH $csv_path The CSV file being imported
     * @return ?array A tuple of at least 2: Return mime-type, content (either Tempcode, or a string, or a filename and file-path pair to a temporary file), map of HTTP headers if transferring immediately, map of ini_set commands if transferring immediately (null: show standard success message)
     */
    public function run($catalogue_name, $key_field, $new_handling, $delete_handling, $update_handling, $meta_keywords_field, $meta_description_field, $notes_field, $allow_rating, $allow_comments, $allow_trackbacks, $csv_path)
    {
        require_code('catalogues2');
        require_lang('catalogues');

        $fields = $GLOBALS['SITE_DB']->query_select('catalogue_fields', array('*'), array('c_name' => $catalogue_name));

        // Find out what categories we have in the catalogue
        $categories = array();
        $cat_rows = $GLOBALS['SITE_DB']->query_select('catalogue_categories', array('cc_title', 'cc_parent_id', 'id'), array('c_name' => $catalogue_name));
        foreach ($cat_rows as $cat_row) {
            $categories[get_translated_text($cat_row['cc_title'])] = $cat_row['id'];

            // Root category is 'default' category for catalogue importing (category with same name as catalogue)
            if ((!array_key_exists($catalogue_name, $categories)) && (is_null($cat_row['cc_parent_id']))) {
                $categories[$catalogue_name] = $cat_row['id'];
            }
        }
        $root_cat = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_categories', 'id', array('cc_parent_id' => null));

        // Open CSV file
        safe_ini_set('auto_detect_line_endings', '1');
        $handle = fopen($csv_path, 'rt');

        // Read column names
        $del = ',';
        $csv_field_titles = fgetcsv($handle, 1000, $del);
        if ((count($csv_field_titles) == 1) && (strpos($csv_field_titles[0], ';') !== false)) {
            $del = ';';
            rewind($handle);
            $csv_field_titles = fgetcsv($handle, 1000, $del);
        }
        $csv_field_titles = array_flip($csv_field_titles);

        // Check key exists, if we have one
        if (($key_field != '') && ($key_field != 'ID')) {
            if (!array_key_exists($key_field, $csv_field_titles)) {
                fclose($handle);
                @unlink($csv_path);
                return array(null, do_lang_tempcode('CATALOGUES_IMPORT_MISSING_KEY_FIELD'));
            }
            $found_key = false;
            foreach ($fields as $field) {
                if (get_translated_text($field['cf_name']) == $key_field) {
                    $found_key = true;
                    break;
                }
            }
            if (!$found_key) {
                fclose($handle);
                @unlink($csv_path);
                return array(null, do_lang_tempcode('CATALOGUES_IMPORT_MISSING_KEY_FIELD'));
            }
        }

        global $LAX_COMCODE;
        $temp2 = $LAX_COMCODE;
        $LAX_COMCODE = true;

        if (($meta_keywords_field != '') && (!array_key_exists($meta_keywords_field, $csv_field_titles))) {
            fclose($handle);
            @unlink($csv_path);
            return array(null, do_lang_tempcode('CATALOGUES_IMPORT_MISSING_META_KEYWORDS_FIELD'));
        }
        if (($meta_description_field != '') && (!array_key_exists($meta_description_field, $csv_field_titles))) {
            fclose($handle);
            @unlink($csv_path);
            return array(null, do_lang_tempcode('CATALOGUES_IMPORT_MISSING_META_DESCRIPTION_FIELD'));
        }
        if (($notes_field != '') && (!array_key_exists($notes_field, $csv_field_titles))) {
            fclose($handle);
            @unlink($csv_path);
            return array(null, do_lang_tempcode('CATALOGUES_IMPORT_MISSING_NOTES_FIELD'));
        }

        // Import, line by line
        $matched_ids = array();
        while (($data = fgetcsv($handle, 100000, $del)) !== false) {
            if ($data === array(null)) {
                continue; // blank line
            }
            $test = $this->import_csv_lines($catalogue_name, $data, $root_cat, $fields, $categories, $csv_field_titles, $key_field, $new_handling, $delete_handling, $update_handling, $matched_ids, $notes_field, $meta_keywords_field, $meta_description_field, $allow_rating, $allow_comments, $allow_trackbacks);
            if (!is_null($test)) {
                fclose($handle);
                @unlink($csv_path);
                return $test;
            }
        }

        // Handle non-matched existing ones
        if ($delete_handling == 'delete') {
            $all_entry_ids = $GLOBALS['SITE_DB']->query_select('catalogue_entries', array('id'), array('c_name' => $catalogue_name));
            foreach ($all_entry_ids as $id) {
                if (!array_key_exists($id['id'], $matched_ids)) {
                    // Delete entry
                    actual_delete_catalogue_entry($id['id']);
                }
            }
        }

        $LAX_COMCODE = $temp2;

        fclose($handle);
        @unlink($csv_path);
        return null;
    }

    /**
     * Create an entry-id=>value map of uploaded csv data and it's importing
     *
     * @param  ID_TEXT $catalogue_name The name of the catalogue that was used
     * @param  array $csv_data Data array of CSV imported file's lines
     * @param  ?AUTO_LINK $catalogue_root Catalogue root ID (null: Not a tree catalogue)
     * @param  array $fields Array of catalogue fields
     * @param  array $categories Array of categories
     * @param  array $csv_field_titles Array of csv field titles
     * @param  ID_TEXT $key_field Key field
     * @param  ID_TEXT $new_handling New handling method
     * @param  ID_TEXT $delete_handling Delete handling method
     * @param  ID_TEXT $update_handling Update handling method
     * @param  array $matched_ids IDs that are matched are collected here
     * @param  ID_TEXT $notes_field Notes field
     * @param  ID_TEXT $meta_keywords_field Meta keywords field
     * @param  ID_TEXT $meta_description_field Meta description field
     * @param  boolean $allow_rating Whether rating is allowed for this resource
     * @param  boolean $allow_comments Whether comments are allowed for this resource
     * @param  boolean $allow_trackbacks Whether trackbacks are allowed for this resource
     * @return ?array Return to propagate [immediate exit] (null: nothing to propagate)
     */
    public function import_csv_lines($catalogue_name, $csv_data, $catalogue_root, $fields, &$categories, $csv_field_titles, $key_field, $new_handling, $delete_handling, $update_handling, &$matched_ids, $notes_field, $meta_keywords_field, $meta_description_field, $allow_rating, $allow_comments, $allow_trackbacks)
    {
        $notes = '';
        $meta_keywords = '';
        $meta_description = '';
        $key = '';

        if (array_key_exists($notes_field, $csv_field_titles)) {
            if (!array_key_exists($csv_field_titles[$notes_field], $csv_data)) {
                $csv_data[$csv_field_titles[$notes_field]] = ''; // Not set for this particular row, even though column exists in the CSV
            }

            $notes = $csv_data[$csv_field_titles[$notes_field]];
            unset($csv_field_titles[$notes_field]);
        }

        if (array_key_exists($meta_keywords_field, $csv_field_titles)) {
            if (!array_key_exists($csv_field_titles[$meta_keywords_field], $csv_data)) {
                $csv_data[$csv_field_titles[$meta_keywords_field]] = ''; // Not set for this particular row, even though column exists in the CSV
            }

            $meta_keywords = $csv_data[$csv_field_titles[$meta_keywords_field]];
            unset($csv_field_titles[$meta_keywords_field]);
        }

        if (array_key_exists($meta_description_field, $csv_field_titles)) {
            if (!array_key_exists($csv_field_titles[$meta_description_field], $csv_data)) {
                $csv_data[$csv_field_titles[$meta_description_field]] = ''; // Not set for this particular row, even though column exists in the CSV
            }

            $meta_description = $csv_data[$csv_field_titles[$meta_description_field]];
            unset($csv_field_titles[$meta_description_field]);
        }

        if (array_key_exists($key_field, $csv_field_titles)) {
            if (!array_key_exists($csv_field_titles[$key_field], $csv_data)) {
                $csv_data[$csv_field_titles[$key_field]] = ''; // Not set for this particular row, even though column exists in the CSV
            }

            $key = $csv_data[$csv_field_titles[$key_field]];
        }

        // Tidy up fields, to make $map
        $map = array();
        $matched_at_least_one_field = false;
        foreach ($fields as $field) {
            $field_name = get_translated_text($field['cf_name']);

            if (array_key_exists($field_name, $csv_field_titles)) {
                if (!array_key_exists($csv_field_titles[$field_name], $csv_data)) {
                    $csv_data[$csv_field_titles[$field_name]] = ''; // Not set for this particular row, even though column exists in the CSV
                }

                $value = trim($csv_data[$csv_field_titles[$field_name]]);

                if (($field['cf_type'] == 'picture') || ($field['cf_type'] == 'video')) {
                    if (preg_replace('#\..*$#', '', $value) == 'Noimage') {
                        $value = '';
                    }

                    if ($value != '') {
                        if ((strpos($value, '\\') === false) && (strpos($value, '/') === false)) {
                            $value = cms_rawurlrecode('uploads/catalogues/' . rawurlencode($value));
                        }
                    }
                } else {
                    if ((strip_tags($value) != $value) && (strpos($value, '[html') === false) && (strpos($value, '[semihtml') === false)) {
                        $value = '[html]' . $value . '[/html]';
                    }
                }

                $map[$field['id']] = $value;
                $matched_at_least_one_field = true; // to check matching of csv and db fields
            } else { // Can't bind the field, so we'll make this the default
                $map[$field['id']] = $field['cf_default'];
            }
        }

        if (!$matched_at_least_one_field) {
            return array(null, do_lang_tempcode('FIELDS_UNMATCH'));
        }

        // See if we can match to existing record, via $key_field
        $method = 'add';
        $has_match = mixed();
        if ($key_field != '') {
            if ($key_field == 'ID') {
                if ($key != '') {
                    $has_match = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_entries', 'id', array('id' => intval($key)));
                }
            } else {
                require_code('fields');
                foreach ($fields as $field) {
                    if (get_translated_text($field['cf_name']) == $key_field) {
                        $hook_ob = get_fields_hook($field['cf_type']);
                        list(, , $db_type) = $hook_ob->get_field_value_row_bits($field);
                        switch ($db_type) {
                            case 'integer':
                                $has_match = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_' . $db_type . ' x JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.id=x.ce_id', 'x.id', array('c_name' => $catalogue_name, 'cv_value' => intval($key)));
                                break;
                            case 'float':
                                $has_match = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_' . $db_type . ' x JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.id=x.ce_id', 'x.id', array('c_name' => $catalogue_name, 'cv_value' => floatval($key)));
                                break;
                            case 'short_trans':
                            case 'long_trans':
                                $has_match = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_' . $db_type . ' x JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.id=x.ce_id', 'x.id', array('c_name' => $catalogue_name, $GLOBALS['SITE_DB']->translate_field_ref('cv_value') => $key), '', false, array('cv_value' => strtoupper($db_type)));
                                break;
                            default:
                                $has_match = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogue_efv_' . $db_type . ' x JOIN ' . get_table_prefix() . 'catalogue_entries e ON e.id=x.ce_id', 'x.id', array('c_name' => $catalogue_name, 'cv_value' => $key));
                                break;
                        }
                        break;
                    }
                }
            }

            if (!is_null($has_match)) {
                $method = $update_handling;
            } else {
                $method = $new_handling;
            }
        }

        if ($method == 'skip') {
            $matched_ids[$has_match] = true;
            return null;
        }

        if ($method == 'delete') {
            actual_delete_catalogue_entry($has_match);
        }

        if (($method == 'overwrite') || ($method == 'freshen') || ($method == 'add')) {
            // Handle category addition
            $category_title = array_key_exists('CATEGORY', $csv_field_titles) ? $csv_data[$csv_field_titles['CATEGORY']] : '';
            if ($category_title == '') { // Have to do a general category for the catalogue
                // Checks the general category exists or not
                if (array_key_exists($catalogue_name, $categories)) {
                    $category_id = $categories[$catalogue_name];
                } else { // If category field is null, record adds to a general category named by catalogue_name.
                    $catalogue_title = $GLOBALS['SITE_DB']->query_select_value_if_there('catalogues', 'c_title', array('c_name' => $catalogue_name));

                    if (array_key_exists($catalogue_title, $categories)) {
                        $category_id = $categories[$catalogue_title];
                    } else {
                        $category_id = actual_add_catalogue_category($catalogue_name, $catalogue_title, '', '', $catalogue_root);
                        if (get_value('disable_cat_cat_perms') !== '1') {
                            $this->set_permissions(strval($category_id));
                        }

                        $categories[$catalogue_title] = $category_id;
                    }

                    $categories[$catalogue_name] = $category_id;
                }
            } elseif (array_key_exists($category_title, $categories)) {
                $category_id = $categories[$category_title];
            } else {
                $category_id = actual_add_catalogue_category($catalogue_name, $category_title, '', '', $catalogue_root);
                if (get_value('disable_cat_cat_perms') !== '1') {
                    $this->set_permissions(strval($category_id));
                }

                $categories[$category_title] = $category_id;
            }

            if (($method == 'overwrite') || ($method == 'add')) {
                // Map settings to defaults
                foreach ($map as $key => $val) {
                    if (is_null($val)) {
                        foreach ($fields as $field) {
                            if ($field['id'] == $key) {
                                $map[$key] = $field['cf_default'];
                            }
                        }
                    }
                }
            } else { // 'freshen'
                // Remove non-covered columns
                foreach ($map as $key => $val) {
                    if ((is_null($val)) || ($val == '')) {
                        unset($map[$key]);
                    }
                }
            }

            if (($method == 'overwrite') || ($method == 'freshen')) {
                actual_edit_catalogue_entry($has_match, $category_id, 1, $notes, $allow_rating ? 1 : 0, $allow_comments ? 1 : 0, $allow_trackbacks ? 1 : 0, $map, $meta_keywords, $meta_description);
                $id = $has_match;
            } else { // Add
                $id = actual_add_catalogue_entry($category_id, 1, $notes, $allow_rating ? 1 : 0, $allow_comments ? 1 : 0, $allow_trackbacks ? 1 : 0, $map);

                require_code('seo2');
                seo_meta_set_for_explicit('catalogue_entry', strval($id), $meta_keywords, $meta_description);
            }

            $matched_ids[$id] = true;

            return null;
        }

        return null;
    }

    /**
     * Set permissions of the news category from POST parameters.
     *
     * @param  ID_TEXT $id The category to set permissions for
     */
    public function set_permissions($id)
    {
        set_category_permissions_from_environment($this->permission_module, $id, $this->privilege_page);
    }
}
