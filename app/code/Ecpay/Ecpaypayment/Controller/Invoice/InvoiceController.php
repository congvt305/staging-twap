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

use Ecpay\Ecpaypayment\Helper\Data as EcpayHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class InvoiceController extends Action
{
    /**
     * @var EcpayHelper
     */
    private $ecpayHelper;

    public function __construct(
        Context $context,
        EcpayHelper $ecpayHelper
    ) {
        parent::__construct($context);
        $this->ecpayHelper = $ecpayHelper;
    }

    public function execute()
    {
        try {
            $orderId = $this->getRequest()->getParam("order_id");
            $this->ecpayHelper->createEInvoice($orderId);
        } catch (\Exception $e) {
            $this->exceptionLogger->logException($e);
            throw $e;
        }
    }
}
