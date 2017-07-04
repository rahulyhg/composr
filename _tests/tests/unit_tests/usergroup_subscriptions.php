<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

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
class usergroup_subscriptions_test_set extends cms_test_case
{
    public $group_id;

    public function setUp()
    {
        parent::setUp();

        require_code('ecommerce2');

        $this->group_id = add_usergroup_subscription('test', 'test', 123.00, 10.00, 12, 'y', 1, 1, 1, 1, ' ', ' ', ' ', array());

        $this->assertTrue(12 == $GLOBALS['FORUM_DB']->query_select_value('f_usergroup_subs', 's_length', array('id' => $this->group_id)));
    }

    public function testEditusergroup()
    {
        edit_usergroup_subscription($this->group_id, 'Edit usergroup subscription', 'new edit', 122.00, 10.00, 3, 'y', 1, 0, 1, 1, ' ', ' ', ' ', array());

        $this->assertTrue(3 == $GLOBALS['FORUM_DB']->query_select_value('f_usergroup_subs', 's_length', array('id' => $this->group_id)));
    }

    public function tearDown()
    {
        delete_usergroup_subscription($this->group_id, 'test@example.com');

        parent::tearDown();
    }
}
