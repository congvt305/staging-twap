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

        if (json_decode($additionalData, true)["RtnCode"] == 1) {
            return json_decode($additionalData, true);
        } else {
            return null;
        }
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

        if (json_decode($ecpayInvoiceInvalidateData, true)["RtnCode"] == 1) {
            return json_decode($ecpayInvoiceInvalidateData, true);
        } else {
            return null;
        }
    }

    public function getInvalidateEInvoiceNumber($orderId)
    {
        $invalidateEInvoiceInformation = $this->getInvalidateEInvoiceInformation($orderId);
        return $invalidateEInvoiceInformation["IA_Invoice_No"];
    }

    public function hasEInvoiceTriplicateData($orderId)
    {
        return is_null($this->getEInvoiceTriplicateInformation($orderId)) ? 0 : count($this->getEInvoiceTriplicateInformation($orderId));
    }

    protected function getEInvoiceTriplicateInformation($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $additionalData = $payment->getAdditionalData();
        $additionalInfo = $payment->getAdditionalInformation();
        $rawDetailsInfo = $additionalInfo["raw_details_info"];

        if (json_decode($additionalData, true)["RtnCode"] == 1
            && !empty($rawDetailsInfo["ecpay_einvoice_triplicate_title"])
            && !empty($rawDetailsInfo["ecpay_einvoice_tax_id_number"])
        ) {
            return $rawDetailsInfo;
        } else {
            return null;
        }
    }

    public function getEInvoiceTriplicateTitle($orderId)
    {
        $eInvoiceTriplicateInformation = $this->getEInvoiceTriplicateInformation($orderId);
        return $eInvoiceTriplicateInformation["ecpay_einvoice_triplicate_title"];
    }

    public function getEInvoiceTaxIdNumber($orderId)
    {
        $eInvoiceTriplicateInformation = $this->getEInvoiceTriplicateInformation($orderId);
        return $eInvoiceTriplicateInformation["ecpay_einvoice_tax_id_number"];
    }

    public function hasEInvoiceCellphoneBarCodeData($orderId)
    {
        return is_null($this->getEInvoiceCellphoneBarCodeInformation($orderId)) ? 0 : count($this->getEInvoiceCellphoneBarCodeInformation($orderId));
    }

    protected function getEInvoiceCellphoneBarCodeInformation($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $payment = $order->getPayment();
        $additionalInfo = $payment->getAdditionalInformation();
        $rawDetailsInfo = $additionalInfo["raw_details_info"];

        if (!empty($rawDetailsInfo["ecpay_einvoice_cellphone_barcode"])) {
            return $rawDetailsInfo;
        } else {
            return null;
        }
    }

    public function getEInvoiceCellphoneBarCode($orderId)
    {
        $eInvoiceCellphoneBarCodeInformation = $this->getEInvoiceCellphoneBarCodeInformation($orderId);
        return $eInvoiceCellphoneBarCodeInformation["ecpay_einvoice_cellphone_barcode"];
    }
}
