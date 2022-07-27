<?php

namespace CJ\VLogicOrder\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use CJ\VLogicOrder\Model\ShipmentActivity as ShipmentActivityProcess;

class ShipmentActivity extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ShipmentActivityProcess
     */
    private $shipmentActivityProcess;

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param ShipmentActivityProcess $shipmentActivityProcess
     */
    public function __construct(
        Action\Context          $context,
        JsonFactory             $jsonFactory,
        ShipmentActivityProcess $shipmentActivityProcess
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->shipmentActivityProcess = $shipmentActivityProcess;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/index');
        try {
            $response = $this->shipmentActivityProcess->execute($this->getRequest()->getParam('order_id'));
            if (!empty($response['success'])) {
                $this->messageManager->addSuccessMessage(__('Successful'));
            } else {
                $this->messageManager->addErrorMessage(__(json_encode($response)));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__($exception->getMessage()));
        }
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
