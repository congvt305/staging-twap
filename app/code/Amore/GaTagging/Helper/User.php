<?php
declare(strict_types=1);

namespace Amore\GaTagging\Helper;

use Amore\GaTagging\Model\CommonVariable;
use Magento\Eav\Model\Config;

/**
 * Class User
 */
class User
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Eguana\SocialLogin\Model\SocialLoginHandler
     */
    protected $socialLoginModel;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Eguana\SocialLogin\Model\SocialLoginHandler $socialLoginModel
     * @param Config $eavConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Eguana\SocialLogin\Model\SocialLoginHandler $socialLoginModel,
        Config $eavConfig
    ) {
        $this->customerSession = $customerSession;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->groupRepository = $groupRepository;
        $this->socialLoginModel = $socialLoginModel;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return array
     */
    public function getCustomerData()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [
                'AP_DATA_GCID' => '',
                'AP_DATA_CID' => '',
                'AP_DATA_ISMEMBER' => '',
                'AP_DATA_ISLOGIN' => CommonVariable::VALUE_NO,
                'AP_DATA_LOGINTYPE' => '',
                'AP_DATA_CA' => '',
                'AP_DATA_CD' => '',
                'AP_DATA_CG' => '',
                'AP_DATA_CT' => ''
            ];
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerSession->getCustomer();

        $coreSession = $this->socialLoginModel->getCoreSession();
        $loginType = CommonVariable::DEFAULT_LOGIN_TYPE;
        if (isset($coreSession->getData()['socialmedia_type'])) {
            $loginType = $coreSession->getData()['socialmedia_type'];
        }

        $genderLabel = '';
        $gender = '';
        if ($customer->getGender()) {
            $genderOptions = $this->eavConfig->getAttribute('customer','gender')->getSource()->getAllOptions();
            foreach ($genderOptions as $genderOption) {
                if ($genderOption['value'] == $customer->getGender()) {
                    $genderLabel = $genderOption['label'];
                    break;
                }
            }
            if ($genderLabel == 'Male') {
                $gender = 'M';
            } elseif($genderLabel == 'Female') {
                $gender = 'F';
            }
        }

        return [
            'AP_DATA_GCID' => hash('sha512', (string)$customer->getId()),
            'AP_DATA_CID' => $this->getCustomerIntegrationNumber($customer),
            'AP_DATA_ISMEMBER' => $this->getCustomerIntegrationNumber($customer) !== 'X' ? 'O' : 'X',
            'AP_DATA_ISLOGIN' => CommonVariable::VALUE_YES,
            'AP_DATA_LOGINTYPE' => $loginType,
            'AP_DATA_CA' => $customer->getDob() ? $this->getCustomerAge($customer->getDob()) : '',
            'AP_DATA_CD' => $customer->getDob() ? $this->getCustomerBirthYear($customer->getDob()) : '',
            'AP_DATA_CG' => $gender,
            'AP_DATA_CT' => $this->getCustomerGroupCode($customer->getGroupId())
        ];
    }

    /**
     * @param $dobString
     * @return string
     */
    private function getCustomerAge($dobString)
    {
        $birthYear = $this->dateTimeFactory->create()->date('Y', $dobString);
        $thisYear = $this->dateTimeFactory->create()->date('Y');
        return $thisYear - $birthYear;
    }

    /**
     * @param $dobString
     * @return string
     */
    private function getCustomerBirthYear($dobString)
    {
        return $this->dateTimeFactory->create()->date('Y', $dobString);
    }

    /**
     * @param $customer
     * @return string
     */
    private function getCustomerIntegrationNumber($customer)
    {
        try {
            $integrationNumber = $customer->getIntegrationNumber();
            return $integrationNumber ? hash('sha512', $integrationNumber) : 'X';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $groupId
     * @return string
     */
    protected function getCustomerGroupCode($groupId)
    {
        try {
            $customerGroup = $this->groupRepository->getById($groupId);
            return $customerGroup->getCode();
        } catch (\Exception $e) {
            return '';
        }
    }
}
