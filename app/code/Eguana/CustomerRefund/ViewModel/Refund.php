<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 8:46 AM
 */

namespace Eguana\CustomerRefund\ViewModel;


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

    public function __construct(
        \Eguana\CustomerRefund\Model\Refund $refundModel,
        \Magento\Framework\Registry $registry
    )
    {
        $this->refundModel = $refundModel;
        $this->registry = $registry;
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
        return $this->refundModel->canRefundOnline($order);
    }

    public function canShowRefundOfflineButton($order)
    {
        return $this->refundModel->canRefundOffline($order);
    }

    public function canShowBankInfoPopup($order)
    {
        return $this->refundModel->canShowBankInfoPopup($order);
    }

}
