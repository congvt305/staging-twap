<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 10:41 AM
 */

namespace Eguana\CustomerRefund\Model;

class RefundOfflineManagement implements \Eguana\CustomerRefund\Api\RefundOfflineManagementInterface
{
    /**
     * @var \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface
     */
    private $bankInfoRepository;
    /**
     * @var \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory
     */
    private $bankInfoDataFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Refund
     */
    private $customerRefund;

    /**
     * @param \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory $bankInfoDataFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Refund $customerRefund
     */
    public function __construct(
        \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository,
        \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory $bankInfoDataFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Refund $customerRefund
    ) {
        $this->bankInfoRepository = $bankInfoRepository;
        $this->bankInfoDataFactory = $bankInfoDataFactory;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->customerRefund = $customerRefund;
    }

    /**
     * Process refund order offline
     *
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function process(\Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData): bool
    {
        try {
            $order = $this->orderRepository->get($bankInfoData->getOrderId());
            if ($this->customerRefund->canRefundOffline($order)) {
                $this->saveBankInfo($bankInfoData);
                //cannot cancel logistics...??
                $this->hold($order);
                $this->messageManager->addSuccessMessage(__('You requested to refund.'));
            } else {
                $this->messageManager->addErrorMessage(__('The order cannot be cancelled at this time because the system has already arranged for shipment. If you need to cancel your order, please use the return process, thank you!'));
                return false;
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
//            $this->messageManager->addErrorMessage(__('Something is wrong with the refund request. Please contact our customer service.'));
        }
        return true;
    }

    /**
     * Save data bank info
     *
     * @param $bankInfoData
     * @return void
     */
    private function saveBankInfo($bankInfoData)
    {
        $bankInfo = $this->bankInfoDataFactory->create();
        $bankInfo->setBankName($bankInfoData->getBankName());
        $bankInfo->setAccountOwnerName($bankInfoData->getAccountOwnerName());
        $bankInfo->setBankAccountNumber($bankInfoData->getBankAccountNumber());
        $bankInfo->setOrderId($bankInfoData->getOrderId());
        $this->bankInfoRepository->save($bankInfo);
    }

    /**
     * Hold order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function hold($order)
    {
        $order->hold();
        $order->setStatus(\Eguana\CustomerRefund\Api\RefundOfflineManagementInterface::STATUS_PENDING_REFUND);
        $this->orderRepository->save($order);
    }
}

