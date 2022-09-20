<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 3:21 AM
 */

namespace Eguana\CustomerRefund\Model;


use Eguana\CustomerRefund\Api\BankinfoManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class BankinfoManagement implements BankinfoManagementInterface
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
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Refund
     */
    private $customerRefund;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory $bankInfoDataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Refund $customerRefund
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository,
        \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory $bankInfoDataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Refund $customerRefund,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->bankInfoRepository = $bankInfoRepository;
        $this->bankInfoDataFactory = $bankInfoDataFactory;
        $this->messageManager = $messageManager;
        $this->customerRefund = $customerRefund;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function process(\Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData): bool
    {
        try {
            $order = $this->orderRepository->get($bankInfoData->getOrderId());
            if ($this->customerRefund->canShowBankInfoPopup($order)) {
                $this->saveBankInfo($bankInfoData);
            } else {
                $this->messageManager->addErrorMessage(__('The order cannot be cancelled at this time because the system has already arranged for shipment. If you need to cancel your order, please use the return process, thank you!'));
                return false;
            }

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something is wrong when saving the bank information. Please contact our customer service.'));
        }
        return true;
    }

    private function saveBankInfo($bankInfoData)
    {
        $bankInfo = $this->bankInfoDataFactory->create();
        $bankInfo->setBankName($bankInfoData->getBankName());
        $bankInfo->setAccountOwnerName($bankInfoData->getAccountOwnerName());
        $bankInfo->setBankAccountNumber($bankInfoData->getBankAccountNumber());
        $bankInfo->setOrderId($bankInfoData->getOrderId());
        $this->bankInfoRepository->save($bankInfo);
    }
}
