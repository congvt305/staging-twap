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

    public function __construct(
        Context $context,
        EcpayPaymentModel $ecpayPaymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->ecpayPaymentModel = $ecpayPaymentModel;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam("order_id");
            $storeId = $this->storeManager->getStore()->getId();
            $resArr = $this->ecpayPaymentModel->createEInvoice($orderId, $storeId);
            echo print_r($resArr, true) . "<br/>";
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
