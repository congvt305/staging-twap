<?php

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Export\Product;
use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\Catalog\Pricing\Price\SpecialPrice as CatalogSpecialPrice;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Convert\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Calculation\CollectionFactory;

class Price implements RowCustomizerInterface
{
    /**
     * @var array
     */
    protected $prices = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Product
     */
    protected $export;

    /**
     * @var CollectionFactory
     */
    protected $calculationCollectionFactory;

    /**
     * @var DataObject
     */
    protected $objectConverter;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var Http
     */
    private $request;

    public function __construct(
        StoreManagerInterface $storeManager,
        Product $export,
        CollectionFactory $calculationCollectionFactory,
        Calculation $calculation,
        Http $request,
        DataObject $objectConverter
    ) {
        $this->storeManager = $storeManager;
        $this->export = $export;
        $this->calculationCollectionFactory = $calculationCollectionFactory;
        $this->objectConverter = $objectConverter;
        $this->calculation = $calculation;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(Product::PREFIX_PRICE_ATTRIBUTE)) {

            $collection->applyFrontendPriceLimitations();
            $collection->addAttributeToSelect(['special_price', 'special_from_date', 'special_to_date']);

            $storeId = $this->request->getParam('store_id') ?: $collection->getStoreId();

            $currentCurrency = $this->storeManager->getStore()->getCurrentCurrency();
            $this->storeManager->setCurrentStore($storeId);
            $this->storeManager->getStore()->setCurrentCurrency($this->storeManager->getStore()->getBaseCurrency());

            foreach ($collection as &$item) {
                $addressRequestObject = $this->calculation->getDefaultRateRequest($storeId);
                $addressRequestObject->setProductClassId($item->getTaxClassId());

                $taxPercent = $this->calculation->getRate($addressRequestObject);
                $finalPrice = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();

                if (null === $finalPrice || 0.0001 > $finalPrice) {
                    $item->load($item->getId());

                    if ($specialPrice = $item->getPriceInfo()->getPrice(CatalogSpecialPrice::PRICE_CODE)->getValue()) {
                        $finalPrice = $specialPrice;
                    } else {
                        $finalPrice = $item->getPriceInfo()->getPrice(CatalogFinalPrice::PRICE_CODE)->getValue();
                    }
                }

                $this->prices[$item['entity_id']] = [
                    'final_price' => $finalPrice,
                    'price' => $item['price'],
                    'min_price' => $item['min_price'],
                    'max_price' => $item['max_price'],
                    'tax_price' => $taxPercent != 0 ?
                        ($item['price'] + $item['price'] * $taxPercent / 100)
                        : $item['price'],
                    'tax_final_price' => $taxPercent != 0 ?
                        ($finalPrice + $finalPrice * $taxPercent / 100)
                        : $finalPrice,
                    'tax_min_price' => $taxPercent != 0 ?
                        ($item['min_price'] + $item['min_price'] * $taxPercent / 100)
                        : $item['min_price']
                ];
            }

            $this->storeManager->getStore()->setCurrentCurrency($currentCurrency);
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $customData[Product::PREFIX_PRICE_ATTRIBUTE]
            = isset($this->prices[$productId]) ? $this->prices[$productId]
            : [];

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
