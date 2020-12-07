<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 4:55
 */

namespace Amore\PointsIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class OrderToPosSenderObserver implements ObserverInterface
{
    /**
     * @var \Amore\PointsIntegration\Model\PosOrderData
     */
    private $posOrderData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
     */
    private $request;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;

    /**
     * OrderToPosSenderObserver constructor.
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     */
    public function __construct(
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json
    ) {
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
    }

    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();
        $orderData = '';
        $status = 0;
        try {
            $websiteId = $order->getStore()->getWebsiteId();
            $orderData = $this->posOrderData->getOrderData($order);
            $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');

            $status = $this->responseCheck($response);

            if ($status) {
                $this->posOrderData->updatePosSendCheck($order->getEntityId());
            }
        } catch (NoSuchEntityException $exception) {
            $response = $exception->getMessage();
        } catch (\Exception $exception) {
            $response = $exception->getMessage();
        }

        $this->logging($orderData, $response, $status);
    }

    public function responseCheck($response)
    {
        $arrResponse = $this->json->unserialize($response);
        if ($arrResponse['statusCode'] == '200') {
            return 1;
        } else {
            return 0;
        }
    }

    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.pos.points-integration.order.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->json->serialize($sendData),
                'status' => $status,
                'result_message' => $this->json->serialize($responseData)
            ]
        );
    }
}
