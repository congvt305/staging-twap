<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/19/20
 * Time: 12:48 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Config\Source;

class OrderStatus extends \Magento\Sales\Model\Config\Source\Order\Status implements
    \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE,
    ];

}