<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2017

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/**
 * Hook class.
 */
class Hook_spam_heuristics_posting_speed
{
    /**
     * Find the confidence score for a particular spam heuristic as applied to the current context.
     *
     * @param  string $post_data Confidence score
     * @return integer Confidence score
     */
    public function assess_confidence($post_data)
    {
        $score = intval(get_option('spam_heuristic_confidence_posting_speed'));
        if ($score == 0) {
            return 0;
        }

        $csrf_token = post_param_string('csrf_token', null);
        if ($csrf_token !== null) {
            $generation_time = $GLOBALS['SITE_DB']->query_select_value_if_there('post_tokens', 'generation_time', array('token' => $csrf_token));
            if ($generation_time !== null) {
                $posted_seconds_ago = (time() - $generation_time);
                $threshold = intval(get_option('spam_heuristic_posting_speed'));
                if ($posted_seconds_ago < $threshold) {
                    return $score;
                }
            }
        }

        return 0;
    }
}
