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
 * @package    polls
 */

/**
 * Hook class.
 */
class Hook_realtime_rain_polls
{
    /**
     * Run function for realtime-rain hooks.
     *
     * @param  TIME $from Start of time range.
     * @param  TIME $to End of time range.
     * @return array A list of template parameter sets for rendering a 'drop'.
     */
    public function run($from, $to)
    {
        $drops = array();

        if (has_actual_page_access(get_member(), 'polls')) {
            $rows = $GLOBALS['SITE_DB']->query('SELECT b.option1,b.option2,b.option3,b.option4,b.option5,b.option6,b.option7,b.option8,b.option9,b.option10,b.votes1,b.votes2,b.votes3,b.votes4,b.votes5,b.votes6,b.votes7,b.votes8,b.votes9,b.votes10,b.question,b.id,b.submitter AS member_id,a.date_and_time AS timestamp FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'poll a LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'poll b ON a.date_and_time>b.date_and_time WHERE NOT EXISTS(SELECT * FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'poll x WHERE x.id<>a.id AND x.id<>b.id AND x.date_and_time BETWEEN b.date_and_time AND a.date_and_time) AND b.date_and_time IS NOT NULL AND b.date_and_time BETWEEN ' . strval($from) . ' AND ' . strval($to));

            foreach ($rows as $row) {
                require_lang('polls');

                $timestamp = $row['timestamp'];
                $member_id = $row['member_id'];

                $best = null;
                $best_num = -1;
                for ($i = 1; $i <= 10; $i++) {
                    if ($row['votes' . strval($i)] > $best_num) {
                        $best = $row['option' . strval($i)];
                        $best_num = $row['votes' . strval($i)];
                    }
                }

                if ($best_num == -1) {
                    continue;
                }

                $ticker_text = do_lang('VOTES_ARE_IN', strip_comcode(get_translated_text($row['question'])), strip_comcode(get_translated_text($best)));

                $drops[] = rain_get_special_icons(null, $timestamp, null, $ticker_text) + array(
                        'TYPE' => 'polls',
                        'FROM_MEMBER_ID' => strval($member_id),
                        'TO_MEMBER_ID' => null,
                        'TITLE' => rain_truncate_for_title(get_translated_text($row['question'])),
                        'IMAGE' => find_theme_image('icons/48x48/menu/social/polls'),
                        'TIMESTAMP' => strval($timestamp),
                        'RELATIVE_TIMESTAMP' => strval($timestamp - $from),
                        'TICKER_TEXT' => $ticker_text,
                        'URL' => build_url(array('page' => 'polls', 'type' => 'view', 'id' => $row[1]['id']), get_module_zone('polls')),
                        'IS_POSITIVE' => false,
                        'IS_NEGATIVE' => false,

                        // These are for showing connections between drops. They are not discriminated, it's just three slots to give an ID code that may be seen as a commonality with other drops.
                        'FROM_ID' => 'member_' . strval($member_id),
                        'TO_ID' => null,
                        'GROUP_ID' => 'poll_' . strval($row['id']),
                    );
            }
        }

        return $drops;
    }
}
