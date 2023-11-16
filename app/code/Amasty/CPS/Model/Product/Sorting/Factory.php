<?php

namespace Amasty\CPS\Model\Product\Sorting;

class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $className
     * @param array $data
     * @return SortInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($className, array $data = [])
    {
        $sortInstance = $this->objectManager->create('\Amasty\CPS\Model\Product\Sorting\\'.$className, $data);

        if (!$sortInstance instanceof SortInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('%1 doesn\'t implement SortInterface', $className)
            );
        }
        return $sortInstance;
    }
}
