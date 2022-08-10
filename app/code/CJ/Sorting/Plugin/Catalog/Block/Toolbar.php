<?php

namespace CJ\Sorting\Plugin\Catalog\Block;
use \Magento\Catalog\Block\Product\ProductList\Toolbar as ProductListToolbar;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Toolbar
 * @package CJ\Sorting\Plugin\Catalog\Block
 */
class Toolbar
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Toolbar constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * @param ProductListToolbar $subject
     * @param \Closure $proceed
     * @param $collection
     * @return mixed
     */
    public function aroundSetCollection(
        ProductListToolbar $subject,
        \Closure $proceed, $collection
    ) {
        if ($this->_storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $currentOrder = $subject->getCurrentOrder();
            if ($currentOrder) {
                if ($currentOrder == 'created_at') {
                    $subject->setData('_current_grid_direction', 'DESC');
                }
            }
        }

        return $proceed($collection);
    }
}
