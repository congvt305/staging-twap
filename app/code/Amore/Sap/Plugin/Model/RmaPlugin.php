<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-14
 * Time: 오전 10:09
 */

namespace Amore\Sap\Plugin\Model;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;

class RmaPlugin
{
    const RMA_SENT_TO_SAP_BEFORE = 0;

    const RMA_SENT_TO_SAP_SUCCESS = 1;

    const RMA_SENT_TO_SAP_FAIL = 2;

    const RMA_RESENT_TO_SAP_SUCCESS = 3;

    /**
     * @var Config
     */
    private $config;
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
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * RmaPlugin constructor.
     * @param Json $json
     * @param Request $request
     * @param Config $config
     * @param Logger $logger
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param OrderRepositoryInterface $orderRepository
     * @param HistoryFactory $historyFactory
     */
    public function __construct(
        Json $json,
        Request $request,
        Config $config,
        Logger $logger,
        SapOrderConfirmData $sapOrderConfirmData,
        OrderRepositoryInterface $orderRepository,
        HistoryFactory $historyFactory
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
        $this->orderRepository = $orderRepository;
        $this->historyFactory = $historyFactory;
    }

    public function beforeSaveRma(\Magento\Rma\Model\Rma $subject, $data)
    {
        $enableCheck = $this->config->getActiveCheck('store', $subject->getStoreId());
        $availableStatus = 'authorized';
        $orderIncrementId = $subject->getOrderIncrementId();
        $order = $subject->getOrder();
        $rmaSendCheck = $order->getData('sap_return_send_check');

        if ($enableCheck) {
            if ($subject->getStatus() == $availableStatus) {
                if ($rmaSendCheck == null) {
                    $order->setData('sap_return_send_check', self::RMA_SENT_TO_SAP_BEFORE);
                }

                try {
                    $orderRmaData = $this->sapOrderConfirmData->singleOrderData($orderIncrementId);

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Send Data");
                        $this->logger->info($this->json->serialize($orderRmaData));
                    }

                    $result = $this->request->postRequest($this->json->serialize($orderRmaData), $order->getStoreId());

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Result Data");
                        $this->logger->info($this->json->serialize($result));
                    }

                    $resultSize = count($result);

                    if ($resultSize > 0) {
                        if ($result['code'] == '0000') {
                            $outdata = $result['data']['response']['output']['outdata'];
                            foreach ($outdata as $data) {
                                if ($data['retcod'] == 'S') {
                                    if ($rmaSendCheck == 0 || $rmaSendCheck == 2) {
                                        $this->saveRmaSendCheck($order, self::RMA_RESENT_TO_SAP_SUCCESS);
                                    } else {
                                        $this->saveRmaSendCheck($order, self::RMA_SENT_TO_SAP_SUCCESS);
                                    }
                                    $this->addComment($order, "SAP Return : RMA Sent to SAP Successfully.");
                                } else {
                                    $this->saveRmaSendCheck($order, self::RMA_SENT_TO_SAP_FAIL);
                                    $this->addComment(
                                        $order,
                                        __(
                                            'SAP Return : Error returned from SAP for order %1. Error code : %2. Message : %3',
                                            $order->getIncrementId(),
                                            $data['ugcod'],
                                            $data['ugtxt']
                                        )
                                    );
                                    throw new \Exception(
                                        __(
                                            'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                            $order->getIncrementId(),
                                            $data['ugcod'],
                                            $data['ugtxt']
                                        )
                                    );
                                }
                            }
                        } else {
                            $this->saveRmaSendCheck($order, self::RMA_SENT_TO_SAP_FAIL);
                            $this->addComment(
                                $order,
                                __(
                                    'SAP Return : Error returned from SAP for order %1. Error code : %2. Message : %3',
                                    $order->getIncrementId(),
                                    $result['code'],
                                    $result['message']
                                )
                            );
                            throw new \Exception(
                                __(
                                    'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                    $order->getIncrementId(),
                                    $result['code'],
                                    $result['message']
                                )
                            );
                        }
                    } else {
                        $this->saveRmaSendCheck($order, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                        $this->addComment($order, 'SAP Return : Something went wrong while sending order data to SAP. No response');
                        throw new \Exception(__('Something went wrong while sending order data to SAP. No response'));
                    }
                } catch (NoSuchEntityException $e) {
                    $this->saveRmaSendCheck($order, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                    $this->addComment($order, "SAP Return : " . $e->getMessage());
                    throw new NoSuchEntityException(__($e->getMessage()));
                } catch (LocalizedException $e) {
                    $this->saveRmaSendCheck($order, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                    $this->addComment($order, "SAP Return : " . $e->getMessage());
                    throw new LocalizedException(__($e->getMessage()));
                } catch (\Exception $exception) {
                    $this->addComment($order, "SAP Return : " . $exception->getMessage());
                    $this->saveRmaSendCheck($order, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                    throw new \Exception(__('SAP Return : Error occurred while sending RMA data to SAP'));
                }
            }
            $this->orderRepository->save($order);
        }
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @param $status int
     */
    public function saveRmaSendCheck($order, $status)
    {
        $order->setData('sap_return_send_check', $status);
        $this->orderRepository->save($order);
    }

    /**
     * @param $order \Magento\Sales\Model\Order
     * @param $message string
     */
    public function addComment($order, $message)
    {
        /** @var \Magento\Sales\Model\Order\Status\History $history */
        $history = $this->historyFactory->create();
        $history->setStatus(false);
        $history->setComment($message);
        $history->setEntityName('rma');
        $history->setIsVisibleOnFront(false);
        $history->setOrder($order);
        $history->save();
    }
}
