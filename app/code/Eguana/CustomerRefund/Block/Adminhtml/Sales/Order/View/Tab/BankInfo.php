<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 9:33 AM
 */

namespace Eguana\CustomerRefund\Block\Adminhtml\Sales\Order\View\Tab;


use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class BankInfo extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Eguana_CustomerRefund::sales/order/view/tab/bankinfo.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $adminHelper;
    /**
     * @var \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface
     */
    private $bankInfoRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $bankInfoRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->adminHelper = $adminHelper;
        $this->bankInfoRepository = $bankInfoRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }


    public function getBankInformations()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $this->getOrder()->getId())
            ->create();
       return  $this->bankInfoRepository->getList($searchCriteria)->getItems();
    }

    /**
     * Status history date/datetime getter
     *
     * @param array $item
     * @param string $dateType
     * @param int $format
     * @return string
     */
    public function getItemCreatedAt(array $item, $dateType = 'date', $format = \IntlDateFormatter::MEDIUM)
    {
        if (!isset($item['created_at'])) {
            return '';
        }
        if ('date' === $dateType) {
            return $this->formatDate($item['created_at'], $format);
        }
        return $this->formatTime($item['created_at'], $format);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Customer Bank Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return __('Customer Bank Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}
