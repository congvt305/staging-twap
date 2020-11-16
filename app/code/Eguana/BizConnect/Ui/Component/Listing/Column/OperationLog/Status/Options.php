<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 6:19 PM
 *
 */

namespace Eguana\BizConnect\Ui\Component\Listing\Column\OperationLog\Status;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => 'SUCCESS'
            ],
            [
                'value' => 0,
                'label' => 'FAILED'
            ],
            [
                'value' => 2,
                'label' => 'N/A'
            ],
        ];
    }
}
