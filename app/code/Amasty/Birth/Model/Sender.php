<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/

namespace Amasty\Birth\Model;

class Sender
{
    protected $_types = [
        'reg',
        'birth',
        'activity',
        'wishlist',
        'regbirth'
    ];

    /**
     * @var \Amasty\Birth\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Sender constructor.
     *
     * @param \Amasty\Birth\Helper\Data $helper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Birth\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
    ) {
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function process()
    {
        foreach ($this->_types as $type) {
            if ($this->_isTypeEnabled($type)) {
                $className = 'Amasty\Birth\Model\Sender\\' . ucfirst($type);
                if (class_exists($className)) {
                    /** @var \Amasty\Birth\Model\Sender\AbstractSender $command */
                    $command = $this->objectManager->create($className);
                    $command->execute();
                }
            }
        }
        $this->_removeOldCoupons();

        return $this;
    }

    protected function _isTypeEnabled($type)
    {
        return $this->helper->getModuleConfig($type . '/enabled');
    }

    protected function _removeOldCoupons()
    {
        $days = intVal($this->helper->getModuleConfig('general/remove_days'));
        if ($days <= 0) {
            return;
        }
        /** @var $collection \Magento\SalesRule\Model\ResourceModel\Rule\Collection */
        $rules = $this->collectionFactory->create()
            ->addFieldToFilter('name', ['like' => 'Special Coupon%'])
            ->addFieldToFilter('from_date', ['lt' => date('Y-m-d', strtotime("-$days days"))]);

        $errors = '';
        foreach ($rules as $rule) {
            try {
                $rule->delete();
            } catch (\Exception $e) {
                $errors .= "\r\nError when deleting rule #" . $rule->getId() . ' : ' . $e->getMessage();
            }
        }

        if ($errors) {
            throw new \Exception($errors);
        }
    }
}
