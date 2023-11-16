<?php

namespace Amasty\CPS\Controller\Adminhtml\Product;

class Search extends ControllerAbstract
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $searchQuery = $this->getRequest()->getParam('search_query');
        $storeId = (int) $this->getRequest()->getParam('store');
        $sortOrder = $this->getRequest()->getParam('sort_order', false);
        $this->dataProvider->setSortOrder($sortOrder)->setStoreId($storeId);

        $block = $this->layoutFactory->create()->createBlock(
            \Amasty\CPS\Block\Adminhtml\Products\Listing::class,
            'product.listing'
        );

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $block->search($searchQuery)->toHtml()
        );
    }
}
