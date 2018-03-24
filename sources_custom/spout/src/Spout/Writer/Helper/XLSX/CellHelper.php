<?php

namespace Box\Spout\Writer\Helper\XLSX;

/**
 * Class CellHelper
 * This class provides helper functions when working with cells
 *
 * @package Box\Spout\Writer\Helper\XLSX
 */
class CellHelper
{
    /**
     * Returns the cell index (base 26) associated to the base 10 column index.
     * Excel uses A to Z letters for column indexing, where A is the 1st column,
     * Z is the 26th and AA is the 27th.
     * The mapping is zero based, so that 0 maps to A, B maps to 1, Z to 25 and AA to 26.
     *
     * @param int $columnIndex The Excel column index (0, 42, ...)
     * @return string The associated cell index ('A', 'BC', ...)
     */
    public static function getCellIndexFromColumnIndex($columnIndex)
    {
        $cellIndex = '';
        $capitalAAsciiValue = ord('A');

        do {
            $modulus = $columnIndex % 26;
            $cellIndex = chr($capitalAAsciiValue + $modulus) . $cellIndex;

            // substracting 1 because it's zero-based
            $columnIndex = intval($columnIndex / 26) - 1;
        } while ($columnIndex >= 0);

        return $cellIndex;
    }
}