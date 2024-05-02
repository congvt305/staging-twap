<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 15/12/20
 * Time: 11:04 AM
 */
namespace Amore\CustomerRegistration\Model\Config\Backend\Serialized;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ArraySerialized
 *
 * Validate config value
 */
class ArraySerialized extends ConfigValue
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ArraySerialized constructor.
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate before save config value
     * @return ArraySerialized|void
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $label = array_column($value, 'label');
        $type = array_column($value, 'type');
        if ($label != array_unique($label) && $type != array_unique($type)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Customer group label and code can not be repeated.')
            );
        }
        unset($value['__empty']);
        $encodedValue = $this->serializer->serialize($value);
        $this->setValue($encodedValue);
    }

    /**
     * Check after load value
     * @return ArraySerialized|void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if ($value) {
            $decodedValue = $this->serializer->unserialize($value);
            $this->setValue($decodedValue);
        }
    }

}
