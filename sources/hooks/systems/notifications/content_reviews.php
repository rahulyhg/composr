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
 * @package    content_reviews
 */

/**
 * Hook class.
 */
class Hook_notification_content_reviews extends Hook_notification__Staff
{
    /**
     * Find the initial setting that members have for a notification code (only applies to the member_could_potentially_enable members).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?SHORT_TEXT $category The category within the notification code (null: none)
     * @return integer Initial setting
     */
    public function get_initial_setting($notification_code, $category = null)
    {
        return A_NA;
    }

    /**
     * Find whether a handled notification code supports categories.
     * (Content types, for example, will define notifications on specific categories, not just in general. The categories are interpreted by the hook and may be complex. E.g. it might be like a regexp match, or like FORUM:3 or TOPIC:100).
     *
     * @param  ID_TEXT $notification_code Notification code
     * @return boolean Whether it does
     */
    public function supports_categories($notification_code)
    {
        return true;
    }

    /**
     * Standard function to create the standardised category tree.
     *
     * @param  ID_TEXT $notification_code Notification code
     * @param  ?ID_TEXT $id The ID of where we're looking under (null: N/A)
     * @return array Tree structure
     */
    public function create_category_tree($notification_code, $id)
    {
        $page_links = array();

        $_hooks = find_all_hooks('systems', 'content_meta_aware');
        foreach (array_keys($_hooks) as $content_type) {
            require_code('content');
            $object = get_content_object($content_type);
            if ($object === null) {
                continue;
            }
            $info = $object->info();
            if ($info === null) {
                continue;
            }

            $lang = do_lang($info['content_type_label'], null, null, null, null, false);
            if ($lang === null) {
                continue;
            }

            $page_links[] = array(
                'id' => $content_type,
                'title' => $lang,
            );
        }

        sort_maps_by($page_links, 'title');

        return $page_links;
    }

    /**
     * Get a list of all the notification codes this hook can handle.
     * (Addons can define hooks that handle whole sets of codes, so hooks are written so they can take wide authority).
     *
     * @return array List of codes (mapping between code names, and a pair: section and labelling for those codes)
     */
    public function list_handled_codes()
    {
        $list = array();
        $list['content_reviews'] = array(do_lang('CONTENT'), do_lang('content_reviews:NOTIFICATION_TYPE_content_reviews'));
        $list['content_reviews__own'] = array(do_lang('CONTENT'), do_lang('content_reviews:NOTIFICATION_TYPE_content_reviews__own'));
        return $list;
    }
}
