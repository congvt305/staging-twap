<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/26
 * Time: 4:37 PM
 */

namespace Eguana\EInvoice\Block\Ecpay;

use Magento\Framework\View\Element\Template;

class EInvoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->orderRepository = $orderRepository;
    }

    public function hasEInvoices($orderId)
    {
        return is_null($this->getEInvoiceInformation($orderId)) ? 0 : count($this->getEInvoiceInformation($orderId));
    }

    protected function getEInvoiceInformation($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $additionalData = $payment->getAdditionalData();

        return json_decode($additionalData, true);
    }

    public function getEInvoiceDate($orderId)
    {
        $eInvoiceInformation = $this->getEInvoiceInformation($orderId);
        return $eInvoiceInformation["InvoiceDate"];
    }

    public function getEInvoiceNumber($orderId)
    {
        $eInvoiceInformation = $this->getEInvoiceInformation($orderId);
        return $eInvoiceInformation["InvoiceNumber"];
    }

    public function getOrderId()
    {
        return $this->getRequest()->getParam("order_id");
    }

    public function hasInvalidateEInvoices($orderId)
    {
        return is_null($this->getInvalidateEInvoiceInformation($orderId)) ? 0 : count($this->getInvalidateEInvoiceInformation($orderId));
    }

    protected function getInvalidateEInvoiceInformation($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $ecpayInvoiceInvalidateData = $payment->getEcpayInvoiceInvalidateData();

        return json_decode($ecpayInvoiceInvalidateData, true);
    }

    public function getInvalidateEInvoiceNumber($orderId)
    {
        $invalidateEInvoiceInformation = $this->getInvalidateEInvoiceInformation($orderId);
        return $invalidateEInvoiceInformation["InvoiceNumber"];
    }
}
