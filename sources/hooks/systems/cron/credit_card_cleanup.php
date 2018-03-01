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
 * @package    ecommerce
 */

/**
 * Hook class.
 */
class Hook_cron_credit_card_cleanup
{
    protected $threshold;
    protected $card_number_field_id;

    /**
     * Get info from this hook.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     * @param  boolean $calculate_num_queued Calculate the number of items queued, if possible
     * @return ?array Return a map of info about the hook (null: disabled)
     */
    public function info($last_run, $calculate_num_queued)
    {
        if (get_forum_type() != 'cns') {
            return null;
        }

        if ($calculate_num_queued) {
            $credit_card_cleanup_days = get_option('credit_card_cleanup_days');

            if ($credit_card_cleanup_days === null) {
                return null;
            }

            require_code('cns_members');
            $this->card_number_field_id = find_cms_cpf_field_id('cms_payment_card_number');
            if ($this->card_number_field_id === null) {
                return null;
            }

            $this->threshold = time() - 60 * 60 * 24 * intval($credit_card_cleanup_days);

            $where = 'm_last_visit_time<' . strval($this->threshold) . ' AND ' . db_string_not_equal_to('field_' . strval($this->card_number_field_id), '');
            $num_queued = $GLOBALS['FORUM_DB']->query_value_if_there(
                'SELECT COUNT(*) FROM ' .
                $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_members m JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_member_custom_fields f ON f.mf_member_id=m.id ' .
                'WHERE ' . $where
            );
        } else {
            $num_queued = null;
        }

        return array(
            'label' => 'Credit card number scrubbing',
            'num_queued' => $num_queued,
            'minutes_between_runs' => 60 * 24,
        );
    }

    /**
     * Run function for system scheduler scripts. Searches for things to do. ->info(..., true) must be called before this method.
     *
     * @param  ?TIME $last_run Last time run (null: never)
     */
    public function run($last_run)
    {
        $credit_card_cleanup_days = get_option('credit_card_cleanup_days');

        $protected_field_changes = array();
        $protected_field_names = array('payment_cardholder_name', 'payment_card_type', 'payment_card_number', 'payment_card_start_date', 'payment_card_expiry_date', 'payment_card_issue_number', 'billing_street_address', 'billing_city', 'billing_post_code', 'billing_country', 'billing_mobile_phone_number', 'billing_county', 'billing_state');
        foreach ($protected_field_names as $cpf) {
            $field_id = find_cms_cpf_field_id('cms_' . $cpf);
            if ($field_id !== null) {
                $protected_field_changes['field_' . strval($field_id)] = (($cpf == 'payment_card_number' || $cpf == 'payment_card_issue_number') ? null : '');
            }
        }

        $where = 'm_last_visit_time<' . strval($this->threshold);
        $GLOBALS['FORUM_DB']->query_update(
            'f_members m JOIN ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_member_custom_fields f ON f.mf_member_id=m.id AND ' . $where,
            $protected_field_changes
        );
    }
}
