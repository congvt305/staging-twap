<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-19
 * Time: 오후 5:35
 */

namespace Amore\Sap\Controller\Adminhtml\PublishMessage;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class SapInventoryStockPublisher extends Action
{
    public function __construct(
        Action\Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
