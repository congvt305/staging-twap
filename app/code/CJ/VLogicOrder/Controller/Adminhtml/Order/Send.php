<?php

namespace CJ\VLogicOrder\Controller\Adminhtml\Order;

use CJ\VLogicOrder\Model\Request\CreateOrder;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class Send extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param CreateOrder $createOrder
     */
    public function __construct(
        Action\Context           $context,
        JsonFactory              $jsonFactory,
        OrderRepositoryInterface $orderRepository,
        CreateOrder              $createOrder
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->orderRepository = $orderRepository;
        $this->createOrder = $createOrder;
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
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            $response = $this->createOrder->processOrderVLogic($order);
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
