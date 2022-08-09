<?php

namespace CJ\ProductInventoryQty\Controller\Index;

use CJ\ProductInventoryQty\Helper\Data;
use CJ\ProductInventoryQty\Model\ProductTypeFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Index
 * @package CJ\ProductInventoryQty\Controller\Index
 */
class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $resultJsonFactory;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var ProductTypeFactory
     */
    protected ProductTypeFactory $productTypeFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Data $helper,
        ProductTypeFactory $productTypeFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        $this->productTypeFactory = $productTypeFactory;
        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $param = $this->getRequest()->getParam('entity_id');
        $data = [];
        try {
            if (!empty($param)) {
                $product = $this->productTypeFactory->create()->getProductById($param);
                $data = [
                    'qty' => $this->helper->getStockQty($product->getSku(), $product->getTypeId()),
                    'type' => $product->getTypeId()
                ];
            } else {
                $data = [
                    'error' => __('Please enter entity_id param')
                ];
            }
        } catch (LocalizedException $exception) {
            $data = [
                'error' => $exception->getMessage()
            ];
        }
        $result->setData($data);

        return $result;
    }
}
