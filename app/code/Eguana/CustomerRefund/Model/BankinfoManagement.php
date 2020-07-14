<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 3:21 AM
 */

namespace Eguana\CustomerRefund\Model;


use Eguana\CustomerRefund\Api\BankinfoManagementInterface;

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

    public function __construct(
        \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository,
        \Eguana\CustomerRefund\Api\Data\BankInfoDataInterfaceFactory $bankInfoDataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {

        $this->bankInfoRepository = $bankInfoRepository;
        $this->bankInfoDataFactory = $bankInfoDataFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function process(\Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData): bool
    {
        try {
            $this->saveBankInfo($bankInfoData);
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
