<?php

namespace CJ\VLogicOrder\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use CJ\VLogicOrder\Model\ShipmentActivity;

class ShipmentActivityRun extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ShipmentActivity
     */
    private $shipmentActivity;

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param ShipmentActivity $shipmentActivity
     */
    public function __construct(
        Action\Context   $context,
        JsonFactory      $jsonFactory,
        ShipmentActivity $shipmentActivity
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->shipmentActivity = $shipmentActivity;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->shipmentActivity->execute();
            $result = $this->jsonFactory->create();

            return $result->setData(['success' => true]);
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }
}

