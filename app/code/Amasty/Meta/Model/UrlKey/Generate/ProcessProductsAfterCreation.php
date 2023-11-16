<?php

declare(strict_types=1);

namespace Amasty\Meta\Model\UrlKey\Generate;

use Amasty\Meta\Helper\UrlKeyHandler;
use Amasty\Meta\Model\ConfigProvider;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class ProcessProductsAfterCreation
{
    /**
     * @var ProductsToUpdate
     */
    private $productsToUpdate;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var UrlKeyHandler
     */
    private $urlKeyHandler;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ProductsToUpdate $productsToUpdate,
        ConfigProvider $configProvider,
        UrlKeyHandler $urlKeyHandler,
        StoreManagerInterface $storeManager
    ) {
        $this->productsToUpdate = $productsToUpdate;
        $this->configProvider = $configProvider;
        $this->urlKeyHandler = $urlKeyHandler;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->productsToUpdate->getProductsToUpdate() as $product) {
            $storeIds = $product->getStoreIds();

            if (!in_array(Store::DEFAULT_STORE_ID, $storeIds)) {
                $storeIds[] = Store::DEFAULT_STORE_ID;
            }

            foreach ($storeIds as $storeId) {
                if ($this->configProvider->isAutomaticallyModifyUrlKey((int)$storeId)) {
                    $store = $this->storeManager->getStore($storeId);
                    $this->urlKeyHandler->processProduct($product, $store);
                }
            }
        }

        $this->productsToUpdate->clearProductsArray();
    }
}
