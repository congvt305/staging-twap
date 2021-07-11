<?php declare(strict_types=1);

namespace Eguana\Dhl\Model\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Tablerate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Eguana\Dhl\Model\ResourceModel\Carrier\TablerateFactory
     */
    private $tablerateFactory;

    /**
     * Tablerate constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Eguana\Dhl\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Eguana\Dhl\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->tablerateFactory = $tablerateFactory;
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate $tableRate */
        $tableRate = $this->tablerateFactory->create();
        $tableRate->uploadAndImport($this);
        return parent::afterSave();
    }
}
