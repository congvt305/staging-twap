<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-25
 * Time: 오후 7:47
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Controller\Adminhtml\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderCancelSend extends AbstractAction
{

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SapOrderCancelData
     */
    private $sapOrderCancelData;

    /**
     * OrderCancelSend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SapOrderCancelData $sapOrderCancelData
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SapOrderCancelData $sapOrderCancelData
    ) {
        parent::__construct($context, $json, $request, $logger, $config);
        $this->orderRepository = $orderRepository;
        $this->sapOrderCancelData = $sapOrderCancelData;
    }

    public function execute()
    {
        if (!$this->config->checkTestMode()) {
            $orderId = $this->getRequest()->getParam('id');
            $order = $this->orderRepository->get($orderId);

            try {
                $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());
                $result = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId());

                $resultSize = count($result);

                if ($resultSize > 0) {
                    $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                } else {
                    $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $testData = $this->sapOrderCancelData->getTestCancelOrder();

            $jsonTestData = $this->json->serialize($testData);

            try {
                $result = $this->request->postRequest($jsonTestData, 0);
                $resultSize = count($result);

                if ($resultSize > 0) {
                    $this->messageManager->addSuccessMessage(__('Test Order sent to SAP Successfully.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Something went wrong while sending test order update data to SAP. No response.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/index');
    }
}
