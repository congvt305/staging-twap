<?php

namespace Amore\SalesRule\Override\Model;

use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\Store\Model\StoreManagerInterface;

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

            $item->setDiscountAmount((int) $discountData->getAmount());
            $item->setBaseDiscountAmount((int) $discountData->getBaseAmount());
            $item->setOriginalDiscountAmount((int) $discountData->getOriginalAmount());
            $item->setBaseOriginalDiscountAmount((int) $discountData->getBaseOriginalAmount());

            return $this;
        } else {
            parent::setDiscountData($discountData, $item);
        }
    }
}
