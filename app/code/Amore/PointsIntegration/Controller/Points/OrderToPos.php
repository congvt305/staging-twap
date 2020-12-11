<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 6:16
 */

namespace Amore\PointsIntegration\Controller\Points;

use Amore\PointsIntegration\Model\Connection\Request;
use Amore\PointsIntegration\Model\PosOrderData;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderToPos extends Action
{
    /**
     * @var PosOrderData
     */
    private $posOrderData;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * OrderToPos constructor.
     * @param Action\Context $context
     * @param PosOrderData $posOrderData
     * @param Request $request
     * @param ManagerInterface $eventManager
     * @param Json $json
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        PosOrderData $posOrderData,
        Request $request,
        ManagerInterface $eventManager,
        Json $json,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderData = '';
        $status = 0;

        $orderId = $this->_request->getParam('order_id');
        $invoiceId = $this->_request->getParam('invoice_id');

        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->getOrder($orderId);

            $websiteId = $order->getStore()->getWebsiteId();
            $orderData = $this->posOrderData->getOrderData($order);
            $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');

            $status = $this->responseCheck($response);

            if ($status) {
                $this->posOrderData->updatePosSendCheck($order->getEntityId());
            }
        } catch (NoSuchEntityException $exception) {
            $response = $exception->getMessage();
            $this->messageManager->addErrorMessage($response);
            $this->_redirect('sales/order_invoice/view', ['invoice_id' => $invoiceId]);
        } catch (\Exception $exception) {
            $response = $exception->getMessage();
            $this->messageManager->addErrorMessage($response);
            $this->_redirect('sales/order_invoice/view', ['invoice_id' => $invoiceId]);
        }
        $this->logging($orderData, $response, $status);

        $this->_redirect('sales/order_invoice/view', ['invoice_id' => $invoiceId]);
    }

    public function getOrder($orderId)
    {
        $this->orderRepository->get($orderId);
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
                'topic_name' => 'amore.pos.points-integration.order.manual',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->json->serialize($sendData),
                'status' => $status,
                'result_message' => $this->json->serialize($responseData)
            ]
        );
    }
}
