<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/21/20, 8:04 PM
 *
 */

namespace Eguana\BizConnect\Plugin\Order\StateResolver;

use Magento\Sales\Api\Data\OrderInterface;

class InProgressStatePlugin
{
    public function afterGetStateForOrder(
        \Magento\Sales\Model\Order\StateResolver $subject,
        $result,
        OrderInterface $order,
        array $arguments = []
    ) {
        $newState = $result;
        if (isset($arguments[0]) && ($arguments[0] == \Magento\Sales\Model\Order\StateResolver::IN_PROGRESS))
        {
            $newState = 'in_progress';
        }
        return $newState;
    }

}

