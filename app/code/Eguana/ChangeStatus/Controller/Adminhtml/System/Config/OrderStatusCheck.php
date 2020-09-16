<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-11
 * Time: 오후 2:59
 */

namespace Eguana\ChangeStatus\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class OrderStatusCheck extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var \Eguana\ChangeStatus\Model\GetCompletedOrders
     */
    private $getCompletedOrders;

    /**
     * OrderStatusCheck constructor.
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param \Eguana\ChangeStatus\Model\GetCompletedOrders $getCompletedOrders
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        \Eguana\ChangeStatus\Model\GetCompletedOrders $getCompletedOrders
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->getCompletedOrders = $getCompletedOrders;
    }

    public function execute()
    {
        try {
            $this->getCompletedOrders->OrderStatusChanger();
            $result = $this->jsonFactory->create();

            return $result->setData(['success' => true]);
        } catch (\Exception $exception) {
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }

    public function _isAllowed()
    {
        return parent::_isAllowed();
    }
}
