<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/24/2021
 */

namespace Amore\GcrmBanner\Model\Coupon;

use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\SalesRule\Model\Rule as RuleModel;
use Magento\SalesRule\Model\RuleFactory;

class CouponGenerator
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;
    /**
     * @var Rule
     */
    private $ruleResource;
    /**
     * @var GcrmCouponCodeGenerator
     */
    private $gcrmCouponCodeGenerator;
    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    private $couponRepository;
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon
     */
    private $couponResource;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule $ruleResource,
        \Amore\GcrmBanner\Model\Coupon\GcrmCouponCodeGenerator $gcrmCouponCodeGenerator,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Api\CouponRepositoryInterface $couponRepository,
        \Magento\SalesRule\Model\ResourceModel\Coupon $couponResource,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {

        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->gcrmCouponCodeGenerator = $gcrmCouponCodeGenerator;
        $this->couponRepository = $couponRepository;
        $this->dateTime = $dateTime;
        $this->couponResource = $couponResource;
        $this->collectionFactory = $collectionFactory;
    }
    public function generateCoupon($customerId, $salesruleId)
    {
        /** @var RuleModel $rule */
        $rule = $this->ruleFactory->create();
        $this->ruleResource->load($rule, $salesruleId);
        $activeCouponCode = $this->getActiveCouponCodeForCustomer($rule, $customerId);
        if ($activeCouponCode) {
            return $activeCouponCode;
        }
        $codeFormat = null;
        $codePrefix = 'GCRM-' . $salesruleId . '-';
        $codeSuffix = null;
        $this->gcrmCouponCodeGenerator->setData([
            'codeFormat' => $codeFormat,
            'codePrefix' => $codePrefix,
            'codeSuffix' => $codeSuffix,
        ]);

        $rule->setCouponCodeGenerator($this->gcrmCouponCodeGenerator);
        $rule->setCouponType(RuleModel::COUPON_TYPE_AUTO);

        $coupon = $rule->acquireCoupon()
            ->setType(RuleModel::COUPON_TYPE_NO_COUPON)
            ->setCreatedAt($this->dateTime->formatDate(true))
            ->setCustomerId($customerId);

        $this->couponResource->save($coupon);
        return $coupon->getCode();

    }

    private function getActiveCouponCodeForCustomer($rule, $customerId)
    {
        $couponCollection = $this->collectionFactory->create();
        $couponCollection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $couponCollection->addFieldToFilter('rule_id', $rule->getId());
        if ($couponCollection->getSize() > 0) {
            return $couponCollection->getFirstItem()->getData('code');
        }
        return false;
    }

}
