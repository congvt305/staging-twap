<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Plugin;

use Amore\StaffReferral\Api\Data\ReferralInformationInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility;

class ApplyReferralCartRule
{
    /**
     * Check if rule can be applied for specific quote with referral condition
     * @see Utility::canProcessRule
     * @param Utility $subject
     * @param bool $result
     * @param Rule $rule
     * @param Address $address
     * @return bool
     */
    public function afterCanProcessRule(
        Utility $subject,
        $result,
        $rule,
        $address
    ) {
        $needBACode = !!$rule->getData(ReferralInformationInterface::IS_BA_CONDITION_CARTRULE);
        $needFFCode = !!$rule->getData(ReferralInformationInterface::IS_FF_CONDITION_CARTRULE);
        if ($result && ($needBACode || $needFFCode)) {
            //Check referral condition only if other condition is match and there are referral settings is enabled
            $quote = $address->getQuote();
            $hasBACode = !!$quote->getData(ReferralInformationInterface::REFERRAL_BA_CODE_KEY);
            $hasFFCode = !!$quote->getData(ReferralInformationInterface::REFERRAL_FF_CODE_KEY);
            return ($needBACode && $hasBACode) || ($needFFCode && $hasFFCode);
        }
        return $result;
    }
}
