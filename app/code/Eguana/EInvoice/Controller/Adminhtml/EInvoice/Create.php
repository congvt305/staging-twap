<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/4/20
 * Time: 4:21 PM
 */

namespace Eguana\EInvoice\Controller\Adminhtml\EInvoice;


use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Ecpay\Ecpaypayment\Model\Payment
     */
    private $ecpayPaymentModel;
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Eguana\EInvoice\Model\EInvoiceService
     */
    protected $service;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel,
        \Psr\Log\LoggerInterface $logger,
        \Eguana\EInvoice\Model\EInvoiceService $service,
        Action\Context $context
    ) {
        $this->service = $service;
        parent::__construct($context);
        $this->logger = $logger;
        $this->ecpayPaymentModel = $ecpayPaymentModel;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        try {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoice = $this->invoiceRepository->get($invoiceId);
            $order = $this->orderRepository->get($invoice->getOrderId());
            if (empty($data = $this->service->fetchEInvoiceDetail($invoice->getOrderId()))) {
                $result = $this->ecpayPaymentModel->createEInvoice($order->getEntityId(), $order->getStoreId());
                if (isset($result['RtnCode'], $result['RtnMsg']) && $result['RtnCode'] === '1') {
                    $this->messageManager->addSuccessMessage($result['RtnMsg']);
                } else {
                    $this->messageManager->addErrorMessage('create E-Invoice failed.');
                }
            } else {
                // update payment information
                $payment = $order->getPayment();
                $payment->setAdditionalData(json_encode($data));
                $payment->save();
                $this->messageManager->addSuccessMessage($data['RtnMsg']);
            }

        } catch (LocalizedException $e) {
            $context = ['order_id' => $order->getIncrementId()];
            $this->logger->log('info', 'CREATE EINVOICE EXCEPTION: ' . $e->getMessage(), $context);
            $this->logger->log('info', $e->getTraceAsString(), $context);
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('sales/order_invoice/view', ['invoice_id' => $invoiceId]);
        }
        $this->_redirect('sales/order_invoice/view', ['invoice_id' => $invoiceId]);
    }
}
