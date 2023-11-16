<?php

namespace Amore\CustomerLogin\Observer;

use Amore\CustomerRegistration\Model\POSSystem;
use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Log;
use Zend_Log_Writer_Stream;

class CustomerLogin implements ObserverInterface
{
    const GROUP_GRADE_CD = [
        'HKL01' => '5',
        'HKL02' => '8',
        'HKL03' => '11',
        'HKL04' => '14',
        'HKL09' => '17',
        'HKA01' => '20',
        'HKA02' => '23',
        'HKA03' => '26',
        'HKA04' => '29',
        'HKA09' => '32',
        'HKS01' => '35',
        'HKS02' => '38',
        'HKS03' => '41',
        'HKS04' => '44',
        'HKS05' => '47',
        'HKS09' => '50',

    ];

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var POSSystem
     */
    private $posSystem;

    public function __construct(
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        POSSystem $posSystem,
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->posSystem = $posSystem;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();
            $customerId = $customer->getId();

            $customer = $this->customerFactory->create()->load($customerId);
            $customerDataModel = $customer->getDataModel();
            if (!$customerDataModel->getCustomAttribute('mobile_number') ||
                !$customerDataModel->getCustomAttribute('mobile_number')->getValue() ||
                !$customerDataModel->getCustomAttribute('country_pos_code')
            ) {
                return;
            }
            $customerData = $this->posSystem->getMemberInfo(
                $customerDataModel->getFirstname(),
                $customerDataModel->getLastname(),
                $customerDataModel->getCustomAttribute('mobile_number')->getValue(),
                $customerDataModel->getCustomAttribute('country_pos_code')->getValue()
            );
            $writer = new Zend_Log_Writer_Stream(BP . '/var/log/customerlogin.log');
            $logger = new Zend_Log();
            $logger->addWriter($writer);
            $logger->info(print_r('email=' . $customerDataModel->getEmail(), true));
            $logger->info(print_r('mobile=' . $customerDataModel->getCustomAttribute('mobile_number')->getValue(), true));
            $logger->info(print_r('country=' . $customerDataModel->getCustomAttribute('country_pos_code')->getValue(), true));
            $logger->info(print_r($customerData, true));

            $saveCustomer = false;
            $customerData = current($customerData);
            if (!empty($customerData['cstmGradeCD'])) {
                $websiteId = (int)$this->storeManager->getStore($customerDataModel->getStoreId())->getWebsiteId();
                $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

                if ($websiteCode == 'hk_lageige_website') {
                    $customerGroupID = self::GROUP_GRADE_CD[$customerData['cstmGradeCD']] ?? '14';
                } elseif ($websiteCode == 'hk_sulwhasoo_website') {
                    $customerGroupID = self::GROUP_GRADE_CD[$customerData['cstmGradeCD']] ?? '47';
                } elseif ($websiteCode == 'base') {
                    $customerGroupID = self::GROUP_GRADE_CD[$customerData['cstmGradeCD']] ?? '29';
                }

                if ($customerDataModel->getGroupId() != $customerGroupID) {
                    $customerDataModel->setGroupId((int)$customerGroupID);
                    $saveCustomer = true;
                }
            }
            if (isset($customerData['EMPID'])) {
                if ($customerDataModel->getCustomAttribute('ba_code')) {
                    if ($customerDataModel->getCustomAttribute('ba_code')->getValue() != $customerData['EMPID']) {
                        $customerDataModel->setCustomAttribute('ba_code', $customerData['EMPID']);
                        $saveCustomer = true;
                    }
                } else {
                    $customerDataModel->setCustomAttribute('ba_code', $customerData['EMPID']);
                    $saveCustomer = true;
                }
            }

            if ($saveCustomer) {
                $customer->updateData($customerDataModel);
                $customer->save();
            }
        } catch (Exception $e) {
             $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/customerlogin.log');
             $logger = new \Zend_Log();
             $logger->addWriter($writer);
             $logger->info(print_r('-ERROR-', true));
             $logger->info(print_r($e->getMessage(), true));
             $logger->info(print_r($e->getTraceAsString(), true));
        }
    }

}
