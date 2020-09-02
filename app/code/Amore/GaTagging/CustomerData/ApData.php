<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/3/20
 * Time: 7:30 AM
 */

namespace Amore\GaTagging\CustomerData;


use Magento\Customer\CustomerData\SectionSourceInterface;

class ApData implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerSession->getCustomer();

        return [
            'AP_DATA_GCID' => hash('sha512', $customer->getId()),
//            'AP_DATA_CID' => $this->getCustomerIntegrationNumber($customer),
            'AP_DATA_CID' => '',
//            'AP_DATA_ISMEMBER' => $this->getCustomerIntegrationNumber($customer) !== '' ? 'O' : 'X',
            'AP_DATA_ISMEMBER' => 'X',
            'AP_DATA_ISLOGIN' => 'Y',
            'AP_DATA_LOGINTYPE' => 'NORMAL',
            'AP_DATA_CA' => $customer->getDob() ? $this->getCustomerAge($customer->getDob()) : '',
            'AP_DATA_CD' => $customer->getDob() ? $this->getCustomerBirthYear($customer->getDob()) : '',
            'AP_DATA_CG' => $customer->getGender() ? $customer->getGender() : '',
//            'AP_DATA_CT' => $customer->getGroupId(), //todo get customer group name
            'AP_DATA_CT' => '', //todo get customer group name
           // 'AP_DATA_CHANNEL' => '',
           // 'AP_DATA_PAGENAME' => '',
           // 'AP_DATA_BREAD' => '',
        ];
    }

    private function getCustomerAge($dobString)
    {
        $birthYear = $this->dateTimeFactory->create()->date('Y', $dobString);
        $thisYear = $this->dateTimeFactory->create()->date('Y');
        $age = $thisYear - $birthYear;
        return $age;
    }

    private function getCustomerBirthYear($dobString)
    {
        return $this->dateTimeFactory->create()->date('Y', $dobString);
    }

    private function getCustomerIntegrationNumber($customer)
    {
        try {
            $integrationNumber = $customer->getIntegrationNumber();
            return hash('sha512', $customer->getIntegrationNumber());
        } catch (\Exception $e) {
            return '';
        }
    }

}
