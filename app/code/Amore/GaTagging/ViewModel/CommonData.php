<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 6:21 AM
 */

namespace Amore\GaTagging\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class CommonData implements ArgumentInterface
{
    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;

    public function __construct(
        \Amore\GaTagging\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    public function getApDataCid() {
//        return $this->isCustomerLoggedIn() ? hash('sha512', $this->getCustomer()->getIntegrationNumber()) : '';
        return $this->isCustomerLoggedIn() ? hash('sha512', $this->getCustomer()->getId()) : '';
    }

    public function getApDataIsMember() {
        return $this->getCustomer()->getIntegrationNumber() ? 'O' : 'X';
    }

    public function getApDataIslogin() {
        return $this->isCustomerLoggedIn() ? 'Y' : 'N';
    }

    public function getApDataLoginType() {
        return $this->isCustomerLoggedIn() ? 'NORMAL' : '';
    }

    public function getApDataCa() {
        return ( $this->isCustomerLoggedIn() && $this->getCustomer()->getDob() ) ? $this->getCustomerAge($this->getCustomer()->getDob()) : '';
    }

    public function getApDataCd() {
        return ( $this->isCustomerLoggedIn() && $this->getCustomer()->getDob() ) ? $this->getCustomerBirthYear($this->getCustomer()->getDob()) : '';
    }

    public function getApDataCg() {
        return ( $this->isCustomerLoggedIn() && $this->getCustomer()->getGender() ) ?$this->getCustomer()->getGender() : '';
    }

    /**
     * todo implement when membership starts
     * @return string
     */
    public function getApDataCt() {
        return '';
    }

    public function getApDataSiteName() {
        return 'LANEIGE';
    }

    public function getApDataChannel() {

    }

    public function getApDataPageName() {

    }

    public function getApDataPageType() {

    }

    public function getApDataBread() {

    }

    public function getApDataDate() {

    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer() {
        /** @var \Magento\Customer\Model\Customer $customer */
        return $customer = $this->customerSession->getCustomer();
    }

    private function isCustomerLoggedIn() {
        return $this->customerSession->isLoggedIn();
    }

    private function getCustomerAge($dobString)
    {
        $dobString = '1981-05-05';
        $birthYear = $this->dateTimeFactory->create()->date('Y', $dobString);
        $thisYear = $this->dateTimeFactory->create()->date('Y');
        $age = $thisYear - $birthYear;
        return $age;
    }

    private function getCustomerBirthYear($dobString)
    {
        return $this->dateTimeFactory->create()->date('Y', $dobString);
    }

}
