<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 6:19 PM
 *
 */

namespace Eguana\BizConnect\Ui\Component\Listing\Column\OperationLog\Direction;

use Eguana\BizConnect\Model\LoggedOperation;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => LoggedOperation::DIRECTION_INCOMING,
                'value' => LoggedOperation::DIRECTION_INCOMING,
            ],
            [
                'label' => LoggedOperation::DIRECTION_OUTGOING,
                'value' => LoggedOperation::DIRECTION_OUTGOING,
            ],
        ];
    }
}
