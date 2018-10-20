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
 * @package    core_feedback_features
 */

/**
 * Block class.
 */
class Block_main_comments
{
    /**
     * Find details of the block.
     *
     * @return ?array Map of block info (null: block is disabled)
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'page', 'reverse', 'forum', 'invisible_if_no_comments', 'reviews', 'max', 'title', 'explicit_allow');
        return $info;
    }

    /**
     * Find caching details for the block.
     *
     * @return ?array Map of cache details (cache_on and ttl) (null: block is disabled)
     */
    /*
    public function caching_environment() // We can't cache this block, because it needs to execute in order to allow commenting
    {
        $info['cache_on'] = 'array(((array_key_exists(\'max\',$map)) && ($map[\'max\']!='-1'))?intval($map[\'max\']):null,((!array_key_exists(\'reviews\',$map)) || ($map[\'reviews\']==\'1\')),has_privilege(get_member(),\'comment\'),array_key_exists(\'param\',$map)?$map[\'param\']:\'main\',array_key_exists(\'page\',$map)?$map[\'page\']:get_page_name(),array_key_exists(\'forum\',$map)?$map[\'forum\']:null,((array_key_exists(\'invisible_if_no_comments\',$map)) && ($map[\'invisible_if_no_comments\']==\'1\')),((array_key_exists(\'reverse\',$map)) && ($map[\'reverse\']==\'1\')),array_key_exists(\'title\',$map)?$map[\'title\']:\'\',(array_key_exists(\'explicit_allow\', $map)) ? ($map[\'explicit_allow\'] == \'1\') : false)';
        $info['ttl'] = 60 * 5;
        return $info;
    }*/

    /**
     * Execute the block.
     *
     * @param  array $map A map of parameters
     * @return Tempcode The result of execution
     */
    public function run($map)
    {
        $content_type = 'block_main_comments';
        $param = array_key_exists('param', $map) ? $map['param'] : 'main';
        $page = array_key_exists('page', $map) ? $map['page'] : get_page_name();
        $id = $page . '_' . $param;

        $explicit_allow = (array_key_exists('explicit_allow', $map)) ? ($map['explicit_allow'] == '1') : false;

        require_code('feedback');

        $block_id = md5(serialize($map));
        $submitted = ((post_param_integer('_comment_form_post', 0) == 1) && (post_param_string('_block_id', '') == $block_id));

        $self_url = build_url(array('page' => '_SELF'), '_SELF', array(), true, false, true);
        $self_title = empty($map['title']) ? $page : $map['title'];
        $test_changed = post_param_string('title', null);
        if ($test_changed !== null) {
            delete_cache_entry('main_comments');
        }
        $is_hidden = $submitted ? actualise_post_comment(true, $content_type, $id, $self_url, $self_title, array_key_exists('forum', $map) ? $map['forum'] : null, true, null, $explicit_allow) : false;

        if ((array_key_exists('title', $_POST)) && ($is_hidden) && ($submitted)) {
            attach_message(do_lang_tempcode('MESSAGE_POSTED'), 'inform');

            if (get_forum_type() == 'cns') {
                if (addon_installed('unvalidated')) {
                    require_code('submit');
                    $validate_url = get_self_url(true, false, array('keep_session' => null));
                    $_validate_url = build_url(array('page' => 'topics', 'type' => 'validate_post', 'id' => $GLOBALS['LAST_POST_ID'], 'redirect' => protect_url_parameter($validate_url)), get_module_zone('topics'), array(), false, false, true);
                    $validate_url = $_validate_url->evaluate();
                    send_validation_request('MAKE_POST', 'f_posts', false, $GLOBALS['LAST_POST_ID'], $validate_url);
                }
            }
        }

        $invisible_if_no_comments = ((array_key_exists('invisible_if_no_comments', $map)) && ($map['invisible_if_no_comments'] == '1'));
        $reverse = ((array_key_exists('reverse', $map)) && ($map['reverse'] == '1'));
        $allow_reviews = ((array_key_exists('reviews', $map)) && ($map['reviews'] == '1'));
        $num_to_show_limit = ((array_key_exists('max', $map)) && ($map['max'] != '-1')) ? intval($map['max']) : null;

        $hidden = new Tempcode();
        $hidden->attach(form_input_hidden('_block_id', $block_id));

        return get_comments($content_type, true, $id, $invisible_if_no_comments, array_key_exists('forum', $map) ? $map['forum'] : null, null, null, $explicit_allow, $reverse, null, $allow_reviews, $num_to_show_limit, $hidden);
    }
}
