<?php

namespace CJ\CatalogProduct\Plugin\Checkout\Model\Cart;

use CJ\CatalogProduct\Helper\Data;
use Magento\Bundle\Model\Product\SingleChoiceProvider;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Satp\Catalog\Helper\OptionData;

class BundleOptions
{
    /**
     * @var OptionData
     */
    private $optionData;

    /**
     * @var SingleChoiceProvider
     */
    private $singleChoiceProvider;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param OptionData $optionData
     * @param SingleChoiceProvider $singleChoiceProvider
     * @param Data $helperData
     */
    public function __construct(
        OptionData $optionData,
        SingleChoiceProvider $singleChoiceProvider,
        Data $helperData
    ) {
        $this->optionData           = $optionData;
        $this->singleChoiceProvider = $singleChoiceProvider;
        $this->helperData           = $helperData;
    }

    /**
     * @param Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        if ($productInfo->getTypeId() == Type::TYPE_BUNDLE
            && $this->helperData->isAllowQuickBuyBundleProduct()
            && empty($requestInfo['bundle_option'])) {
            $bundleOptionsData = $this->getOptionsData($productInfo);
            foreach ($bundleOptionsData as $optionData) {
                $requestInfo['bundle_option'][$optionData['option_id']] = $optionData['selection_id'];
            }
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * @param $product
     * @return array
     */
    private function getOptionsData($product)
    {
        $result = [];
        if ($this->singleChoiceProvider->isSingleChoiceAvailable($product) === true) {
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);
            $options = $typeInstance->getOptions($product);
            foreach ($options as $option) {
                $optionId = $option->getId();
                $selectionsCollection = $typeInstance->getSelectionsCollection(
                    [$optionId],
                    $product
                );
                $selections = $selectionsCollection->exportToArray();
                foreach ($selections as $selection) {
                    $result[] = [
                        'option_id'    => $optionId,
                        'selection_id' => $selection['selection_id']
                    ];
                }
            }
        }

        return $result;
    }
}
