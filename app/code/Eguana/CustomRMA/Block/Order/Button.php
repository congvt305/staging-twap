<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-29
 * Time: 오후 6:42
 */

namespace Eguana\CustomRMA\Block\Order;

use Magento\Framework\Stdlib\DateTime\DateTime;

class Button extends \Magento\Rma\Block\Order\Button
{
    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $data);
    }

    public function availableFreeReturn()
    {
        $result = false;
        $order = $this->getOrder();
        if ($order->getStatus() == 'complete') {
            $updateAt = $order->getUpdatedAt();
            $orderFreeReturnDate = $this->dateTime->date('Y-m-d H:i:s', strtotime($updateAt . '+ 7 days'));
            $currentDate = $this->dateTime->date();
            if ($currentDate < $orderFreeReturnDate) {
                $result = true;
            }
        } elseif ($order->getStatus() == 'delivery_complete') {
            $result = true;
        }

        return $result;
    }
}
