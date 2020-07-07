<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-01
 * Time: 오후 4:22
 */

namespace Amore\Sap\Observer\SapOrder;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;

class SapOrderUpdateObserver implements ObserverInterface
{
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var SapOrderCancelData
     */
    private $sapOrderCancelData;

    public function __construct(
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        ManagerInterface $messageManager,
        SapOrderCancelData $sapOrderCancelData
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->sapOrderCancelData = $sapOrderCancelData;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditMemo */
        $creditMemo = $observer->getEvent()->getData('creditmemo');
        $order = $creditMemo->getOrder();

        $enableCheck = $this->config->getActiveCheck('store', $order->getStoreId());
        if ($enableCheck) {
            if (!$this->config->checkTestMode()) {
                try {
                    $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Send Data");
                        $this->logger->info($this->json->serialize($orderUpdateData));
                    }

                    $result = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Result Data");
                        $this->logger->info($result);
                    }

                    $resultSize = count($result);
                    if ($resultSize > 0) {
                        if ($result['code'] == '0000') {
                            $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                        } else {
                            $this->messageManager->addErrorMessage(__('Error occurred while sending order %1. Error code : %2. Message : %3', $order->getIncrementId(), $result['code'], $result['message']));
                        }
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

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Test Order Cancel Send Data");
                    $this->logger->info($jsonTestData);
                }

                try {
                    $result = $this->request->postRequest($jsonTestData, 0, 'cancel');
                    $resultSize = count($result);

                    if ($resultSize > 0) {
                        $this->messageManager->addSuccessMessage('Test Order Update sent to SAP Successfully.');
                        if ($result['code'] == '0000') {
                            $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                            $this->logger->info('Test Order Update sent to SAP Successfully.');
                        } else {
                            $this->messageManager->addErrorMessage(__('Error occurred while sending order %1. Error code : %2. Message : %3', $order->getIncrementId(), $result['code'], $result['message']));
                        }
                    } else {
                        $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                        $this->logger->info('Something went wrong while sending test order update data to SAP. No response.');
                    }
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->logger->info($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->logger->info($e->getMessage());
                }
            }
        }
    }
}
