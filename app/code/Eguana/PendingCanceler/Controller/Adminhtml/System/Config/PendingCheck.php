<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 4:23
 */

namespace Eguana\PendingCanceler\Controller\Adminhtml\System\Config;

use Eguana\PendingCanceler\Model\PendingOrderCanceler;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class PendingCheck extends Action
{
    /**
     * @var PendingOrderCanceler
     */
    private $pendingOrderCanceler;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * PendingCheck constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param PendingOrderCanceler $pendingOrderCanceler
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        PendingOrderCanceler $pendingOrderCanceler
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->pendingOrderCanceler = $pendingOrderCanceler;
    }

    public function execute()
    {
        try {
            $this->pendingOrderCanceler->pendingCanceler();
            $result = $this->jsonFactory->create();

            return $result->setData(['success' => true]);
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }
}
