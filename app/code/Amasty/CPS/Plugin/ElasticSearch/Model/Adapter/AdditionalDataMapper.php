<?php

namespace Amasty\CPS\Plugin\ElasticSearch\Model\Adapter;

class AdditionalDataMapper
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
     * @param $productId
     * @param array $indexData
     * @param $storeId
     * @param array $context
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        $productId,
        array $indexData,
        $storeId,
        $context = []
    ) {
        $document = $proceed($productId, $indexData, $storeId, $context);

        $brandIdsByProduct = $this->brandProductProvider->getBrandProductsData(
            [$productId],
            $storeId
        );
        if (isset($brandIdsByProduct[$productId]) && !empty($brandIdsByProduct[$productId])) {
            foreach ($brandIdsByProduct[$productId] as $brandId => $position) {
                $document[self::FIELD_NAME][] = $brandId;
                $document[sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $brandId)] = $position;
            }
        }

        return $document;
    }
}
