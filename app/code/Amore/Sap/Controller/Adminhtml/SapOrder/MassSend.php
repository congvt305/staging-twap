<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-22
 * Time: 오후 3:48
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Controller\Adminhtml\AbstractAction;

class MassSend extends AbstractAction
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * MassSend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SapOrderConfirmData $sapOrderConfirmData
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SapOrderConfirmData $sapOrderConfirmData
    ) {
        parent::__construct($context, $json, $request, $logger, $config);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
    }

    public function execute()
    {
        $orderDataList = [];
        $orderItemDataList = [];
        $orderStatusError = [];
        $storeIdList = [];

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->filter->getCollection($this->collectionFactory->create());

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection->getItems() as $order) {
            try {
                $storeId = $order->getStoreId();
                $storeIdList[] = $storeId;
                if ($order->getStatus() == 'processing') {
                    $orderData = $this->sapOrderConfirmData->getOrderData($order->getIncrementId());
                    $orderItemData = $this->sapOrderConfirmData->getOrderItem($order->getIncrementId());
                    $orderDataList = array_merge($orderDataList, $orderData);
                    $orderItemDataList = array_merge($orderItemDataList, $orderItemData);
                } else {
                    $orderStatusError[] = $order->getIncrementId();
                }
            } catch (NoSuchEntityException $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
            } catch (\Exception $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
            }
        }

        if ($this->config->getLoggingCheck()) {
            $this->logger->info("ORDER List Data");
            $this->logger->info($this->json->serialize($orderDataList));
            $this->logger->info("ORDER Item List Data");
            $this->logger->info($this->json->serialize($orderItemDataList));
        }

        if ($this->differentStoreExist($storeIdList)) {
            $this->messageManager->addErrorMessage(__("There are more than two different stores` orders selected. Please select order by store and try again."));
            return $resultRedirect->setPath('sales/order/index');
        }

        if (count($orderStatusError) > 0) {
//            $errorOrderList = implode(", ", $orderStatusError);
            foreach ($orderStatusError as $error) {
                $this->messageManager->addErrorMessage(__("Error occurred while sending order : %1.", $error));
            }
        }

        $orderCount = count($orderDataList);
        try {
            if ($orderCount > 0) {
                $storeIdUnique = array_unique($storeIdList);
                $sendData = $this->sapOrderConfirmData->massSendOrderData($orderDataList, $orderItemDataList);
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND DATA");
                    $this->logger->info($this->json->serialize($sendData));
                }

                $result = $this->request->postRequest($this->json->serialize($sendData), array_shift($storeIdUnique));

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND RESULT");
                    $this->logger->info($this->json->serialize($result));
                }

                $resultSize = count($result);
                if ($resultSize > 0) {
                    $this->messageManager->addSuccessMessage(__('%1 orders sent to SAP Successfully.', $orderCount));
                } else {
                    $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response'));
                }
            } else {
                $this->messageManager->addErrorMessage(__('There is no order to send. Check order and try again.'));
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath('sales/order/index');
    }

    public function differentStoreExist($storeIdList)
    {
        if (count(array_unique($storeIdList)) > 1) {
            return true;
        }
        return false;
    }
}
