<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 5/11/20
 * Time: 9:07 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Counter\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * To change start & end time of counter list
 *
 * Class StartEndTime
 */
class StartEndTime extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item['start_time']) {
                    $item['start_time'] = date('H:i', strtotime($item['start_time']));
                }

                if ($item['end_time']) {
                    $item['end_time'] = date('H:i', strtotime($item['end_time']));
                }
            }
        }
        return $dataSource;
    }
}
