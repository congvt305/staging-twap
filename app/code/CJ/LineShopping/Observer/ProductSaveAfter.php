<?php

namespace CJ\LineShopping\Observer;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Framework\Event\ObserverInterface;
use CJ\LineShopping\Logger\Logger;
use Exception;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var ProductAction
     */
    protected ProductAction $productAction;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param ProductAction $productAction
     */
    public function __construct(
        Logger $logger,
        ProductAction $productAction
    ) {
        $this->logger = $logger;
        $this->productAction = $productAction;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            foreach ($product->getStoreIds() as $storeId) {
                $this->productAction->updateAttributes([$product->getEntityId()], array('is_modify' => true), $storeId);
            }
        } catch (Exception $exception) {
            $this->logger->error(Logger::EXPORT_FEED_DATA,
                [
                    'type' => 'Update Product',
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
