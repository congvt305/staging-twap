<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-23
 * Time: 오후 3:00
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Ecpay\Ecpaypayment\Model\Order;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderSend extends Action
{
    /**
     * @var Json
     */
    private $json;
    /**
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * OrderSend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param Request $request
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        SapOrderConfirmData $sapOrderConfirmData,
        Request $request,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get($orderId);

        try {
            $orderSendData = $this->sapOrderConfirmData->singleOrderData($order->getIncrementId());
            $result = $this->request->postRequest($this->json->serialize($orderSendData), $order->getStoreId());


            $resultSize = count($result);
//            $resultSize = 1;

            if ($resultSize > 0) {
                $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
            } else {
                $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP.'));
            }

        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amore_Sap::sap');
    }
}
