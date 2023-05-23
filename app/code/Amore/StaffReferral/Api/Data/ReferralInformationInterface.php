<?php
namespace Amore\StaffReferral\Api\Data;

interface ReferralInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const REFERRAL_TYPE = 'referral_type';
    const REFERRAL_CODE = 'referral_code';

    const REFERRAL_TYPE_CANCEL = 0;
    const REFERRAL_TYPE_BA = 1;
    const REFERRAL_TYPE_FF = 2;

    const REFERRAL_BA_CODE_KEY = 'referral_ba_code';
    const REFERRAL_FF_CODE_KEY = 'referral_ff_code';

    const IS_BA_CONDITION_CARTRULE = 'is_ba_referral';
    const IS_FF_CONDITION_CARTRULE = 'is_ff_referral';

    /**
     * @param $type
     * @return ReferralInformationInterface
     */
    public function setReferralType($type);

    /**
     * @param $code
     * @return ReferralInformationInterface
     */
    public function setReferralCode($code);

    /**
     * @return int
     */
    public function getReferralType();

    /**
     * @return string
     */
    public function getReferralCode();
}
