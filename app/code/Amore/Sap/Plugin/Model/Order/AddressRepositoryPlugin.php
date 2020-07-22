<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-07
 * Time: 오후 3:07
 */

namespace Amore\Sap\Plugin\Model\Order;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddressRepositoryPlugin
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
     * SapOrderAddressUpdateObserver constructor.
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

    public function beforeSave(\Magento\Sales\Model\Order\AddressRepository $subject, \Magento\Sales\Api\Data\OrderAddressInterface $entity)
    {
        $orderId = $entity->getParentId();
        $order = $this->orderRepository->get($orderId);
        $orderStatus = $order->getStatus();

        $availableStatus = ['sap_processing', 'sap_success', 'sap_fail'];

        $enableSapCheck = $this->config->getActiveCheck('store', $order->getStoreId());
        $enableAddressCheck = $this->config->getAddressActiveCheck('store', $order->getStoreId());

        if ($enableSapCheck && $enableAddressCheck) {
            if (in_array($orderStatus, $availableStatus)) {
                if (!$this->config->checkTestMode()) {
                    try {
                        $orderUpdateData = $this->sapOrderCancelData->singleAddressUpdateData($order->getIncrementId(), $entity);

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Order Address Update Data");
                            $this->logger->info($this->json->serialize($orderUpdateData));
                        }

                        $result = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Order Address Update Result Data");
                            $this->logger->info($this->json->serialize($result));
                        }

                        $resultSize = count($result);
                        if ($resultSize > 0) {
                            if ($result['code'] == '0000') {
                                $responseHeader = $result['data']['response']['header'];
                                if ($responseHeader['rtn_TYPE'] == 'S') {
                                    $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                                } else {
                                    throw new \Exception(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                                }
                            } else {
                                throw new \Exception(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $result['code'], $result['message']));
                            }
                        } else {
                            throw new \Exception(__('Something went wrong while sending order data to SAP. No response.'));
                        }
                    } catch (NoSuchEntityException $e) {
                        throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                    } catch (\Exception $e) {
                        throw new \Exception(__('SAP : ' . $e->getMessage()));
                    }
                } else {
                    $testData = $this->sapOrderCancelData->getTestCancelOrder();

                    $jsonTestData = $this->json->serialize($testData);

                    try {
                        $result = $this->request->postRequest($jsonTestData, 0, 'cancel');

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Order Address Test Update Result Data");
                            $this->logger->info($this->json->serialize($result));
                        }

                        $resultSize = count($result);

                        if ($resultSize > 0) {
                            if ($result['code'] == '0000') {
                                $responseHeader = $result['data']['response']['header'];
                                if ($responseHeader['rtn_TYPE'] == 'S') {
                                    $this->messageManager->addSuccessMessage(__('Test Order Address Update sent to SAP Successfully.'));
                                } else {
                                    throw new \Exception(__('Error returned from SAP for Test order. Error code : %1. Message : %2', $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                                }
                            } else {
                                throw new \Exception(__('Error returned from SAP for Test order. Error code : %1. Message : %2', $result['code'], $result['message']));
                            }
                        } else {
                            throw new \Exception(__('Something went wrong while sending order address update data to SAP. No response.'));
                        }
                    } catch (LocalizedException $e) {
                        throw new LocalizedException(__('SAP : ' . $e->getMessage()));
                    } catch (\Exception $e) {
                        throw new \Exception(__('SAP : ' . $e->getMessage()));
                    }
                }
            } else {
                throw new \Exception(__('Cannot change the shipping address with current order status.'));
            }
        }
    }
}
