<?php
namespace Amore\SalesRule\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Model\Rule as SalesRule;

use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Store\Model\StoreManagerInterface;

class Rule
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->storeManager = $storeManager;
    }

    /**
     * Before plugin needed for existing and new rules alike
     * @param SalesRule $subject
     * @return array
     */
    public function beforeSave(SalesRule $subject)
    {
        if (in_array($subject->getSimpleAction(), \Amasty\Promo\Observer\Salesrule\Discount::PROMO_RULES) && $subject->getSimpleAction() != \Amasty\Promo\Model\Rule::SAME_PRODUCT) {
            $subject->setData(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_FREE_SHIPPING, 0);
            //validate input promo items before save to make sure when apply rule in front end do got get error
            if ($subject->getExtensionAttributes() && isset($subject->getExtensionAttributes()['ampromo_rule']['sku'])) {
                $skuArr = explode(',', $subject->getExtensionAttributes()['ampromo_rule']['sku']);
                foreach($subject->getWebsiteIds() as $websiteId) {
                    $storeIdArr = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
                    foreach ($storeIdArr as $storeId) {
                        $collection = $this->collectionFactory->create();
                        $collection->getSelect()->reset('columns')->columns(['sku']);
                        $collection->addStoreFilter($storeId);
                        $collection->addFieldToFilter('sku', ['in' => $skuArr]);
                        $collection->addFieldToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        $skuData = [];
                        foreach ($collection->getItems() as $item) {
                            $skuData[] = $item->getSku();
                        }
                        if ($diff = array_diff($skuArr, $skuData)) {
                            $websiteName = $this->storeManager->getWebsite($websiteId)->getName();
                            throw new LocalizedException(__('Promo Items: %1 do not exist or be disabled on %2. Please check SKUs again.', implode(',', $diff), $websiteName));
                        }
                    }
                }
            }
        }
        return [];
    }
}
