<?php
declare(strict_types=1);

namespace Amore\StaffReferral\Model\Data;

use Amore\StaffReferral\Api\Data\ReferralInformationInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ReferralInformation extends AbstractExtensibleModel implements ReferralInformationInterface
{

    /**
     * @param int $type
     * @return ReferralInformationInterface
     */
    public function setReferralType($type)
    {
        return $this->setData(self::REFERRAL_TYPE, $type);
    }

    /**
     * @param string $code
     * @return ReferralInformationInterface
     */
    public function setReferralCode($code)
    {
        return $this->setData(self::REFERRAL_CODE, $code);
    }

    /**
     * @return int
     */
    public function getReferralType()
    {
        return (int)$this->_getData(self::REFERRAL_TYPE);
    }

    /**
     * @return string
     */
    public function getReferralCode()
    {
        return (string)$this->_getData(self::REFERRAL_CODE);
    }
}
