<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Model;

use Amore\StaffReferral\Api\Data;
use Amore\StaffReferral\Api\GuestReferralCodeManagementInterface;
use Amore\StaffReferral\Api\ReferralApplyResultInterface;
use Amore\StaffReferral\Api\ReferralCodeManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;

class GuestReferralCodeManagement implements GuestReferralCodeManagementInterface
{
    /**
     * Quote repository.
     *
     * @var ReferralCodeManagementInterface
     */
    protected $referralCodeManagement;

    /**
     * @var MaskedQuoteIdToQuoteId
     */
    private $maskedQuoteIdToQuoteId;


    /**
     * GuestReferralCodeManagement constructor.
     * @param ReferralCodeManagementInterface $referralCodeManagement
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     */
    public function __construct(
        ReferralCodeManagementInterface $referralCodeManagement,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
    ) {
        $this->referralCodeManagement = $referralCodeManagement;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @param string cartId
     * @param Data\ReferralInformationInterface $referralInformation
     * @return ReferralApplyResultInterface
     * @throws NoSuchEntityException The specified cart or referral code does not exist.
     * @throws CouldNotSaveException The specified referral code could not be added.
     */
    public function validateAndApplyReferralCode(
        $cartId,
        Data\ReferralInformationInterface $referralInformation
    ) {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($cartId);
        return $this->referralCodeManagement->validateAndApplyReferralCode($quoteId, $referralInformation);
    }
}
