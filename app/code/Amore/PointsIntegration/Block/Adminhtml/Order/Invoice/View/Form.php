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
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Amore\PointsIntegration\Model\Source\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->config = $config;
    }

    public function showPosOrderSendBtn()
    {
        $order = $this->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $moduleActive = $this->config->getActive($websiteId);
        $orderActive = $this->config->getPosOrderActive($websiteId);
        $posSendCheck = $order->getData('pos_order_send_check');

        $showBtn = ($moduleActive && $orderActive && !$posSendCheck);

        return $showBtn;
    }

    public function sendOrderToPosUrl()
    {
        $orderId = $this->getOrder()->getEntityId();
        $invoiceId = $this->getInvoice()->getEntityId();
        return $this->getUrl('pointsintegration/points/ordertopos', ['order_id' => $orderId, 'invoice_id' => $invoiceId]);
    }
}
