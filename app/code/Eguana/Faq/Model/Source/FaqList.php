<?php
declare(strict_types=1);

namespace Eguana\Faq\Model\Source;

use Eguana\Faq\Api\Data\FaqInterface;
use Eguana\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;

class FaqList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const USE_IN_CATALOG = 1;

    const IS_USE_IN_CATALOG_COLUMN = 'is_use_in_catalog';
    /**
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $faqCollectionFactory;

    /**
     * @param CollectionFactory $faqCollectionFactory
     * @param StoreManagerInterfaceAlias $storeManager
     */
    public function __construct(
        CollectionFactory $faqCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $categoryList = [];
        $storeId = $this->storeManager->getStore()->getId();

        /** @var  \Eguana\Faq\Model\ResourceModel\Faq\Collection $faqCollection */
        $faqCollection = $this->faqCollectionFactory->create();
        $faqCollection->join(
            ['efs' => 'eguana_faq_store'],
            'main_table.entity_id = efs.entity_id',
            []
        );
        $faqCollection->addFieldtoFilter('is_use_in_catalog', self::USE_IN_CATALOG);
        $faqCollection->addFieldtoFilter(FaqInterface::IS_ACTIVE, true);
        if ($storeId == 0) {
            $storeFilter = array_keys($this->storeManager->getStores());
        } else {
            $storeFilter = [$storeId];
        }
        $faqCollection->addFieldtoFilter('efs.store_id', ['in' => $storeFilter])->distinct('entity_id');

        foreach ($faqCollection as $faq) {
            $categoryList[] =
                [
                    'label' => $faq->getTitle(),
                    'value' => $faq->getId()
                ];
        }
        return $categoryList;
    }

    /**
     * Get all options
     *
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array|null
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->toOptionArray();
        }
        return $this->_options;
    }
}
