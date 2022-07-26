<?php

namespace CJ\Review\Block\Top;

use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use CJ\CouponCustomer\Helper\Data as CouponHelper;
use Amore\PointsIntegration\Model\CustomerPointsSearch;

class Info extends Template
{

    protected $_orderCustomerCollection;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CouponHelper
     */
    protected $couponHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * @param Template\Context $context
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $customerSession
     * @param CouponHelper $couponHelper
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context  $context,
        CollectionFactory $orderCollectionFactory,
        Session           $customerSession,
        CouponHelper      $couponHelper,
        Json              $json,
        CustomerPointsSearch $customerPointsSearch,
        array             $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->couponHelper = $couponHelper;
        $this->json = $json;
        $this->customerPointsSearch = $customerPointsSearch;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer(): \Magento\Customer\Model\Customer
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return int
     */
    public function getCountCoupon(): int
    {
        return $this->couponHelper->getCustomerAvailableCoupons()->getSize();
    }

    /**
     * @return int
     */
    public function getCountOrderReviewAvailable(): int
    {
        $orderCollection = $this->getOrderCollection();
        $orderCollection->addFieldToFilter('reviewed', 0);
        $orderCollection->addFieldToFilter('status', 'complete');
        return $orderCollection->getSize();
    }

    /**
     * @return int
     */
    public function getCountOrderReviewed(): int
    {
        $orderCollection = $this->getOrderCollection();
        $orderCollection->addFieldToFilter('reviewed', 1);
        return $orderCollection->getSize();
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrderCollection(): \Magento\Sales\Model\ResourceModel\Order\Collection
    {
        return $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('status')
            ->addFieldToFilter('customer_id', $this->getCustomer()->getId());
    }

    /**
     * @return int
     */
    public function getPoint(): int
    {
        $customer = $this->getCustomer();
        $customerPointsInfo = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
        if(!$customerPointsInfo)
        {
            return 0;
        }else{
            return (int) $customerPointsInfo['data']['availablePoint'] ?? 0;
        }
    }
}
