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

/*
Note that we generally prefer JSON in Composr.
However in some cases legacy APIs may force us to use XML-RPC.
*/

/**
 * Do a highly-simplified XML-RPC request (no actual calling supported - just messaging).
 *
 * @param  URLPATH $url The XML-RPC call URL
 * @param  string $method The method name to call.
 * @param  array $params An array of parameters.
 * @param  boolean $accept_failure Whether to accept failure.
 * @return ?string The result (null: failed).
 */
function xml_rpc($url, $method, $params, $accept_failure = false)
{
    require_code('xml');

    $rpc = "
<" . "?xml version=\"1.0\"?" . ">
<methodCall>
 <methodName>{$method}</methodName>
 <params>
";
    foreach ($params as $_value) {
        $value = _xml_rpc_type_convert($_value);
        $rpc .= <<<END

      <param>
            <value>{$value}</value>
      </param>
END;
    }
    $rpc .= <<<END

 </params>
</methodCall>
END;

    $result = http_get_contents($url, array('post_params' => array($rpc), 'raw_post' => true, 'raw_content_type' => 'text/xml'));
    return $result;
}

/**
 * Convert some data to XML-RPC format.
 *
 * @param  mixed $_value Data
 * @return string XML-RPC format version
 *
 * @ignore
 */
function _xml_rpc_type_convert($_value)
{
    switch (gettype($_value)) {
        case 'boolean':
            $value = '<boolean>' . ($_value ? '1' : '0') . '</boolean>';
            break;
        case 'array':
            $keys = array_keys($_value);
            if ((count($_value) > 0) && (!is_integer(array_pop($keys)))) {
                $value = '<struct>';
                foreach ($_value as $k => $v) {
                    $value .= '<name>' . $k . '</name><value>' . _xml_rpc_type_convert($v) . '</value>';
                }
                $value .= '</struct>';
            } else {
                $value = '<array><data>';
                foreach ($_value as $v) {
                    $value .= '<value>' . _xml_rpc_type_convert($v) . '</value>';
                }
                $value .= '</data></array>';
            }
            break;
        case 'object':
            $value = '<string>' . xmlentities($_value->evaluate()) . '</string>';
            break;
        case 'integer':
            $value = '<i4>' . strval($_value) . '</i4>';
            break;
        case 'float':
            $value = '<double>' . float_to_raw_string($_value) . '</double>';
            break;
        case 'NULL':
            $value = '<nil/>';
            break;
        default:
            $value = '<string>' . xmlentities(strval($_value)) . '</string>';
            break;
    }
    return $value;
}
