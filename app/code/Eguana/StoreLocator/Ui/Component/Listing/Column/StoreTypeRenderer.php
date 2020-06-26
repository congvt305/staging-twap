<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 25/6/20
 * Time: 7:10 PM
 */
namespace Eguana\StoreLocator\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class StoreTypeRenderer extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['store_type'])) {
                    if ($item['store_type'] == 'RS') {
                        $item['store_type'] = 'Road Shop Store';
                    }
                    if ($item['store_type'] == 'FS') {
                        $item['store_type'] = 'Flagship Store';
                    }
                }
            }
        }

        return $dataSource;
    }
}
