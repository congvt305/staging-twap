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

    public function __construct(\Eguana\CustomerRefund\Model\Refund $refundModel)
    {
        $this->refundModel = $refundModel;
    }
    private function getOrder()
    {

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function canShowRefundOnlineButton($order)
    {
        return $this->refundModel->canRefundOnline($order);
    }

    public function canShowRefundOfflineButton()
    {

    }

}
