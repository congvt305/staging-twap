<?php


namespace Sapt\Sales\Block\Order\History;


use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

/**
 * Sales order view items block.
 *
 * @api
 * @since 100.0.2
 */
class Items extends AbstractItems
{

    /**
     * Order items per page.
     *
     * @var int
     */
    protected $itemsPerPage;

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @param Context $context
     * @param array $data
     * @param CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        array $data = [],
        CollectionFactory $itemCollectionFactory = null
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
        parent::__construct($context, $data);
    }

    /**
     * Determine if the pager should be displayed for order items list.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return bool
     * @since 100.1.7
     */
    public function isPagerDisplayed()
    {
        return false;
    }

    /**
     * Get visible items for current page.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return \Magento\Framework\DataObject[]
     * @since 100.1.7
     */
    public function getItems()
    {
        return $this->createItemsCollection()->getItems();
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    /**
     * Create items collection
     *
     * @return Collection
     */
    protected function createItemsCollection()
    {
        $collection = $this->itemCollectionFactory->create();
        $collection->setOrderFilter($this->getData('order'));

        return $collection;
    }
}
