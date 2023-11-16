<?php

namespace Amasty\CPS\Controller\Adminhtml\Product;

class Save extends ControllerAbstract
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->pinProduct();
        $this->moveToPage();
        $this->moveToTop();
        $this->unPinProduct();
        $this->setSortOrder();

        return $this->resultJsonFactory->create()->setData([]);
    }

    protected function moveToPage()
    {
        $moveProductData = $this->getRequest()->getParam('move_product_data', []);
        if (isset($moveProductData['entity_id'])
            && isset($moveProductData['destination_position'])
        ) {
            $this->dataProvider->setProductPositionData(
                [$moveProductData['entity_id'] => $moveProductData['destination_position']],
                -1
            );
        }
    }

    protected function moveToTop()
    {
        $moveProductData = $this->getRequest()->getParam('top_product_data', []);
        if (isset($moveProductData['entity_id'])) {
            $this->dataProvider->setProductPositionData(
                [$moveProductData['entity_id'] => 0]
            );
        }
    }

    protected function setSortOrder()
    {
        $sortOrder = $this->getRequest()->getParam('sort_order');
        if ($sortOrder !== null) {
            $this->dataProvider->setSortOrder($sortOrder);
            $this->brandProduct->sortProductsByOrder();
        }
    }

    protected function unPinProduct()
    {
        $automaticProductData = $this->getRequest()->getParam('automatic_product_data', []);
        if ($automaticProductData && isset($automaticProductData['entity_id'])) {
            $this->dataProvider->unsetProductPositionData($automaticProductData['entity_id']);
        }
    }

    protected function pinProduct()
    {
        $positions = $this->getRequest()->getParam('positions', []);
        if (!empty($positions)) {
            $this->dataProvider->setProductPositionData($positions);
        }
    }
}
