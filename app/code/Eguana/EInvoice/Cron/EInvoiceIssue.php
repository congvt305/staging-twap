<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/20
 * Time: 11:42 AM
 */

namespace Eguana\EInvoice\Cron;

use Magento\Framework\Serialize\Serializer\Json;

class EInvoiceIssue
{
    /**
     * @var \Eguana\EInvoice\Model\Order
     */
    private $order;
    /**
     * @var \Ecpay\Ecpaypayment\Model\Payment
     */
    private $ecpayPaymentModel;
    /**
     * @var \Eguana\EInvoice\Model\Source\Config
     */
    private $config;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Eguana\EInvoice\Model\Email
     */
    private $helperEmail;

    /**
     * @var \Eguana\EInvoice\Model\EInvoiceService
     */
    protected $einvoiceService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * EInvoiceIssue constructor.
     * @param \Eguana\EInvoice\Model\Order $order
     * @param \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel
     * @param \Eguana\EInvoice\Model\Source\Config $config
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Eguana\EInvoice\Model\Email $helperEmail
     */
    public function __construct(
        \Eguana\EInvoice\Model\Order $order,
        \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel,
        \Eguana\EInvoice\Model\Source\Config $config,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\EInvoice\Model\Email $helperEmail,
        \Eguana\EInvoice\Model\EInvoiceService $einvoiceService,
        \Psr\Log\LoggerInterface $logger,
        Json $json
    ) {
        $this->order = $order;
        $this->logger = $logger;
        $this->ecpayPaymentModel = $ecpayPaymentModel;
        $this->config = $config;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->helperEmail = $helperEmail;
        $this->einvoiceService = $einvoiceService;
        $this->json = $json;
    }

    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $isActive = $this->config->getEInvoiceIssueActive($store->getId());

            if ($isActive) {
                $notIssuedOrderList = $this->order->getNotIssuedOrders($store->getId());

                foreach ($notIssuedOrderList as $index => $order) {
                    try {
                        $orderId = $order->getEntityId();
                        if (!empty($data = $this->einvoiceService->fetchEInvoiceDetail($orderId))) {
                            // update payment information
                            $payment = $order->getPayment();
                            $payment->setAdditionalData($this->json->serialize($data));
                            $payment->save();
                        } else {
                            $ecpayInvoiceResult = $this->ecpayPaymentModel->createEInvoice($order);

                            if ($ecpayInvoiceResult["RtnCode"] != "1") {
                                //send mail
                                $this->helperEmail->sendEmail($order, $ecpayInvoiceResult["RtnMsg"]);
                            }
                        }
                    } catch (\Exception $e) {
                        //$this->helperEmail->sendEmail($order, $e->getMessage());
                        $context = [
                            'order_id' => $order->getIncrementId()
                        ];
                        $this->logger->log('info', 'EINVOICE EXCEPTION: ' . $e->getMessage(), $context);
                        $this->logger->log('info', 'ERROR TRACE', $context);
                        $this->logger->log('info', $e->getTraceAsString(), $context);
                    }
                }
            }
        }
    }
}
