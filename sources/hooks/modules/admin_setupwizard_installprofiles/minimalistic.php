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
 * @package    setupwizard
 */

/**
 * Hook class.
 */
class Hook_admin_setupwizard_installprofiles_minimalistic
{
    /**
     * Get info about the installprofile.
     *
     * @return ?array Map of installprofile details (null: profile is unavailable)
     */
    public function info()
    {
        if (!addon_installed('setupwizard')) {
            return null;
        }

        return array(
            'title' => do_lang('MINIMALISTIC_SITE'),
        );
    }

    /**
     * Get a list of addons that are kept with this installation profile (added to the list of addons always kept).
     *
     * @return array Triple: List of addons in the profile, Separated list of ones to show under advanced, Ones we really are shunning
     */
    public function get_addon_list()
    {
        return array(
            array(),
            array(),
            array(
                'banners',
                'ecommerce',
                'shopping',
                'cns_avatars',
                'cns_cartoon_avatars',
                'cns_member_avatars',
                'cns_thematic_avatars',
                'wordfilter',
                'awards',
                'counting_blocks',
                'help_page',
                'language_block',
                'printer_friendly_block',
                'search',
                'stats',
                'stats_block',
                'syndication',
                'syndication_blocks',
                'themewizard',
                'users_online_block',
                'tickets',
            ),
        );
    }

    /**
     * Get a map of default settings associated with this installation profile.
     *
     * @return array Map of default settings
     */
    public function field_defaults()
    {
        return array(
            'have_default_banners_hosting' => '0',
            'have_default_banners_donation' => '0',
            'have_default_banners_advertising' => '0',
            'have_default_catalogues_projects' => '0',
            'have_default_catalogues_faqs' => '0',
            'have_default_catalogues_links' => '0',
            'have_default_catalogues_contacts' => '0',
            'keep_personal_galleries' => '0',
            'keep_news_categories' => '0',
            'keep_blogs' => '0',
            'have_default_rank_set' => '0',
            'have_default_cpf_set' => '0',
            'have_default_full_emoticon_set' => '0',
            'show_content_tagging' => '0',
            'show_content_tagging_inline' => '0',
            'show_screen_actions' => '0',
            'have_default_wordfilter' => '0',
            'rules' => 'corporate',
        );
    }

    /**
     * Find details of desired blocks.
     *
     * @return array Details of what blocks are wanted
     */
    public function default_blocks()
    {
        // Usually these won't be here (except side_personal_stats), because the addons won't have been installed -- but if they were, we do want to represent those block-driven addons
        return array(
            'YES' => array(
                'main_news',
            ),
            'YES_CELL' => array(),
            'PANEL_LEFT' => array(),
            'PANEL_RIGHT' => array(
                'main_newsletter_signup',
                'main_poll',
            ),
        );
    }

    /**
     * Get options for blocks in this profile.
     *
     * @return array Details of what block options are wanted
     */
    public function block_options()
    {
        return array();
    }

    /**
     * Execute any special code needed to put this install profile into play.
     */
    public function install_code()
    {
    }
}
