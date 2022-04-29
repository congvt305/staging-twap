<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/06/05
 * Time: 11:23 AM
 */

namespace Ecpay\Ecpaypayment\Controller\Invoice;

use Ecpay\Ecpaypayment\Model\Payment as EcpayPaymentModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderRepository;

class InvoiceController extends Action
{
    /**
     * @var EcpayPaymentModel
     */
    private $ecpayPaymentModel;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param EcpayPaymentModel $ecpayPaymentModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        EcpayPaymentModel $ecpayPaymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        OrderRepository $orderRepository
    ) {
        parent::__construct($context);
        $this->ecpayPaymentModel = $ecpayPaymentModel;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create e-invoice
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam("order_id");
            $order = $this->orderRepository->get($orderId);
            $resArr = $this->ecpayPaymentModel->createEInvoice($order);
            echo print_r($resArr, true) . "<br/>";
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
