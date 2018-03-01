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

// Based on http://stackoverflow.com/questions/8763647/is-there-a-way-to-check-if-a-gif-image-has-animation-with-php-or-java
function is_ani($filename)
{
    $filecontents = file_get_contents($filename);

    $str_loc = 0;
    $count = 0;
    while ($count < 2) { // There is no point in continuing after we find a 2nd frame
        $where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);
        if ($where1 === false) {
            break;
        } else {
            $str_loc = $where1 + 1;
            $where2 = strpos($filecontents, "\x00\x2C", $str_loc);
            if ($where2 === false) {
                break;
            } else {
                if ($where1 + 8 == $where2) {
                    $count++;
                }
                $str_loc = $where2 + 1;
            }
        }
    }

    if ($count > 1) {
        return true;
    }
    return false;
}

/**
 * Composr test case class (unit testing).
 */
class image_compression_test_set extends cms_test_case
{
    public function testImageCompression()
    {
        // This test is not great, as some files just don't compress well. But it does pick up Photoshops terrible lack of compression and storage of metadata

        require_code('images');
        require_code('themes2');

        $themes = find_all_themes();
        foreach (array_keys($themes) as $theme) {
            if ($theme == '_unnamed_') {
                continue;
            }

            foreach (array('images', 'images_custom') as $dir) {
                $base = get_file_base() . '/themes/' . $theme . '/' . $dir;
                require_code('files2');
                $files = get_directory_contents($base, '', IGNORE_SHIPPED_VOLATILE | IGNORE_UNSHIPPED_VOLATILE | IGNORE_FLOATING | IGNORE_CUSTOM_THEMES);
                foreach ($files as $path) {
                    if ((!is_image($path, IMAGE_CRITERIA_WEBSAFE | IMAGE_CRITERIA_GD_READ)) || (substr($path, -8) == '.gif.png')) {
                        continue;
                    }

                    $filesize = filesize($base . '/' . $path);

                    // Approximate base size
                    if (substr($path, -4) == '.gif') {
                        $filesize -= 800; // For the palette (not in all gifs, but needed for non-trivial ones)
                        $min_ratio = 0.8;
                        if (is_ani($base . '/' . $path)) {
                            continue; // Can't do animated gifs
                        }
                    } else {
                        $filesize -= 73;
                        $min_ratio = 0.28;
                    }
                    if ($filesize < 1) {
                        $filesize = 1;
                    }

                    list($width, $height) = cms_getimagesize($base . '/' . $path);
                    $area = $width * $height;
                    $this->assertTrue(floatval($area) / floatval($filesize) > $min_ratio, 'Rubbish compression density on ' . $path . ' theme image');
                }
            }
        }
    }
}
