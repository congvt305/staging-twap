<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sapt\Catalog\Block\ConfigurableProduct\Product\View\Type;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManagerInterface;

class Configurable
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    protected $jsonEncoder;
    protected $jsonDecoder;
    protected $stockRegistry;

    public function __construct(
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager
    ) {

        $this->jsonDecoder = $jsonDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
    }

    // Adding Quantitites (product=>qty)
    public function aroundGetJsonConfig(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        \Closure $proceed
    )
    {
        $config = $proceed();

        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $quantities = [];
            $config = $this->jsonDecoder->decode($config);
            foreach ($subject->getAllowProducts() as $product) {
                $stockitem = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                $quantities[$product->getId()] = $stockitem->getQty();
            }

            $config['quantities'] = $quantities;
            $config = $this->jsonEncoder->encode($config);
        }

        return $config;
    }
}
