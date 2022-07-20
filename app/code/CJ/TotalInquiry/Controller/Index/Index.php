<?php

namespace CJ\TotalInquiry\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use CJ\TotalInquiry\Model\ResourceModel\Order as OrderResourceModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 * @package CJ\TotalInquiry\Controller\Index
 */
class Index extends Action implements HttpGetActionInterface
{
    const STATUS_DELIVERY_COMPLETE = 'delivery_complete';
    const STATUS_CODE_PROCESSING_WITH_SHIPMENT = 'processing_with_shipment';
    const STATUS_SAP_SUCCESS = 'sap_success';
    const STATUS_SHIPMENT_PROCESSING = 'shipment_processing';
    const STATUS_PREPARING = 'preparing';
    const STATUS_COMPLETE = 'complete';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var OrderResourceModel
     */
    protected $orderResource;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CustomerSession $customerSession
     * @param OrderResourceModel $orderResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CustomerSession $customerSession,
        OrderResourceModel $orderResource,
        LoggerInterface $logger
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->orderResource = $orderResource;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = [];
        $customerId = $this->getCustomerId();
        try {
            if (!empty($customerId)) {
                $data = [
                    'new' => $this->orderResource->getTotalOrders(self::STATUS_CODE_PROCESSING_WITH_SHIPMENT, $customerId),
                    'process' => $this->orderResource->getTotalOrders(self::STATUS_SAP_SUCCESS, $customerId, self::STATUS_PREPARING),
                    'shipping' => $this->orderResource->getTotalOrders(self::STATUS_SHIPMENT_PROCESSING, $customerId),
                    'complete' => $this->orderResource->getTotalOrders(self::STATUS_DELIVERY_COMPLETE, $customerId, self::STATUS_COMPLETE)
                ];
            } else {
                throw new LocalizedException(__('You are not logged in. Please login and try again'));
            }
        } catch (LocalizedException $exception) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addErrorMessage(__('Something went wrong with total inquiry!'));
            $this->logger->info(__('Total Inquiry: %1', $exception->getMessage()));

            return $resultRedirect->setPath('');
        }
        $result->setData($data);

        return $result;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerSession->getCustomer()->getId();
    }
}
