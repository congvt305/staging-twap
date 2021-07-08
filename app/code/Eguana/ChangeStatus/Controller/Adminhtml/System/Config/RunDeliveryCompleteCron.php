<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/4/21
 * Time: 4:30 PM
 */
namespace Eguana\ChangeStatus\Controller\Adminhtml\System\Config;

use Eguana\ChangeStatus\Model\GetCompletedOrders;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

/**
 * Class RunDeliveryCompleteCron
 *
 * To run cron for changing order status to delivery complete
 */
class RunDeliveryCompleteCron extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var GetCompletedOrders
     */
    private $completedOrders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param JsonFactory $jsonFactory
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param GetCompletedOrders $completedOrders
     */
    public function __construct(
        JsonFactory $jsonFactory,
        Action\Context $context,
        LoggerInterface $logger,
        GetCompletedOrders $completedOrders
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->jsonFactory = $jsonFactory;
        $this->completedOrders = $completedOrders;
    }

    /**
     * To change order status to "Delivery Complete" from "Shipment Processing"
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonFactory->create();
        try {
            $response = $this->completedOrders->changeStatusToDeliveryComplete();
            return $result->setData(['success' => $response]);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return $result->setData(['success' => false]);
        }
    }
}
