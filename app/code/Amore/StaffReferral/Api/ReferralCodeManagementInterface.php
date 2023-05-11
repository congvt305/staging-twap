<?php


namespace Amore\StaffReferral\Api;


use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ReferralCodeManagementInterface
{
    /**
     * @param int $cartId
     * @param Data\ReferralInformationInterface $referralInformation
     * @return ReferralApplyResultInterface
     * @throws NoSuchEntityException The specified cart or referral code does not exist.
     * @throws CouldNotSaveException The specified referral code could not be added.
     */
    public function validateAndApplyReferralCode(
        $cartId,
        Data\ReferralInformationInterface $referralInformation
    );
}
