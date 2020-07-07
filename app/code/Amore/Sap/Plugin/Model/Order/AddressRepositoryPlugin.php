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

        $enableCheck = $this->config->getActiveCheck('store', $order->getStoreId());

        if ($enableCheck) {
            if (!$this->config->checkTestMode()) {
                try {
                    $orderUpdateData = $this->sapOrderCancelData->singleAddressUpdateData($order->getIncrementId());
                    $result = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                    $resultSize = count($result);
                    if ($resultSize > 0) {
                        $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                    } else {
                        $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                    }
                } catch (NoSuchEntityException $e) {
                    throw new NoSuchEntityException(__($e->getMessage()));
                } catch (\Exception $e) {
                    throw new \Exception(__($e->getMessage()));
                }
            } else {
                $testData = $this->sapOrderCancelData->getTestCancelOrder();

                $jsonTestData = $this->json->serialize($testData);

                try {
                    $result = $this->request->postRequest($jsonTestData, 0, 'cancel');
                    $resultSize = count($result);

                    if ($resultSize > 0) {
                        $this->messageManager->addSuccessMessage('Test Order Address Update sent to SAP Successfully.');
                        $this->logger->info('Test Order Address Update sent to SAP Successfully.');
                    } else {
                        $this->messageManager->addErrorMessage(__('Something went wrong while sending order address update data to SAP. No response.'));
                        $this->logger->info('Something went wrong while sending test order address update data to SAP. No response.');
                    }
                } catch (LocalizedException $e) {
                    $this->logger->info($e->getMessage());
                    throw new LocalizedException(__($e->getMessage()));
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                    throw new \Exception(__($e->getMessage()));
                }
            }
        }
    }
}
