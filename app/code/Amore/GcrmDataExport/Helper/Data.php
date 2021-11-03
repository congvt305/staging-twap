<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 3/11/21
 * Time: 08:00 PM
 */
namespace Amore\GcrmDataExport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * Helper class for export classes
 */
class Data extends AbstractHelper
{
    /**
     * Remove break line and replace it with space
     *
     * @param string $value
     * @return string
     */
    public function fixLineBreak($value = ''): string
    {
        return str_replace(["\r", "\n", "<br>", "<br/>"], '', $value);
    }

    /**
     * Get single row collection item data and fix line break issue for all columns
     *
     * @param array $itemData
     * @return mixed
     */
    public function fixSingleRowData($itemData)
    {
        foreach ($itemData as $columnName => $value) {
            $itemData[$columnName] = $this->fixLineBreak($value);
        }

        return $itemData;
    }
}
