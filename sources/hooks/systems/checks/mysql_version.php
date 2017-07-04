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
class Hook_check_mysql_version
{
    /**
     * Check various input var restrictions.
     *
     * @return array List of warnings
     */
    public function run()
    {
        $minimum_version = '5.5.3'; // also maintain in tut_webhosting.txt
        // ^ Why? We need this for proper Unicode support: https://dev.mysql.com/doc/refman/5.5/en/charset-unicode-utf8mb4.html

        // If you really need to fiddle it and don't care about emoji, add this to _config.php while installing (before step 5 runs):   $SITE_INFO['database_charset'] = 'utf8';

        $warning = array();

        $version = null;

        if (isset($GLOBALS['SITE_DB']->connection_read[0])) {
            if (function_exists('mysqli_get_server_version') && get_db_type() == 'mysqli') {
                $__version = @mysqli_get_server_version($GLOBALS['SITE_DB']->connection_read[0]);
                if ($__version !== false) {
                    $_version = strval($__version);
                    $version = strval(intval(substr($_version, 0, strlen($_version) - 4))) . '.' . strval(intval(substr($_version, -4, 2))) . '.' . strval(intval(substr($_version, -2, 2)));
                }
            } elseif (function_exists('mysql_get_server_info') && get_db_type() == 'mysql') {
                $_version = @mysql_get_server_info($GLOBALS['SITE_DB']->connection_read[0]);
                if ($_version !== false) {
                    $version = $_version;
                }
            }
        }

        if ($version !== null) {
            if (version_compare($version, $minimum_version, '<')) {
                $warning[] = do_lang_tempcode('MYSQL_TOO_OLD', escape_html($minimum_version), escape_html($version));
            }

            $max_tested_mysql_version = '5.7';
            if ((!is_maintained('mysql')) && (version_compare($version, $max_tested_mysql_version . '.1000', '>'))) {
                $warning[] = do_lang_tempcode('WARNING_NON_MAINTAINED', escape_html('MySQL versions newer than ' . $max_tested_mysql_version), escape_html(get_brand_base_url()), escape_html('mysql'));
            }
        }

        return $warning;
    }
}
