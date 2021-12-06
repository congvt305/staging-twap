<?php

namespace CJ\PointRedemption\Plugin;

use Laminas\Di\Exception\LogicException;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use \CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use \Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class PointValidation
{
    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var CustomerPointsSearch
     */

    protected CustomerPointsSearch $customerPointsSearch;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var CheckoutSession
     */
    protected CheckoutSession $checkoutSession;

    public function __construct(
        Session $customerSession,
        CustomerPointsSearch $customerPointsSearch,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        $isLogin = $this->customerSession->isLoggedIn();
        $isRedeemableProduct = $productInfo->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        if ($isRedeemableProduct) {
            if (!$isLogin) {
                throw new LocalizedException(
                    __("Please login to redeem this product")
                );//todo translate
            }
            $pointAmount = $productInfo->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE);
            $this->validate($pointAmount);
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param $pointAmount
     * @throws LocalizedException
     */
    private function validate($pointAmount)
    {
        try {
            $customerId = $this->customerSession->getCustomerId();
            $websiteId = $this->customerSession->getCustomer()->getWebsiteId();
            $memberPointInfo = $this->customerPointsSearch->getMemberSearchResult($customerId, $websiteId);
            if (!isset($memberPointInfo['data']['availablePoint'])) {
                throw new LocalizedException(
                    __(
                        "Point service is not available now, please try later. Sorry for the inconvenient"
                    )
                );
            }
            $balanceAmount = (int)$memberPointInfo['data']['availablePoint'];
            $usedAmount = $this->getUsedPointAmount();
            if ($balanceAmount - $usedAmount < $pointAmount) {
                throw new LocalizedException(
                    __(
                        "Your point balance is not enough to redeem this product"
                    )
                );
                //todo translate
            }
        } catch (LogicException $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * @return float|int|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getUsedPointAmount()
    {
        $orderAmount = $this->getOrderUsedPointAmount();
        $quoteAmount = $this->getQuoteUsedPointAmount();
        return $orderAmount + $quoteAmount;
    }

    /**
     * @return float|int|mixed
     */
    private function getOrderUsedPointAmount()
    {
        $usedAmount = 0;
        // Get orders which have not been synced yet.
        $searchCriteriaBuilder = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $this->customerSession->getCustomerId(), 'eq')
            ->addFilter('pos_order_paid_sent', 0, 'eq')
            ->addFilter('status', ['canceled', 'closed'], 'nin')
            ->create();

        $orders = $this->orderRepository->getList($searchCriteriaBuilder)->getItems();
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                $pointAmount = $item->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE);
                $isRedeemableProduct = $item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
                if ($isRedeemableProduct && $pointAmount) {
                    $usedAmount = $usedAmount + ($pointAmount * $item->getQty());
                }
            }
        }
        return $usedAmount;
    }

    /**
     * @return float|int|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getQuoteUsedPointAmount()
    {
        $usedAmount = 0;
        $quote = $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $pointAmount = $item->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE);
            $isRedeemableProduct = $item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
            if ($isRedeemableProduct && $pointAmount) {
                $usedAmount = $usedAmount + ($pointAmount * $item->getQty());
            }
        }

        return $usedAmount;
    }
}
