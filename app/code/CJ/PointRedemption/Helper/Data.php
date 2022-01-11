<?php

namespace CJ\PointRedemption\Helper;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;

class Data extends AbstractHelper
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

    /**
     * @var Resolver
     */
    protected $layerResolver;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CustomerPointsSearch $customerPointsSearch
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerPointsSearch $customerPointsSearch,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        CheckoutSession $checkoutSession,
        Resolver $layerResolver,
        Registry $registry,
        RedirectInterface $redirect
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->layerResolver = $layerResolver;
        $this->registry = $registry;
        $this->redirect = $redirect;
    }

    /**
     * @param $validatedAmount
     * @param $quote
     * @return void
     * @throws LocalizedException
     */
    public function validatePointBalance($validatedAmount, $quote = null)
    {
        try {
            $this->loginValidation();
            $balanceAmount = $this->getCustomerPointBalance();
            $usedAmount = $this->getUsedPointAmount($quote);
            if ($balanceAmount - $usedAmount < $validatedAmount) {
                throw new LocalizedException(
                    __(
                        "Your point balance is insufficient."
                    )
                );
            }
        } catch (\Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }
    }

    /**
     * @return float|int|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getUsedPointAmount($quote = null)
    {
        $orderAmount = $this->getOrderUsedPointAmount();
        $quoteAmount = $this->getQuoteUsedPointAmount($quote);
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
    public function getQuoteUsedPointAmount($quote = null)
    {
        $usedAmount = 0;
        $quote = $quote ?? $this->checkoutSession->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $pointAmount = $item->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE);
            $isRedeemableProduct = $item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
            if ($isRedeemableProduct && $pointAmount) {
                $usedAmount = $usedAmount + ($pointAmount * $item->getQty());
            }
        }

        return $usedAmount;
    }

    /**
     * @param $customerId
     * @param $websiteId
     * @return int
     * @throws LocalizedException
     */
    private function getCustomerPointBalance()
    {
        $this->loginValidation();
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

        return (int)$memberPointInfo['data']['availablePoint'];
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function loginValidation()
    {
        $isLogin = $this->customerSession->isLoggedIn();
        if (!$isLogin) {
            throw new LocalizedException(
                __(
                    "Please login into your account to continue shopping"
                )
            );
        }
    }

    /**
     * @return false|mixed|null
     */
    public function isMembershipCategory()
    {
        $currentCategory = $this->layerResolver->get()->getCurrentCategory();
        return $currentCategory ? $currentCategory->getData('is_membership') : false;
    }

    /**
     * @return bool
     */
    public function isAjaxRequestFromPointRedemptionPDP()
    {
        if ($this->_getRequest()->isAjax()) {
            $referer = $this->redirect->getRefererUrl();
            $queryString = 'point=true';
            if (strpos($referer, $queryString) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isPointDisplay()
    {
        return $this->isMembershipCategory() || $this->isAjaxRequestFromPointRedemptionPDP();
    }
}
