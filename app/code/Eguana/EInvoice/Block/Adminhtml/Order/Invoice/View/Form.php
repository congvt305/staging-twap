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
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;

    /**
     * Form constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Amore\PointsIntegration\Model\Source\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->json = $json;
        $this->config = $config;
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
        return isset($addtionalData['InvoiceNumber']) || (isset($addtionalData['RtnCode']) && $addtionalData['RtnCode'] == '1');
    }

    public function canShowOrderToPosButton()
    {
        $order = $this->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $moduleActive = $this->config->getActive($websiteId);
        $orderActive = $this->config->getPosOrderActive($websiteId);
        $posSendCheck = $order->getData('pos_order_send_check');

        $showBtn = ($moduleActive && $orderActive && !$posSendCheck);

        return $showBtn;
    }

    public function getSendOrderToPosURl()
    {
        $order = $this->getOrder();
        $invoiceId = $this->getInvoice()->getEntityId();
        return $this->getUrl('pointsintegration/points/ordertopos', ['order_id' => $order->getEntityId(), 'invoice_id' => $invoiceId]);
    }
}
