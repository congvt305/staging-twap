<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 4:54 AM
 */
namespace Eguana\FacebookPixel\Helper;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Data class to get values from configuration
 *
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**#@+
     * Configuration field paths
     */
    const XML_PATH_MODULE_ENABLE        = 'eguana_facebook_pixel/general/enable';
    const XML_PATH_FACEBOOK_PIXED_ID    = 'eguana_facebook_pixel/general/pixel_id';
    const XML_PATH_PRODUCT_VIEW         = 'eguana_facebook_pixel/event_tracking/product_view';
    const XML_PATH_CATEGORY_VIEW        = 'eguana_facebook_pixel/event_tracking/category_view';
    const XML_PATH_PURCHASE             = 'eguana_facebook_pixel/event_tracking/purchase';
    const XML_PATH_ADD_TO_CART          = 'eguana_facebook_pixel/event_tracking/add_to_cart';
    const XML_PATH_INCLUDE_TAX          = 'tax/calculation/price_includes_tax';
    /**#@-*/

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * Store object
     *
     * @var null|Store
     */
    private $store = null;

    /**
     * Tax display flag
     *
     * @var null|int
     */
    private $taxDisplayFlag = null;

    /**
     * Tax catalog flag
     *
     * @var null|int
     */
    private $taxCatalogFlag = null;

    /**
     * Store ID
     *
     * @var null|int
     */
    private $storeId = null;

    /**
     * Base currency code
     *
     * @var null|string
     */
    private $baseCurrencyCode = null;

    /**
     * Current currency code
     *
     * @var null|string
     */
    private $currentCurrencyCode = null;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $taxConfig
     * @param EncoderInterface $jsonEncoder
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $taxConfig,
        EncoderInterface $jsonEncoder,
        CatalogHelper $catalogHelper
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $storeManager;
        $this->taxConfig = $taxConfig;
        $this->jsonEncoder = $jsonEncoder;
        $this->catalogHelper = $catalogHelper;

        parent::__construct($context);
    }

    /**
     * Json encode the data array
     *
     * @param array $data
     * @return string
     */
    public function serializes($data)
    {
        $result = $this->jsonEncoder->encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * Is Tax Config
     *
     * @return Config
     */
    public function isTaxConfig()
    {
        return $this->taxConfig;
    }

    /**
     * Is Module Enabled
     *
     * @param null $scope
     * @return mixed
     */
    public function isModuleEnabled($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Get config facebook pixel id
     *
     * @param null $scope
     * @return mixed
     */
    public function getPixelId($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FACEBOOK_PIXED_ID,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Product View
     *
     * @param null $scope
     * @return mixed
     */
    public function isProductView($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_VIEW,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Purchase
     *
     * @param null $scope
     * @return mixed
     */
    public function isPurchase($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PURCHASE,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Is Add To Cart
     *
     * @param null $scope
     * @return bool
     */
    public function isAddToCart($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADD_TO_CART,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * @param null $scope
     * @return mixed
     */
    public function isIncludeTax($scope = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INCLUDE_TAX,
            ScopeInterface::SCOPE_STORE,
            $scope
        );
    }

    /**
     * Add slashes to string and prepares string for javascript.
     *
     * @param string $str
     * @return string
     */
    public function escapeSingleQuotes($str)
    {
        return str_replace("'", "\'", $str);
    }

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        $currencyCode = '';
        try {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $currencyCode;
    }

    /**
     * Get product price
     *
     * @param Product $product
     * @return float|int|mixed|string
     */
    public function getProductPrice($product)
    {
        $price = 0;
        try {
            switch ($product->getTypeId()) {
                case 'bundle':
                    $price = $this->getBundleProductPrice($product);
                    break;
                case 'configurable':
                    $price = $this->getConfigurableProductPrice($product);
                    break;
                case 'grouped':
                    $price = $this->getGroupedProductPrice($product);
                    break;
                default:
                    $price = $this->getFinalPrice($product);
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $price;
    }

    /**
     * Get bundle product price.
     *
     * @param Product $product
     * @return float|int|string
     */
    private function getBundleProductPrice($product)
    {
        $price = 0;
        try {
            $includeTax = (bool)$this->getDisplayTaxFlag();
            $price = $this->getFinalPrice(
                $product,
                $product->getPriceModel()->getTotalPrices(
                    $product,
                    'min',
                    $includeTax,
                    1
                )
            );
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $price;
    }

    /**
     * Get configurable product price
     *
     * @param Product $product
     * @return float|int|string|null
     */
    private function getConfigurableProductPrice($product)
    {
        try {
            if ($product->getFinalPrice() === 0) {
                $simpleCollection = $product->getTypeInstance()
                    ->getUsedProducts($product);

                foreach ($simpleCollection as $simpleProduct) {
                    if ($simpleProduct->getPrice() > 0) {
                        return $this->getFinalPrice($simpleProduct);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $this->getFinalPrice($product);
    }

    /**
     * Get grouped product price
     * @param Product $product
     * @return mixed
     */
    private function getGroupedProductPrice($product)
    {
        $assocProducts = $product->getTypeInstance(true)
            ->getAssociatedProductCollection($product)
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('tax_class_id')
            ->addAttributeToSelect('tax_percent');

        $minPrice = INF;
        foreach ($assocProducts as $assocProduct) {
            $minPrice = min($minPrice, $this->getFinalPrice($assocProduct));
        }

        return $minPrice;
    }

    /**
     * Returns final price.
     *
     * @param Product $product
     * @param string $price
     * @return string|int|mmixed|null
     */
    private function getFinalPrice($product, $price = null)
    {
        try {
            $price = $this->resultPriceFinal($product, $price);

            $productType = $product->getTypeId();
            //  Apply tax if needed
            // Simple, Virtual, Downloadable products price is without tax
            // Grouped products have associated products without tax
            // Bundle products price already have tax included/excluded
            // Configurable products price already have tax included/excluded
            if ($productType != 'configurable' && $productType != 'bundle') {
                // If display tax flag is on and catalog tax flag is off
                if ($this->getDisplayTaxFlag() && !$this->getCatalogTaxFlag()) {
                    $price = $this->catalogHelper->getTaxPrice(
                        $product,
                        $price,
                        true,
                        null,
                        null,
                        null,
                        $this->getStoreId(),
                        false,
                        false
                    );
                }
            }

            // Case when catalog prices are with tax but display tax is set to
            // to exclude tax. Applies for all products except for bundle
            if ($productType != 'bundle') {
                // If display tax flag is off and catalog tax flag is on
                if (!$this->getDisplayTaxFlag() && $this->getCatalogTaxFlag()) {
                    $price = $this->catalogHelper->getTaxPrice(
                        $product,
                        $price,
                        false,
                        null,
                        null,
                        null,
                        $this->getStoreId(),
                        true,
                        false
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $price;
    }

    /**
     * Result price final
     *
     * @param Product $product
     * @param float|int $price
     * @return float|int|mixed
     */
    private function resultPriceFinal($product, $price)
    {
        try {
            if ($price === null) {
                $price = $product->getFinalPrice();
            }

            if ($price === null) {
                $price = $product->getData('special_price');
            }
            $productType = $product->getTypeId();
            // 1. Convert to current currency if needed

            // Convert price if base and current currency are not the same
            // Except for configurable products they already have currency converted
            if (($this->getBaseCurrencyCode() !== $this->getCurrentCurrencyCode())
                && $productType != 'configurable'
            ) {
                // Convert to from base currency to current currency
                $price = $this->getStore()->getBaseCurrency()
                    ->convert($price, $this->getCurrentCurrencyCode());
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $price;
    }

    /**
     * Returns flag based on "Stores > Configuration > Sales > Tax
     * > Price Display Settings > Display Product Prices In Catalog"
     * Returns 0 or 1 instead of 1, 2, 3.
     * @return int|null
     */
    private function getDisplayTaxFlag()
    {
        if ($this->taxDisplayFlag === null) {
            try {
                // Tax Display
                // 1 - excluding tax
                // 2 - including tax
                // 3 - including and excluding tax
                $flag = $this->isTaxConfig()->getPriceDisplayType($this->getStoreId());

                // 0 means price excluding tax, 1 means price including tax
                if ($flag == 1) {
                    $this->taxDisplayFlag = 0;
                } else {
                    $this->taxDisplayFlag = 1;
                }
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }

        return $this->taxDisplayFlag;
    }

    /**
     * Returns Stores > Configuration > Sales > Tax > Calculation Settings
     * > Catalog Prices configuration value
     *
     * @return int|null
     */
    private function getCatalogTaxFlag()
    {
        try {
            // Are catalog product prices with tax included or excluded?
            if ($this->taxCatalogFlag === null) {
                $this->taxCatalogFlag = (int)$this->isIncludeTax();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        // 0 means excluded, 1 means included
        return $this->taxCatalogFlag;
    }

    /**
     * Get Store
     *
     * @return StoreInterface|Store|null
     */
    public function getStore()
    {
        try {
            if ($this->store === null) {
                $this->store = $this->storeManager->getStore();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $this->store;
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        try {
            if ($this->storeId === null) {
                $this->storeId = $this->getStore()->getId();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $this->storeId;
    }

    /**
     * Return base currency code
     *
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        if ($this->baseCurrencyCode === null) {
            $this->baseCurrencyCode = strtoupper(
                $this->getStore()->getBaseCurrencyCode()
            );
        }

        return $this->baseCurrencyCode;
    }

    /**
     * Return current currency code
     *
     * @return string|null
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->currentCurrencyCode === null) {
            $this->currentCurrencyCode = strtoupper(
                $this->getStore()->getCurrentCurrencyCode()
            );
        }

        return $this->currentCurrencyCode;
    }
}
