<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2021/01/22
 * Time: 11:33 AM
 */

namespace Eguana\EInvoice\Controller\Adminhtml\EInvoice;

use Eguana\EInvoice\Cron\EInvoiceIssue;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class EInvoiceCheck extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var EInvoiceIssue
     */
    private $eInvoiceIssue;

    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        EInvoiceIssue $eInvoiceIssue
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->eInvoiceIssue = $eInvoiceIssue;
    }

    public function execute()
    {
        $this->eInvoiceIssue->execute();
        $result = $this->jsonFactory->create();

        return $result->setData(["success" => true]);
    }
}
