<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 6/11/20
 * Time: 8:07 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Counter\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * To change start & end time of counter list
 *
 * Class SlotTimeBreak
 */
class SlotTimeBreak extends Column
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
                if ($item['slot_time']) {
                    $time = (int) $item['slot_time'];
                    $item['slot_time'] = date('H:i', mktime(0, $time));
                }

                if ($item['break']) {
                    $break = (int) $item['break'];
                    $item['break'] = date('H:i', mktime(0, $break));
                }
            }
        }
        return $dataSource;
    }
}
