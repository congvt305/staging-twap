<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/11/20
 * Time: 1:27 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\Component\Counter\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status grid column renderer
 *
 * Class Status
 */
class Status extends Column
{
    /**
     * Status column renderer
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) : array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $item['status'];
                if ($status) {
                    $text = 'Enable';
                    $class = 'notice';
                } else {
                    $text = 'Disable';
                    $class = 'critical';
                }
                $item['status'] = '<span class="grid-severity-' . $class . '"><span>' . $text . '</span></span>';
            }
        }
        return $dataSource;
    }
}
