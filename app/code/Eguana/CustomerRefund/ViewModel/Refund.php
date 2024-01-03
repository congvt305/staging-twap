<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 8:46 AM
 */

namespace Eguana\CustomerRefund\ViewModel;


use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Refund implements ArgumentInterface
{
    /**
     * @var \Eguana\CustomerRefund\Model\Refund
     */
    private $refundModel;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Eguana\CustomerRefund\Model\Refund $refundModel
     * @param \Magento\Framework\Registry $registry
     * @param DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Eguana\CustomerRefund\Model\Refund $refundModel,
        \Magento\Framework\Registry $registry,
        DateTime $dateTime
    )
    {
        $this->refundModel = $refundModel;
        $this->registry = $registry;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    /**
     * @return mixed|null
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function canShowRefundOnlineButton($order)
    {
        return $this->refundModel->getEcpayMethod($order) ? !$this->isClosingTime() && $this->refundModel->canRefundOnline($order) : $this->refundModel->canRefundOnline($order);
    }

    public function canShowRefundOfflineButton($order)
    {
        return $this->refundModel->canRefundOffline($order);
    }

    public function canShowBankInfoPopup($order)
    {
        return $this->refundModel->canShowBankInfoPopup($order);
    }

    public function isClosingTime()
    {
        $closingHour = '20';
        $closingMinFrom = '15';
        $closingMinTo = '30';
        $localTime = $this->timezone->date();
        $localHour = $localTime->format('H');
        $localMin = $localTime->format('i');
        return $localHour === $closingHour && $localMin >= $closingMinFrom && $localMin <= $closingMinTo;
    }


    /**
     * Check can available return
     *
     * @return bool
     */
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
