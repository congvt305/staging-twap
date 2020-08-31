<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-14
 * Time: 오전 10:09
 */

namespace Amore\Sap\Plugin\Model;

use Amore\Sap\Exception\RmaSapException;
use Amore\Sap\Exception\RmaTrackNoException;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Model\SapOrder\SapOrderReturnData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Model\Rma;
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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SapOrderReturnData
     */
    private $sapOrderReturnData;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * RmaPlugin constructor.
     * @param Json $json
     * @param Request $request
     * @param Config $config
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SapOrderReturnData $sapOrderReturnData
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Json $json,
        Request $request,
        Config $config,
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        SapOrderReturnData $sapOrderReturnData,
        ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->sapOrderReturnData = $sapOrderReturnData;
        $this->messageManager = $messageManager;
        $this->eventManager = $eventManager;
    }

    public function beforeSaveRma(Rma $subject, $data)
    {
        $enableSapCheck = $this->config->getActiveCheck('store', $subject->getStoreId());
        $enableRmaCheck = $this->config->getRmaActiveCheck('store', $subject->getStoreId());
        $availableStatus = 'authorized';
        $order = $subject->getOrder();
        $rmaSendCheck = $order->getData('sap_return_send_check');

        if ($enableSapCheck && $enableRmaCheck) {
            if ($subject->getStatus() == $availableStatus) {
                if ($rmaSendCheck == null) {
                    $subject->setData('sap_return_send_check', self::RMA_SENT_TO_SAP_BEFORE);
                }

                try {
                    $orderRmaData = $this->sapOrderReturnData->singleOrderData($subject);

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Send Data");
                        $this->logger->info($this->json->serialize($orderRmaData));
                    }

                    $result = $this->request->postRequest($this->json->serialize($orderRmaData), $order->getStoreId());

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Result Data");
                        $this->logger->info($this->json->serialize($result));
                    }

                    $this->eventManager->dispatch(
                        "eguana_bizconnect_operation_processed",
                        [
                            'topic_name' => 'amore.sap.return.request',
                            'direction' => 'outgoing',
                            'to' => "SAP",
                            'serialized_data' => $this->json->serialize($orderRmaData),
                            'status' => 1,
                            'result_message' => $this->json->serialize($result)
                        ]
                    );

                    $resultSize = count($result);

                    if ($resultSize > 0) {
                        if ($result['code'] == '0000') {
                            $outdata = $result['data']['response']['output']['outdata'];
                            foreach ($outdata as $data) {
                                if ($data['retcod'] == 'S') {
                                    if ($rmaSendCheck == 0 || $rmaSendCheck == 2) {
                                        $this->messageManager->addSuccessMessage(__("Resent Return Data to Sap Successfully."));
                                    } else {
                                        $this->messageManager->addSuccessMessage(__("Sent Return Data to Sap Successfully."));
                                    }
                                } else {
                                    throw new RmaSapException(
                                        __(
                                            'Error returned from SAP for RMA %1. Error code : %2. Message : %3',
                                            $subject->getIncrementId(),
                                            $data['ugcod'],
                                            $data['ugtxt']
                                        )
                                    );
                                }
                            }
                        } else {
                            throw new RmaSapException(
                                __(
                                    'Error returned from SAP for RMA %1. Error code : %2. Message : %3',
                                    $subject->getIncrementId(),
                                    $result['code'],
                                    $result['message']
                                )
                            );
                        }
                    } else {
                        throw new RmaSapException(__('Something went wrong while sending order data to SAP. No response'));
                    }
                } catch (NoSuchEntityException $e) {
                    throw new NoSuchEntityException(__($e->getMessage()));
                } catch (RmaTrackNoException $e) {
                    throw new RmaTrackNoException(__($e->getMessage()));
                } catch (RmaSapException $e) {
                    throw new RmaSapException(__($e->getMessage()));
                } catch (LocalizedException $e) {
                    throw new LocalizedException(__($e->getMessage()));
                } catch (\Exception $e) {
                    throw new \Exception(__('SAP Return : ' . $e->getMessage()));
                }
            }
        }
    }
}
