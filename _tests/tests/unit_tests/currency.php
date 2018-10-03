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
class currency_test_set extends cms_test_case
{
    public function setUp()
    {
        parent::setUp();

        require_code('currency');
    }

    public function testCountryToCurrency()
    {
        $this->assertTrue(country_to_currency('GB') == 'GBP');
    }

    public function testTypesOk()
    {
        $test_a = currency_convert(10.00, 'USD', 'GBP');
        $test_b = currency_convert(10, 'USD', 'GBP');
        $this->assertTrue(gettype($test_a) == gettype($test_b) && $test_a > $test_b - 0.005 && $test_b > $test_a - 0.005, 'Got ' . serialize($test_a) . ' and ' . serialize($test_b)); // Floats and integers should convert the same
    }

    public function testCurrencyViaConvAPI()
    {
        $test = currency_convert(100.00, 'MMK', 'GBP', 0, 'conv_api');
        $this->assertTrue($test > 0.00, 'Expected GBP value to be more than 0.00, got ' . float_format($test, 2));
        $this->assertTrue($test < 100.00, 'Expected GBP value to be less than 10.00, got ' . float_format($test, 2)); // GBP is worth *much* more
    }
}
