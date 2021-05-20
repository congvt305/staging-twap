<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: sonia
 * Date: 19. 7. 26
 * Time: 오전 8:36
 */

namespace Eguana\Dhl\Model\Config\Backend;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Tablerate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Eguana\Dhl\Model\ResourceModel\Carrier\TablerateFactory
     */
    protected $_tablerateFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Eguana\Dhl\Model\ResourceModel\Carrier\TablerateFactory $tablerateFactory,
        array $data = [])
    {
        $this->_tablerateFactory = $tablerateFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate $tableRate */
        $tableRate = $this->_tablerateFactory->create();
        $tableRate->uploadAndImport($this);
        return parent::afterSave();
    }

}