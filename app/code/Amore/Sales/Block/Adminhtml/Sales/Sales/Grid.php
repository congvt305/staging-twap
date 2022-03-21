<?php
declare(strict_types=1);

namespace Amore\Sales\Block\Adminhtml\Sales\Sales;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ConfigFactory;

class Grid extends \Magento\Reports\Block\Adminhtml\Sales\Sales\Grid
{
    /**
     * @var ConfigFactory|null
     */
    private $configFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param array $data
     * @param ConfigFactory|null $configFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        array $data = [],
        ConfigFactory $configFactory = null
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $resourceFactory,
            $collectionFactory,
            $reportsData,
            $data,
            $configFactory
        );
        $this->configFactory = $configFactory ?: ObjectManager::getInstance()->get(ConfigFactory::class);
    }

    /**
     * Add new 3 custom column to grid
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->setStoreIds($this->_getStoreIds());
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn(
            'atv',
            [
                'header' => __('ATV'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'atv',
                'total' => 'sum',
                'sortable' => false,
                'renderer' => Currency::class,
                'rate' => $rate,
                'header_css_class' => 'col-atv',
                'column_css_class' => 'col-atv'
            ]
        );
        $this->addColumn(
            'discount_rate',
            [
                'header' => __('Discount Rate (%)'),
                'type' => 'number',
                'index' => 'discount_rate',
                'sortable' => false,
                'header_css_class' => 'col-discount-rate',
                'column_css_class' => 'col-discount-rate'
            ]
        );

        $this->addColumn(
            'sku_value',
            [
                'header' => __('SKU Value'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'sku_value',
                'total' => 'sum',
                'sortable' => false,
                'renderer' => Currency::class,
                'rate' => $rate,
                'header_css_class' => 'col-sku-value',
                'column_css_class' => 'col-sku-value'
            ]
        );

        $this->addColumnsOrder('atv', 'total_canceled_amount');
        $this->addColumnsOrder('discount_rate', 'atv');
        $this->addColumnsOrder('sku_value', 'discount_rate');

        return parent::_prepareColumns();
    }

    /**
     * Return collection name based on report_type
     *
     * @return string
     */
    public function getResourceCollectionName()
    {
        return $this->getFilterData()->getData('report_type') === 'updated_at_order'
            ? \Amore\Sales\Model\ResourceModel\Report\Order\Updatedat\Collection::class
            : \Amore\Sales\Model\ResourceModel\Report\Order\Collection::class;
    }

    /**
     * Filter canceled statuses for orders and remove unecessary for order status
     *
     * @return \Magento\Reports\Block\Adminhtml\Sales\Sales\Grid
     */
    protected function _prepareCollection()
    {
        /** @var DataObject $filterData */
        $filterData = $this->getData('filter_data');
        if (!$filterData->hasData('order_statuses')) {
            $orderConfig = $this->configFactory->create();
            $statusValues = [];
            $canceledStatuses = $orderConfig->getStateStatuses(Order::STATE_CANCELED);
            $statusCodes = array_keys($orderConfig->getStatuses());
            foreach ($statusCodes as $code) {
                if (!isset($canceledStatuses[$code]) && in_array($code, \Amore\Sales\Block\Adminhtml\Report\Filter\Form\Order::SHOW_ORDER_STATUS)) {
                    $statusValues[] = $code;
                }
            }
            $filterData->setData('order_statuses', $statusValues);
        }
        return parent::_prepareCollection();
    }
}
