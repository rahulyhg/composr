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
 * @package    shopping
 */

/**
 * Hook class.
 */
class Hook_task_export_shopping_orders
{
    /**
     * Run the task hook.
     *
     * @param  TIME $start_date Date from
     * @param  TIME $end_date Date to
     * @param  string $order_status Order status filter (blank: no filter)
     * @return ?array A tuple of at least 2: Return mime-type, content (either Tempcode, or a string, or a filename and file-path pair to a temporary file), map of HTTP headers if transferring immediately, map of ini_set commands if transferring immediately (null: show standard success message)
     */
    public function run($start_date, $end_date, $order_status)
    {
        $filename = 'orders_' . (($order_status == '') ? '' : ($order_status . '__')) . get_timezoned_date($start_date, false, false, false, true) . '-' . get_timezoned_date($end_date, false, false, false, true) . '.csv';

        $orders = array();
        $data = array();

        $where = 'add_date BETWEEN ' . strval($start_date) . ' AND ' . strval($end_date);
        if ($order_status != '') {
            $where .= ' AND ' . db_string_equal_to('order_status', $order_status);
        }

        $query = 'SELECT o.*,o.id AS o_id,a.*
            FROM ' . get_table_prefix() . 'shopping_orders o
            LEFT JOIN ' . get_table_prefix() . 'ecom_trans_addresses a ON o.txn_id=a.a_txn_id
            WHERE ' . $where . '
            ORDER BY add_date';
        $rows = $GLOBALS['SITE_DB']->query($query);
        remove_duplicate_rows($rows);

        foreach ($rows as $_order) {
            $order = array();

            $order[do_lang('ORDER_NUMBER')] = strval($_order['o_id']);

            $order[do_lang('ORDERED_DATE')] = get_timezoned_date($_order['add_date'], true, false, true, true);

            $order[do_lang('ORDER_STATUS')] = do_lang($_order['order_status']);

            $order[do_lang('PRICE')] = $_order['total_price'];

            $order[do_lang(get_option('tax_system'))] = float_format($_order['total_tax']);

            $order[do_lang('SHIPPING_COST')] = float_format($_order['total_shipping_cost']);

            $order[do_lang('ORDERED_PRODUCTS')] = get_ordered_product_list_string($_order['o_id']);

            $order[do_lang('ORDERED_BY')] = $GLOBALS['FORUM_DRIVER']->get_username($_order['member_id']);
            if ($order[do_lang('ORDERED_BY')] === null) {
                $order[do_lang('ORDERED_BY')] = do_lang('UNKNOWN');
            }

            // Put address together
            $address = array();
            if ($_order['a_firstname'] . $_order['a_lastname'] != '') {
                $address[] = trim($_order['a_firstname'] . ' ' . $_order['a_lastname']);
            }
            if ($_order['a_street_address'] != '') {
                $address[] = $_order['a_street_address'];
            }
            if ($_order['a_city'] != '') {
                $address[] = $_order['a_city'];
            }
            if ($_order['a_county'] != '') {
                $address[] = $_order['a_county'];
            }
            if ($_order['a_state'] != '') {
                $address[] = $_order['a_state'];
            }
            if ($_order['a_post_code'] != '') {
                $address[] = $_order['a_post_code'];
            }
            if ($_order['a_country'] != '') {
                $address[] = $_order['a_country'];
            }
            if ($_order['a_email'] != '') {
                $address[] = do_lang('EMAIL_ADDRESS') . ': ' . $_order['a_email'];
            }
            if ($_order['a_phone'] != '') {
                $address[] = do_lang('PHONE_NUMBER') . ': ' . $_order['a_phone'];
            }
            $full_address = implode("\n", $address);
            $order[do_lang('SHIPPING_ADDRESS')] = $full_address;

            $data[] = $order;
        }

        $headers = array();
        $headers['Content-type'] = 'text/csv';
        $headers['Content-Disposition'] = 'attachment; filename="' . escape_header($filename) . '"';

        $ini_set = array();
        $ini_set['ocproducts.xss_detect'] = '0';

        require_code('files2');
        $outfile_path = cms_tempnam();
        make_csv($data, $filename, false, false, $outfile_path);
        return array('text/csv', array($filename, $outfile_path), $headers, $ini_set);
    }
}
