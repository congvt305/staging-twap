<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Config\Backend;

use Ecpay\Ecpaypayment\Block\Adminhtml\Config\Backend\DatePicker;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;

class ExcludeDaysMultipleFields extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ShippingMethods constructor
     *
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
     * On save convert front value format like "12/01/2018" to backend format "2018-01-12"
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = [];
        $values = $this->getValue();
        foreach ((array)$values as $key => $data) {
            if ($key == '__empty') {
                continue;
            }
            if (!isset($data['date'])) {
                continue;
            }
            try {
                $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
                $value[$key] = [
                    'date' => $date->format('Y-m-d'),
                    'content' => $data['content'],
                ];
            } catch (\Exception $e) {
                // Just skipping error values
            }
        }
        $encodedValue = $this->serializer->serialize($value);
        $this->setValue($encodedValue);
        return parent::beforeSave();
    }
}
