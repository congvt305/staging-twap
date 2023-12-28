<?php
declare(strict_types=1);

namespace Sapt\Review\Block\Customer;

class Recent extends \Magento\Review\Block\Customer\Recent
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $collectionFactory,
            $currentCustomer,
            $data
        );
    }

    /**
     * Return review customer url
     *
     * @return string
     */
    public function getAllReviewsUrl()
    {
        return $this->getUrl('review/customer');
    }
}
