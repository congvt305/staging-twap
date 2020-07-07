<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-07
 * Time: 오전 9:57
 */
namespace Amore\Sap\Plugin\Model\Order;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreditmemoRepositoryPlugin
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
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreditmemoRepositoryPlugin constructor.
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param SapOrderCancelData $sapOrderCancelData
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        ManagerInterface $messageManager,
        SapOrderCancelData $sapOrderCancelData,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->sapOrderCancelData = $sapOrderCancelData;
        $this->orderRepository = $orderRepository;
    }

    public function beforeSave(\Magento\Sales\Model\Order\CreditmemoRepository $subject, \Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        $storeId = $entity->getStoreId();
        $enableCheck = $this->config->getActiveCheck('store', $storeId);
        $order = $this->orderRepository->get($entity->getOrderId());

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
                    throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                } catch (\Exception $e) {
                    throw new \Exception(__('SAP : ' . $e->getMessage()));
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
                    $this->logger->info($e->getMessage());
                    throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    throw new \Exception(__('SAP : ' . $e->getMessage()));
                }
            }
        }
    }
}
