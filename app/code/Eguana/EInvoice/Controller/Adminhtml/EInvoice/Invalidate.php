<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/20/20
 * Time: 7:14 AM
 */

namespace Eguana\EInvoice\Controller\Adminhtml\EInvoice;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;

class Invalidate extends \Magento\Backend\App\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Ecpay\Ecpaypayment\Model\Payment
     */
    private $ecpayPaymentModel;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderRepository = $orderRepository;
        $this->ecpayPaymentModel = $ecpayPaymentModel;
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
            $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $order = $this->orderRepository->get($creditmemo->getOrderId());
            $result = $this->ecpayPaymentModel->invalidateEInvoice($order->getPayment(), $order->getStoreId());
            if (isset($result['RtnCode'], $result['RtnMsg']) && $result['RtnCode'] === '1') {
                $this->messageManager->addSuccessMessage($result['RtnMsg']);
            } else {
                $this->messageManager->addErrorMessage('create E-Invoice Invalidate failed.');
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
        }
        $this->_redirect('sales/order_creditmemo/view', ['creditmemo_id' => $creditmemoId]);
    }

}

