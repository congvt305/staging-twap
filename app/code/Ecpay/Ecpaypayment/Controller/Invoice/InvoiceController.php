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

    public function __construct(
        Context $context,
        EcpayPaymentModel $ecpayPaymentModel
    ) {
        parent::__construct($context);
        $this->ecpayPaymentModel = $ecpayPaymentModel;
    }

    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam("order_id");
            $this->ecpayPaymentModel->createEInvoice($orderId);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
