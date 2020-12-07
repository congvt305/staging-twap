<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 6:05
 */

namespace Amore\PointsIntegration\Block\Adminhtml\Order\Invoice\View;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    public function showPosOrderSendBtn()
    {
        $order = $this->getOrder();
        $posSendCheck = $order->getData('pos_order_send_check');
        return !$posSendCheck;
    }

    public function sendOrderToPosUrl()
    {
        $orderId = $this->getOrder()->getEntityId();
        $invoiceId = $this->getInvoice()->getEntityId();
        return $this->getUrl('pointsintegration/points/ordertopos', ['order_id' => $orderId, 'invoice_id' => $invoiceId]);
    }
}
