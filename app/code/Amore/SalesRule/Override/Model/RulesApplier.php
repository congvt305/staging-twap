<?php

namespace Amore\SalesRule\Override\Model;

use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
/**
 * Override Rule applier model
 */
class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    protected $stores = [
        'tw_laneige',
        'vn_laneige',
        'vn_sulwhasoo',
        'default'
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        CalculatorFactory $calculatorFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Utility $utility,
        ChildrenValidationLocator $childrenValidationLocator = null,
        DataFactory $discountDataFactory = null,
        RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        StoreManagerInterface $storeManager,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory = null)
    {
        $this->storeManager = $storeManager;
        parent::__construct($calculatorFactory, $eventManager, $utility, $childrenValidationLocator, $discountDataFactory, $discountInterfaceFactory, $discountDataInterfaceFactory);
    }

    /**
     * Set Discount data and round without decimal
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param AbstractItem $item
     * @return $this
     */
    protected function setDiscountData($discountData, $item)
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        if (in_array($storeCode, $this->stores)) {
            // Calculate and add odd data
            $discountAmount = $discountData->getAmount();
            $baseDiscountAmount = $discountData->getBaseAmount();
           if ($discountAmount > 0 ) {
               $oddDiscountAmount = $discountAmount - ((int) $discountAmount);
               $oddBaseDiscountAmount = $baseDiscountAmount - (int) $baseDiscountAmount;
               if ($oddDiscountAmount > 0) {
                   $item->setOddDiscountAmount($oddDiscountAmount + $item->getOddDiscountAmount());
                   $item->setOddBaseDiscountAmount($oddBaseDiscountAmount + $item->getOddBaseDiscountAmount());
               }
           }

            $item->setDiscountAmount((int) $discountAmount);
            $item->setBaseDiscountAmount((int) $baseDiscountAmount);
            $item->setOriginalDiscountAmount((int) $discountData->getOriginalAmount());
            $item->setBaseOriginalDiscountAmount((int) $discountData->getBaseOriginalAmount());

            return $this;
        } else {
            parent::setDiscountData($discountData, $item);
        }
    }
}
