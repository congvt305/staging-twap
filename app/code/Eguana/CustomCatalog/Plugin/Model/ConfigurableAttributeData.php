<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 22/9/20
 * Time: 6:18 PM
 */
namespace Eguana\CustomCatalog\Plugin\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData as ConfigurableAttributeDataAlias;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ConfigurableAttributeData
 *
 * Show out of stock product prices
 */
class ConfigurableAttributeData
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ConfigurableAttributeData constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get product attributes and add out of stock index for out of stock child prod
     * @param ConfigurableAttributeDataAlias $subject
     * @param callable $proceed
     * @param Product $product
     * @param array $options
     * @return array[]
     */
    public function aroundGetAttributesData(ConfigurableAttributeDataAlias $subject, callable $proceed, Product $product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $attributes[$attributeId] = [
                    'id' => $attributeId,
                    'code' => $productAttribute->getAttributeCode(),
                    'label' => $productAttribute->getStoreLabel($product->getStoreId()),
                    'options' => $attributeOptionsData,
                    'position' => $attribute->getPosition(),
                ];
                $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
            }
        }
        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }

    /**
     * @param $attribute
     * @param $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $storeId = null;
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            if (isset($config[$attribute->getAttributeId()][$optionId])) {
                if (!empty($config[$attribute->getAttributeId()][$optionId][0])) {
                    $productId = $config[$attribute->getAttributeId()][$optionId][0];
                    $product = null;
                    try {
                        /** @var \Magento\Catalog\Model\Product $product */
                        $product = $this->productRepository->getById($productId, false, $storeId);
                    } catch (\Exception $exception) {
                        $this->logger->error($exception->getMessage());
                    }
                    $status = 0;
                    if ($product->isSalable()) {
                        $status = 1;
                    }
                }
            }
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption['label'],
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
                'stockStatus' => $status,
            ];
        }
        return $attributeOptionsData;
    }

    /**
     * @param $attributeId
     * @param $product
     * @return mixed|null
     */
    protected function getAttributeConfigValue($attributeId, $product)
    {
        return $product->hasPreconfiguredValues()
            ? $product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId)
            : null;
    }
}
