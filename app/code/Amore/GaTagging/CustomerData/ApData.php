<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/3/20
 * Time: 7:30 AM
 */

namespace Amore\GaTagging\CustomerData;


use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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

    public function __construct(
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->httpHeader = $httpHeader;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        if (!$this->isLoggedIn()) {
            return [];
        }

        /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
        $customer = $this->customerSession->getCustomer();

        return [
            'AP_DATA_GCID' => hash('sha512', $customer->getId()),
            'AP_DATA_CID' => $this->getCustomerIntegrationNumber($customer),
            'AP_DATA_ISMEMBER' => $this->getCustomerIntegrationNumber($customer) !== 'X' ? 'O' : 'X',
            'AP_DATA_ISLOGIN' => 'Y',
            'AP_DATA_LOGINTYPE' => 'NORMAL', // 비회원은 없다. 만약 허용한다면 비회원 주문조회, 모바일 로그인 추후 수정 필요
            'AP_DATA_CA' => $customer->getDob() ? $this->getCustomerAge($customer->getDob()) : '',
            'AP_DATA_CD' => $customer->getDob() ? $this->getCustomerBirthYear($customer->getDob()) : '',
            'AP_DATA_CG' => $customer->getGender() ? $customer->getGender() : '',
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
    private function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    private function isMobile()
    {
        return preg_match("/(android|ipod|ipad|blackberry|windows\ ce|lg|mot|samsung|sonyericsson)/i", $this->httpHeader->getHttpUserAgent());
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
