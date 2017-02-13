<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2016

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class shopping_order_management_test_set extends cms_test_case
{
    public $admin_ecom;
    public $item_id;
    public $order_id;
    public $access_mapping;
    public $admin_shopping;

    public function setUp()
    {
        parent::setUp();

        require_code('ecommerce');
        require_code('autosave');
        require_code('shopping');
        require_code('form_templates');

        require_lang('shopping');

        $this->order_id = $GLOBALS['SITE_DB']->query_insert('shopping_orders', array(
            'member_id' => get_member(),
            'session_id' => get_session_id(),
            'add_date' => time(),
            'order_status' => 'NEW',
            'total_price' => 10.00,
            'total_tax' => 1.00,
            'total_shipping_cost' => 2.00,
            'currency' => 'GBP',
            'notes' => '',
            'txn_id' => 'ddfsfdsdfsdfs',
            'purchase_through' => 'cart',
        ), true);

        $this->access_mapping = array(db_get_first_id() => 4);

        require_code('adminzone/pages/modules/admin_ecommerce.php');
        $this->admin_ecom = new Module_admin_ecommerce();

        require_code('adminzone/pages/modules/admin_shopping.php');
        $this->admin_shopping = new Module_admin_shopping();
        if (method_exists($this->admin_shopping, 'pre_run')) {
            $this->admin_shopping->pre_run();
        }
        $this->admin_shopping->run();
    }

    public function testShowOrders()
    {
        return $this->admin_shopping->show_orders();
    }

    public function testOrderDetails()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = strval($order_id);
        return $this->admin_shopping->order_details();
    }

    public function testAddNoteToOrderUI()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = strval($order_id);
        $this->admin_shopping->add_note();
    }

    public function testAddNoteToOrderActualiser()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_POST['order_id'] = $order_id;
        $_POST['note'] = 'Test note';
        $this->admin_shopping->_add_note();
    }

    public function testOrderDispatch()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value_if_there('shopping_orders', 'MAX(id)', array('order_status' => 'ORDER_STATUS_payment_received'));
        if (!is_null($order_id)) {
            $_GET['id'] = $order_id;
            $this->admin_shopping->dispatch();
        }
    }

    public function testOrderDispatchNotification()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $this->admin_shopping->send_dispatch_notification($order_id);
    }

    public function testDeleteOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->delete_order();
    }

    public function testReturnOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->return_order();
    }

    public function testHoldOrder()
    {
        $order_id = $GLOBALS['SITE_DB']->query_select_value('shopping_orders', 'MAX(id)');
        $_GET['id'] = $order_id;
        $this->admin_shopping->hold_order();
    }

    public function testOrderExportUI()
    {
        $this->admin_shopping->export_orders();
    }

    public function testOrderExportActualiser()
    {
        $_POST = array(
            'order_status' => 'ORDER_STATUS_awaiting_payment',
            'require__order_status' => 0,
            'start_date_day' => 10,
            'start_date_month' => 12,
            'start_date_year' => 2008,
            'start_date_hour' => 7,
            'start_date_minute' => 0,
            'require__start_date' => 1,
            'end_date_day' => 10,
            'end_date_month' => 12,
            'end_date_year' => 2009,
            'end_date_hour' => 7,
            'end_date_minute' => 0,
            'require__end_date' => 1,
            'is_from_unit_test' => 1
        );

        $this->admin_shopping->_export_orders(true);
    }

    public function tearDown()
    {
        $GLOBALS['SITE_DB']->query_delete('shopping_orders', array('id' => $this->order_id), '', 1);
        parent::tearDown();
    }
}
