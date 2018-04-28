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
class user_test_set extends cms_test_case
{
    public $member_id;
    public $access_mapping;

    public function setUp()
    {
        parent::setUp();

        require_code('cns_members_action');
        require_code('cns_members_action2');
        require_lang('cns');

        $this->member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username('testmember');
        if (!is_null($this->member_id)) {
            cns_delete_member($this->member_id);
        }

        $this->member_id = cns_make_member(
            'testmember', // username
            '123456', // password
            'test@example.com', // email_address
            null, // primary_group
            null, // secondary_groups
            10, // dob_day
            1, // dob_month
            1980, // dob_year
            array(), // custom_fields
            null, // timezone
            '', // language
            '', // theme
            '', // title
            '', // photo_url
            '', // photo_thumb_url
            null, // avatar_url
            '', // signature
            null, // preview_posts
            1, // reveal_age
            1, // views_signatures
            null, // auto_monitor_contrib_content
            null, // smart_topic_notification
            null, // mailing_list_style
            1, // auto_mark_read
            null, // sound_enabled
            1, // allow_emails
            1, // allow_emails_from_staff
            0, // highlighted_name
            '*', // pt_allow
            '', // pt_rules_text
            1, // validated
            '', // validated_email_confirm_code
            null, // on_probation_until
            0, // is_perm_banned
            true // check_correctness
        );

        $this->assertTrue('testmember' == $GLOBALS['FORUM_DB']->query_select_value('f_members', 'm_username', array('id' => $this->member_id)));
    }

    public function testEdituser()
    {
        cns_edit_member(
            $this->member_id, // member_id
            null, // username
            null, // password
            'testing@example.com', // email_address
            null // primary_group
        );

        $this->assertTrue('testing@example.com' == $GLOBALS['FORUM_DB']->query_select_value('f_members', 'm_email_address', array('id' => $this->member_id)));
    }

    public function tearDown()
    {
        cns_delete_member($this->member_id);
        parent::tearDown();
    }
}
