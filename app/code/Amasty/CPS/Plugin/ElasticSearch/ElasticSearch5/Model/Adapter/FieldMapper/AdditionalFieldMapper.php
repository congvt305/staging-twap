<?php

namespace Amasty\CPS\Plugin\ElasticSearch\ElasticSearch5\Model\Adapter\FieldMapper;

use Amasty\ShopbyBrand\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Psr\Log\LoggerInterface;

class AdditionalFieldMapper
{
    public const ES_DATA_TYPE_INTEGER = 'integer';
    public const FIELD_NAME_POSITION_TEMPLATE = 'brand_position_%s';
    public const FIELD_NAME = 'ambrand_id';

    /**
     * @var string
     */
    private $brandAttributeCode;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Data $brandHelper,
        Repository $attributeRepository,
        LoggerInterface $logger
    ) {
        $this->brandAttributeCode = $brandHelper->getBrandAttributeCode();
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result)
    {
        if ($this->brandAttributeCode !== null) {
            $result[self::FIELD_NAME] = ['type' => self::ES_DATA_TYPE_INTEGER];
            $options = $this->attributeRepository->get($this->brandAttributeCode)->getOptions();

            foreach ($options as $option) {
                $result[sprintf(self::FIELD_NAME_POSITION_TEMPLATE, $option->getValue())] = [
                    'type' => self::ES_DATA_TYPE_INTEGER
                ];
            }
        } else {
            $this->logger->notice(
                __('Amasty Custom Product Sorting indexation error: Brand attribute is not set')->render()
            );
        }

        return $result;
    }
}
