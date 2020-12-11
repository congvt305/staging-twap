<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/4/20
 * Time: 4:13 PM
 */

namespace Eguana\EInvoice\Block\Adminhtml\Order\Invoice\View;


class Form extends \Magento\Sales\Block\Adminhtml\Order\Invoice\View\Form
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->json = $json;
    }

    public function canShowCreateEInvoiceButton()
    {
        $payment = $this->getOrder()->getPayment();
        return !$this->hasEInvoice($payment);
    }

    public function getCreateEInvoiceUrl()
    {
        return $this->getUrl('eguana_einvoice/einvoice/create', ['invoice_id' => $this->getInvoice()->getEntityId()]);
    }

    private function hasEInvoice($payment)
    {
        $addtionalData =$payment->getAdditionalData();
        if (!$addtionalData) {
            return false;
        }
        $addtionalData = $this->json->unserialize($addtionalData);
        return isset($addtionalData['InvoiceNumber']);
    }

    public function canShowOrderToPosButton()
    {
        $order = $this->getOrder();
        $orderToPosCheck = $order->getData('pos_order_send_check');
        return !$orderToPosCheck;
    }

    public function getSendOrderToPosURl()
    {
        $order = $this->getOrder();
        $invoiceId = $this->getInvoice()->getEntityId();
        return $this->getUrl('pointsintegration/points/ordertopos', ['order_id' => $order->getEntityId(), 'invoice_id' => $invoiceId]);
    }
}
