<?php

namespace Amasty\CPS\Plugin\ElasticSearch\Model\Adapter;

class AdditionalBatchDataMapper
{
    public const FIELD_NAME = 'ambrand_id';
    public const FIELD_NAME_POSITION_TEMPLATE = 'brand_position_%s';
    public const INDEX_DOCUMENT = 'document';

    /**
     * @var \Amasty\CPS\Model\ResourceModel\BrandProduct
     */
    private $brandProductProvider;

    public function __construct(
        \Amasty\CPS\Plugin\ElasticSearch\Model\BrandProductProvider $brandProductProvider
    ) {
        $this->brandProductProvider = $brandProductProvider;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param array $documentData
     * @param $storeId
     * @param array $context
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        array $documentData,
        $storeId,
        $context = []
    ) {
        $documentData = $proceed($documentData, $storeId, $context);
        $brandIdsByProduct = $this->brandProductProvider->getBrandProductsData(
            array_keys($documentData),
            $storeId
        );
        foreach ($documentData as $productId => $document) {
            if (isset($brandIdsByProduct[$productId]) && !empty($brandIdsByProduct[$productId])) {
                foreach ($brandIdsByProduct[$productId] as $brandId => $position) {
                    $document[self::FIELD_NAME][] = $brandId;
                    $document[sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $brandId)] = $position;
                }

                $documentData[$productId] = $document;
            }
        }
        return $documentData;
    }
}
