<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/3/20
 * Time: 7:30 AM
 */

namespace Amore\GaTagging\CustomerData;


use Amore\GaTagging\Model\CommonVariable;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Eav\Model\Config;

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
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;
    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Eguana\SocialLogin\Model\SocialLoginHandler
     */
    private $socialLoginModel;

    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Eguana\SocialLogin\Model\SocialLoginHandler $socialLoginModel,
        Config $eavConfig
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->httpHeader = $httpHeader;
        $this->groupRepository = $groupRepository;
        $this->socialLoginModel = $socialLoginModel;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [
                'AP_DATA_GCID' => '',
                'AP_DATA_ISLOGIN' => CommonVariable::VALUE_NO,
                'AP_DATA_LOGINTYPE' => '',
                'AP_DATA_CD' => '',
                'AP_DATA_CG' => '',
                'AP_DATA_CT' => ''
            ];
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerSession->getCustomer();

        $coreSession = $this->socialLoginModel->getCoreSession();
        $loginType = 'NORMAL';
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
            'AP_DATA_GCID' => hash('sha512', $customer->getId()),
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

    private function getCustomerAge($dobString)
    {
        $birthYear = $this->dateTimeFactory->create()->date('Y', $dobString);
        $thisYear = $this->dateTimeFactory->create()->date('Y');
        return $thisYear - $birthYear;
    }

    private function getCustomerBirthYear($dobString)
    {
        return $this->dateTimeFactory->create()->date('Y', $dobString);
    }

    private function getCustomerIntegrationNumber($customer)
    {
        try {
            $integrationNumber = $customer->getIntegrationNumber();
            return $integrationNumber ? hash('sha512', $customer->getIntegrationNumber()) : 'X';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getCustomerGroupCode($groupId)
    {
        $customerGroupCode = '';
        try {
            $customerGroup = $this->groupRepository->getById($groupId);
            $customerGroupCode = $customerGroup->getCode();
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }
        return $customerGroupCode;

    }

}
