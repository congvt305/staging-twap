<?php

namespace CJ\AmastyCheckoutCore\Plugin\Coupons;

use Amasty\Coupons\Api\Data\CouponApplyResultInterfaceFactory;
use Amasty\Coupons\Api\Data\CouponApplyResultListInterfaceFactory;
use Amasty\Coupons\Model\CouponRenderer;
use Amasty\Coupons\Model\Quote\CartItemsSnapshotManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ApplyCouponsToCart {

    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var QuoteRepository
     */
    private $_quoteRepository;

    /**
     * @var CouponApplyResultInterfaceFactory
     */
    private $_couponResultFactory;

    /**
     * @var CouponManagementInterface
     */
    private $_couponManagement;

    /**
     * @var CouponRenderer
     */
    private $_couponRenderer;

    /**
     * @var CartItemsSnapshotManager
     */
    private $_cartItemsSnapshotManager;

    /**
     * @var CouponApplyResultListInterfaceFactory
     */
    private $couponApplyResultListFactory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param QuoteRepository $quoteRepository
     * @param CouponApplyResultInterfaceFactory $couponResultFactory
     * @param CouponManagementInterface $couponManagement
     * @param CouponRenderer $couponRenderer
     * @param CartItemsSnapshotManager $cartItemsSnapshotManager
     * @param CouponApplyResultListInterfaceFactory $couponApplyResultListFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        QuoteRepository $quoteRepository,
        CouponApplyResultInterfaceFactory $couponResultFactory,
        CouponManagementInterface $couponManagement,
        CouponRenderer $couponRenderer,
        CartItemsSnapshotManager $cartItemsSnapshotManager,
        CouponApplyResultListInterfaceFactory $couponApplyResultListFactory,
        LoggerInterface $logger
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_quoteRepository = $quoteRepository;
        $this->_couponResultFactory = $couponResultFactory;
        $this->_couponManagement = $couponManagement;
        $this->_couponRenderer = $couponRenderer;
        $this->_cartItemsSnapshotManager = $cartItemsSnapshotManager;
        $this->_couponApplyResultListFactory = $couponApplyResultListFactory;
        $this->_logger = $logger;
    }

    public function aroundApplyToCart(
        $subject,
        callable $proceed,
        int $cartId,
        array $couponCodes
    ) {
        if ($this->_storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return $proceed($cartId, $couponCodes);
        }

        $isEnableMultiCoupon = $this->_scopeConfig->getValue(
            'amcoupons/general/enable_multi_coupons',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
        if($isEnableMultiCoupon) {
            return $proceed($cartId, $couponCodes);
        }
        try {
            $couponCodes = $this->filterCoupons($couponCodes);
            $quote = $this->_quoteRepository->getActive($cartId);
            $quoteItemsSnapshot = $this->_cartItemsSnapshotManager->takeSnapshot($quote);

            try {
                if(count($couponCodes) > 1) {
                    $this->_couponManagement->set($cartId, $couponCodes[1]);
                } else {
                    $this->_couponManagement->set($cartId, implode(',', $couponCodes));
                }
            } catch (NoSuchEntityException $exception) {
                if (!$quote->getItemsCount() || !$quote->getStoreId()) {
                    throw $exception;
                }
            }

            $appliedCodes = $this->_couponRenderer->render($quote->getCouponCode());

            $couponResultItems = [];
            foreach ($couponCodes as $code) {
                $couponKey = $this->_couponRenderer->findCouponInArray($code, $appliedCodes);
                $isApplied = false;
                if ($couponKey !== false) {
                    $code = $appliedCodes[$couponKey];
                    $isApplied = true;
                }

                $couponResultItems[] = $this->_couponResultFactory->create(
                    ['isApplied' => $isApplied, 'code' => $code]
                );
            }

            $result = $this->_couponApplyResultListFactory->create();
            $result->setItems($couponResultItems);
            $result->setIsQuoteItemsChanged(
                !$this->_cartItemsSnapshotManager->isEqualWithSnapshot($quote, $quoteItemsSnapshot)
            );

            return $result;
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return $proceed($cartId, $couponCodes);
    }

    /**
     * @param array $couponCodes
     *
     * @return array
     */
    private function filterCoupons(array $couponCodes): array
    {
        $inputCoupons = [];

        foreach ($couponCodes as $code) {
            if ($this->_couponRenderer->findCouponInArray($code, $inputCoupons) === false) {
                $inputCoupons[] = $code;
            }
        }

        return $inputCoupons;
    }
}
