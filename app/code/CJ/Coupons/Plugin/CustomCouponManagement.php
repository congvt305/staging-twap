<?php

namespace CJ\Coupons\Plugin;

use Amasty\Coupons\Model\CouponRenderer;
use Amasty\Coupons\Model\SalesRule\FilterCoupons;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;

/**
 * Class CustomCouponManagement
 */
class CustomCouponManagement
{
    /**
     * @var CouponRenderer
     */
    private $couponRenderer;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var FilterCoupons
     */
    private $filterCoupons;

    /**
     * @var \Amasty\Coupons\Model\QuoteCouponStorage
     */
    private $quoteCouponStorage;

    /**
     * @var \CJ\Coupons\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param CouponRenderer $couponRenderer
     * @param CartRepositoryInterface $quoteRepository
     * @param FilterCoupons $filterCoupons
     * @param \Amasty\Coupons\Model\QuoteCouponStorage $quoteCouponStorage
     * @param \CJ\Coupons\Helper\Data $dataHelper
     */
    public function __construct(
        CouponRenderer $couponRenderer,
        CartRepositoryInterface $quoteRepository,
        FilterCoupons $filterCoupons,
        \Amasty\Coupons\Model\QuoteCouponStorage $quoteCouponStorage,
        \CJ\Coupons\Helper\Data $dataHelper
    ) {
        $this->couponRenderer = $couponRenderer;
        $this->quoteRepository = $quoteRepository;
        $this->filterCoupons = $filterCoupons;
        $this->quoteCouponStorage = $quoteCouponStorage;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param CouponManagementInterface $subject
     * @param int $cartId The cart ID.
     * @param string $couponCode The coupon code data.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSet(CouponManagementInterface $subject, $cartId, $couponCode)
    {
        $multiCouponEnabled = $this->dataHelper->getMultiCouponEnabled();
        if ($multiCouponEnabled) {
            $couponCode = $this->prepareCoupon((int)$cartId, (string)$couponCode);
        }

        $this->quoteCouponStorage->setQuoteCoupons((int)$cartId, $couponCode);

        return [$cartId, $couponCode];
    }

    /**
     * Temporary fix for checkout compatibility
     * Override return type, return accepted coupon codes.
     *
     * @param CouponManagementInterface $subject
     * @param bool $result
     * @param string|int $cartId
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 2.0.0
     * @see \Amasty\Coupons\Api\ApplyCouponsToCartInterface::apply
     */
    public function afterSet(CouponManagementInterface $subject, $result, $cartId)
    {
        return $subject->get($cartId);
    }

    /**
     * @param int $cartId
     * @param string $couponCode
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareCoupon(int $cartId, string $couponCode): string
    {
        $renderedCodes = $this->couponRenderer->render($couponCode);

        if (!empty($renderedCodes)) {
            $quote = $this->quoteRepository->getActive($cartId);
            $renderedCodes = $this->filterCoupons->validationFilter($renderedCodes, (int)$quote->getCustomerId());
        }

        return implode(',', $renderedCodes);
    }
}
