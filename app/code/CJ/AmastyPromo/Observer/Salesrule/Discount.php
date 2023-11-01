<?php
declare(strict_types=1);

namespace CJ\AmastyPromo\Observer\Salesrule;

use Amasty\Promo\Model\Config;
use Amasty\Promo\Model\DiscountCalculator;
use Amasty\Promo\Model\RuleResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Psr\Log\LoggerInterface;

class Discount extends \Amasty\Promo\Observer\Salesrule\Discount
{
    /**
     * @var DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var State
     */
    private $state;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param ProductRepositoryInterface $productRepository
     * @param DiscountCalculator $discountCalculator
     * @param RuleResolver $ruleResolver
     * @param State $state
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        ProductRepositoryInterface $productRepository,
        DiscountCalculator $discountCalculator,
        RuleResolver $ruleResolver,
        State $state,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->state = $state;
        $this->discountCalculator = $discountCalculator;
        $this->logger = $logger;
        parent::__construct(
            $promoItemHelper,
            $productRepository,
            $discountCalculator,
            $ruleResolver,
            $state,
            $logger,
            $config
        );
    }

    /**
     * @param Observer $observer
     *
     * @return Data|void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Item $item */
            $item = $observer->getItem();
            /** @var \Magento\SalesRule\Model\Rule $rule */
            $rule = $observer->getRule();

            if ($this->checkItemForPromo($rule, $item)) {
                /** @var Data $result */
                $result = $observer->getResult();
                if (!$item->getAmDiscountAmount()) {
                    if (in_array($rule->getSimpleAction(), ['ampromo_product', 'ampromo_cart'])) {
                        // reset discount amount
                        $item->setDiscountAmount(0);
                        $item->setBaseDiscountAmount(0);
                    }
                    $baseDiscount = $this->discountCalculator->getBaseDiscountAmount($observer->getRule(), $item);
                    $discount = $this->discountCalculator->getDiscountAmount($observer->getRule(), $item);

                    $result->setBaseAmount($baseDiscount);
                    $result->setAmount($discount);
                    $item->setAmBaseDiscountAmount($baseDiscount);
                    $item->setAmDiscountAmount($discount);
                } elseif ($this->state->getAreaCode() === Area::AREA_WEBAPI_REST) {
                    $result->setAmount($item->getAmDiscountAmount());
                    $result->setBaseAmount($item->getAmBaseDiscountAmount());
                } elseif (!$result->getBaseAmount()) {
                    //Customize here
                    //Use to re-appply data when goes to checkoout page amasty one_page_checkout because it re-initial item in vendor/amasty/module-one-step-checkout-core/Model/Quote/CheckoutInitialization.php:saveInitial()
                    $result->setAmount($item->getAmDiscountAmount());
                    $result->setBaseAmount($item->getAmBaseDiscountAmount());
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
