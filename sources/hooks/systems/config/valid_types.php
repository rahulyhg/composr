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
 * @package    core_configuration
 */

/**
 * Hook class.
 */
class Hook_config_valid_types
{
    /**
     * Gets the details relating to the config option.
     *
     * @return ?array The details (null: disabled)
     */
    public function get_details()
    {
        return array(
            'human_name' => 'FILE_TYPES',
            'type' => 'line',
            'category' => 'SECURITY',
            'group' => 'UPLOADED_FILES',
            'explanation' => 'CONFIG_OPTION_valid_types',
            'shared_hosting_restricted' => '0',
            'list_options' => '',
            'required' => true,

            'public' => true,

            'addon' => 'core_configuration',
        );
    }

    /**
     * Gets the default value for the config option.
     *
     * @return ?string The default value (null: option is disabled)
     */
    public function get_default()
    {
        return '1st,3g2,3gp,3gp2,3gpp,3p,7z,aac,ai,aif,aifc,aiff,asf,atom,avi,bmp,bz2,css,csv,cur,dat,diff,doc,docx,dot,dotx,eml,f4v,gif,gz,htm,html,ico,ics,ini,iso,jpe,jpeg,jpg,js,json,keynote,log,m2v,m4v,mdb,mid,mov,mp2,mp3,mp4,mpa,mpe,mpeg,mpg,mpv2,numbers,odb,odc,odg,odi,odp,ods,odt,ogg,ogv,otf,pages,patch,pdf,php,png,ppt,pptx,ps,psd,pub,qt,ra,ram,rar,rm,rss,rtf,sql,svg,tar,tga,tgz,tif,tiff,torrent,tpl,ttf,txt,vsd,vtt,wav,weba,webm,webp,woff,wma,wmv,xls,xlsx,xml,xsd,xsl,zip';
    }
}
