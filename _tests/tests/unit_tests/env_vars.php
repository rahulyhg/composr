<?php /*

 Composr
 Copyright (c) ocProducts, 2004-2018

 See text/EN/licence.txt for full licensing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    testing_platform
 */

/**
 * Composr test case class (unit testing).
 */
class env_vars_test_set extends cms_test_case
{
    protected $bak;

    public function __construct()
    {
        unset($_GET['keep_devtest']);

        // Test assumes data is correct on this server, and is in $_SERVER -- only weird servers don't comply, and we're making sure we can support those weird servers
        $this->bak = $_SERVER;
    }

    protected function wipe_data($blankify)
    {
        foreach (array('DOCUMENT_ROOT', 'PHP_SELF', /*Derived in front controller 'SCRIPT_FILENAME', */'SCRIPT_NAME', 'REQUEST_URI', 'QUERY_STRING') as $var) {
            if ($blankify) {
                $_SERVER[$var] = '';
                $_ENV[$var] = '';
            } else {
                unset($_SERVER[$var]);
                unset($_ENV[$var]);
            }
        }
    }

    protected function default_doc_normalise($url)
    {
        return str_replace('index.php', '', $url);
    }

    public function testMissing_DOCUMENT_ROOT()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['DOCUMENT_ROOT'] == $this->bak['DOCUMENT_ROOT']);

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['DOCUMENT_ROOT'] == $this->bak['DOCUMENT_ROOT']);
    }

    public function testMissing_PHP_SELF()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['PHP_SELF'] == $this->bak['PHP_SELF']);

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['PHP_SELF'] == $this->bak['PHP_SELF']);
    }

    public function testMissing_SCRIPT_FILENAME()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['SCRIPT_FILENAME'] == $this->bak['SCRIPT_FILENAME']);

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['SCRIPT_FILENAME'] == $this->bak['SCRIPT_FILENAME']);
    }

    public function testMissing_SCRIPT_NAME()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['SCRIPT_NAME'] == $this->bak['SCRIPT_NAME']);

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['SCRIPT_NAME'] == $this->bak['SCRIPT_NAME']);
    }

    public function testMissing_REQUEST_URI()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($this->default_doc_normalise($_SERVER['REQUEST_URI']) == $this->default_doc_normalise($this->bak['REQUEST_URI']));

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($this->default_doc_normalise($_SERVER['REQUEST_URI']) == $this->default_doc_normalise($this->bak['REQUEST_URI']));
    }

    public function testMissing_QUERY_STRING()
    {
        $this->wipe_data(true);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['QUERY_STRING'] == $this->bak['QUERY_STRING']);

        $this->wipe_data(false);
        fixup_bad_php_env_vars();
        $this->assertTrue($_SERVER['QUERY_STRING'] == $this->bak['QUERY_STRING']);
    }
}
