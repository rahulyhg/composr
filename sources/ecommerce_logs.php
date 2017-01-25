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
 * @package    ecommerce
 */

/**
 * The UI to view sales logs.
 *
 * @param  ?MEMBER $filter_member_id Member to filter by (null: none)
 * @param  boolean $show_username Whether to show the username column
 * @param  boolean $show_username Whether to show the deletion column
 * @param  integer $max_default Default maximum number of records to show
 * @return array A pair: The sales table, pagination
 */
function build_sales_table($filter_member_id, $show_username = false, $show_delete = false, $max_default = 20)
{
    require_lang('ecommerce');

    require_code('templates_map_table');
    require_code('content');
    require_code('ecommerce');

    $max = get_param_integer('max_ecommerce_logs', $max_default);
    $start = get_param_integer('start_ecommerce_logs', 0);

    $permission_product_rows = list_to_map('id', $GLOBALS['SITE_DB']->query_select('ecom_prods_permissions', array('id', 'p_module', 'p_category')));

    $where = array();
    if ($filter_member_id !== null) {
        $where['member_id'] = $filter_member_id;
    }

    $rows = $GLOBALS['SITE_DB']->query_select('ecom_sales s JOIN ' . get_table_prefix() . 'ecom_transactions t ON t.id=s.transaction_id', array('*', 's.id AS s_id', 't.id AS t_id'), $where, 'ORDER BY date_and_time DESC', $max, $start);
    $max_rows = $GLOBALS['SITE_DB']->query_select_value('ecom_sales', 'COUNT(*)', $where);

    $sales_rows = array();
    require_code('templates_results_table');
    require_code('templates_columned_table');
    foreach ($rows as $row) {
        if ($show_username) {
            $member_link = $GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($row['member_id']);
        }

        list($found,) = find_product_details($row['t_type_code']);
        if ($found !== null) {
            $item_name = $found['item_name'];
        }

        $item_link = make_string_tempcode(escape_html($item_name));
        $matches = array();
        if (preg_match('#^PERMISSION\_(\d+)$#', $row['t_type_code'], $matches) != 0) {
            $permission_product_id = intval($matches[1]);
            if (isset($permission_product_rows[$permission_product_id])) {
                $module = $permission_product_rows[$permission_product_id]['p_module'];
                if ($module != '') {
                    $category_id = $permission_product_rows[$permission_product_id]['p_category'];

                    $resource_type = convert_composr_type_codes('module', $module, 'content_type');
                    if ($resource_type != '') {
                        $content_type_ob = get_content_object($resource_type);
                        $cma_info = $content_type_ob->info();
                        if (!$cma_info['is_category']) {
                            $resource_type = $cma_info['parent_category_meta_aware_type'];
                        }

                        list(, , $cma_info) = content_get_details($resource_type, $category_id);

                        $page_link = str_replace('_WILD', $category_id, $cma_info['view_page_link_pattern']);
                        $item_link = hyperlink(page_link_to_url($page_link), $item_name, false, true);
                    }
                }
            }
        }

        if (strpos($item_name, $row['details']) === false) {
            $details_1 = $row['details'];
            if (strpos($item_name, $row['details2']) === false) {
                $details_2 = $row['details2'];
            } else {
                $details_2 = '';
            }
        } else {
            $details_1 = $row['details2'];
            $details_2 = '';
        }

        $date = get_timezoned_date($row['date_and_time']);

        $transaction_fields = array(
            'TRANSACTION' => $row['t_id'],
            'IDENTIFIER' => $row['t_purchase_id'],
            'LINKED_ID' => $row['t_parent_txn_id'],
            'AMOUNT' => $row['t_amount'],
            'CURRENCY' => $row['t_currency'],
            'STATUS' => $row['t_status'],
            'REASON' => $row['t_reason'],
            'PENDING_REASON' => $row['t_pending_reason'],
            'NOTES' => $row['t_memo'],
        );
        $_transaction_fields = new Tempcode();
        foreach ($transaction_fields as $key => $val) {
            if ($val != '') {
                $_transaction_fields->attach(map_table_field(do_lang_tempcode($key), $val));
            }
        }
        $map_table = do_template('MAP_TABLE', array('FIELDS' => $_transaction_fields));
        $date_with_tooltip = do_template('CROP_TEXT_MOUSE_OVER', array('TEXT_LARGE' => $map_table, 'TEXT_SMALL' => escape_html($date)));

        if ($show_delete) {
            $url = build_url(array('page' => 'admin_ecommerce_logs', 'type' => 'delete_sales_log_entry', 'id' => $row['s_id']), '_SEARCH');
            $actions = do_template('COLUMNED_TABLE_ACTION_DELETE_ENTRY', array('_GUID' => '12e3ea365f1a1ed2e7800293f3203283', 'NAME' => '#' . strval($row['s_id']), 'URL' => $url));
        }

        $sales_row = array();
        if ($show_username) {
            $sales_row[] = $member_link;
        }
        $sales_row[] = $item_link;
        $sales_row[] = $details_1;
        $sales_row[] = $details_2;
        $sales_row[] = protect_from_escaping($date_with_tooltip->evaluate());
        if ($show_delete) {
            $sales_row[] = $actions;
        }

        $sales_rows[] = $sales_row;
    }
    if (count($sales_rows) == 0) {
        return inform_screen($this->title, do_lang_tempcode('NO_ENTRIES'));
    }

    $header_row = array();
    if ($show_username) {
        $header_row[] = do_lang_tempcode('USERNAME');
    }
    $header_row[] = do_lang_tempcode('PRODUCT');
    $header_row[] = do_lang_tempcode('DETAILS');
    $header_row[] = do_lang_tempcode('OTHER_DETAILS');
    $header_row[] = do_lang_tempcode('DATE_TIME');
    if ($show_delete) {
        $header_row[] = do_lang_tempcode('ACTIONS');
    }
    $_header_row = columned_table_header_row($header_row);

    $_sales_rows = new Tempcode();
    foreach ($sales_rows as $sales_row) {
        $_sales_rows->attach(columned_table_row($sales_row, true));
    }

    $sales_table = do_template('COLUMNED_TABLE', array('_GUID' => 'd87800ff26e9e5b8f7593fae971faa73', 'HEADER_ROW' => $_header_row, 'ROWS' => $_sales_rows));

    require_code('templates_pagination');
    $pagination = pagination(do_lang('ECOM_PRODUCTS_MANAGE_SALES'), $start, 'start_ecommerce_logs', $max, 'max_ecommerce_logs', $max_rows, false, 5, null, 'tab__ecommerce_logs');

    return array($sales_table, $pagination);
}
