<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Model;

use Amore\CustomerRegistration\Model\POSSystem;
use Amore\StaffReferral\Api\Data;
use Amore\StaffReferral\Api\ReferralApplyResultInterface;
use Amore\StaffReferral\Api\ReferralApplyResultInterfaceFactory;
use Amore\StaffReferral\Api\ReferralCodeManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class ReferralCodeManagement implements ReferralCodeManagementInterface
{

    const TW_WEBSITE = [
        'tw_lageige_website',
        'base'
    ];
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var POSSystem
     */
    private $posSystem;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReferralApplyResultInterfaceFactory
     */
    private $referralApplyResultFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Amore\CustomerRegistration\Helper\Data
     */
    private $config;

    /**
     * ReferralCodeManagement constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param POSSystem $posSystem
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ReferralApplyResultInterfaceFactory $referralApplyResultFactory
     * @param LoggerInterface $logger
     * @param \Amore\CustomerRegistration\Helper\Data $config
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        POSSystem $posSystem,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ReferralApplyResultInterfaceFactory $referralApplyResultFactory,
        LoggerInterface $logger,
        \Amore\CustomerRegistration\Helper\Data $config
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->posSystem = $posSystem;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->referralApplyResultFactory = $referralApplyResultFactory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param $cartId
     * @param Data\ReferralInformationInterface $referralInformation
     * @return ReferralApplyResultInterface
     * @throws NoSuchEntityException The specified cart or referral code does not exist.
     * @throws CouldNotSaveException The specified referral code could not be added.
     */
    public function validateAndApplyReferralCode(
        $cartId,
        Data\ReferralInformationInterface $referralInformation
    ) {
        /**
         * @var Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        if ($referralInformation->getReferralType() === Data\ReferralInformationInterface::REFERRAL_TYPE_BA) {
            $website = $quote->getStore()->getWebsite();
            $message = $this->validateStaffReferral($referralInformation->getReferralCode(),  $website->getCode(), $website->getId());
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_BA_CODE_KEY,
                $referralInformation->getReferralCode()
            );
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_FF_CODE_KEY
            );
        } elseif ($referralInformation->getReferralType() === Data\ReferralInformationInterface::REFERRAL_TYPE_FF) {
            $message = $this->validateFriendReferral(
                $referralInformation->getReferralCode(),
                $quote->getCustomerId(),
                $quote->getStore()->getWebsiteId()
            );
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_BA_CODE_KEY
            );
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_FF_CODE_KEY,
                $referralInformation->getReferralCode()
            );
        } elseif ($referralInformation->getReferralType() === Data\ReferralInformationInterface::REFERRAL_TYPE_CANCEL) {
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_BA_CODE_KEY
            );
            $quote->setData(
                Data\ReferralInformationInterface::REFERRAL_FF_CODE_KEY
            );
            $message = __('Removed referral code');
        } else {
            throw new CouldNotSaveException(__('Invalid Referral Code'));
        }
        $this->quoteRepository->save($quote->collectTotals());
        return $this->referralApplyResultFactory->create()->setMessage($message);
    }

    /**
     * @param string $referralCode
     * @param int $websiteId
     * @return string
     * @throws NoSuchEntityException The specified referral code does not exist.
     */
    private function validateFriendReferral($referralCode, $customerId, $websiteId)
    {
        $referralCode = trim($referralCode);
        if (is_numeric($referralCode)) {
            try {
                $mobileFilter = $this->filterBuilder->setField('mobile_number')
                    ->setValue($referralCode)
                    ->setConditionType('eq')
                    ->create();
                $phoneFilter = $this->filterBuilder->setField('billing_telephone')
                    ->setValue($referralCode)
                    ->setConditionType('eq')
                    ->create();

                if ($customerId !== null) {
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('website_id', $websiteId)
                        ->addFilters([$mobileFilter, $phoneFilter])
                        ->addFilter('entity_id', $customerId, 'neq')
                        ->create();
                } else {
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('website_id', $websiteId)
                        ->addFilters([$mobileFilter, $phoneFilter])
                        ->create();
                }
                $count = $this->customerRepositoryInterface->getList($searchCriteria)->getTotalCount();
                if ($count > 0) {
                    return __('Valid Referral Code');
                }
            } catch (\Exception $e) {
                $this->logger->critical('Error when fetching Customer phone number: ' . $e->getMessage());
            }
        }
        throw new NoSuchEntityException(__('Invalid Referral Code'));
    }

    /**
     * @param string $code
     * @return string
     * @throws NoSuchEntityException The specified referral code does not exist.
     */
    private function validateStaffReferral($code, $websiteCode, $websiteId)
    {
        $code = trim($code);
        if (in_array($websiteCode, self::TW_WEBSITE) && !is_numeric($code)) {
            throw new NoSuchEntityException(__('Invalid Referral Code'));
        }

        if (strlen($code) >= $this->config->getMinimumLengthBACode($websiteId) && strlen($code) <= $this->config->getMaximumLengthBACode($websiteId)) {
            try {
                $verificationResult = $this->posSystem->callBACodeInfoApi($code);
                if (isset($verificationResult['verify']) && $verificationResult['verify']) {
                    return __('Valid Referral Code');
                }
            } catch (\Exception $e) {
                $this->logger->critical('Error when fetching BA Info: ' . $e->getMessage());
            }
        }
        throw new NoSuchEntityException(__('Invalid Referral Code'));
    }
}
