<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Sapt\Review\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Helper\Data as ReviewHelper;

class Recent extends ListCustomer
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = [],
        ?ReviewHelper $reviewHelper = null
    ) {
        $data['reviewHelper'] = $reviewHelper ?? ObjectManager::getInstance()->get(ReviewHelper::class);
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $collectionFactory,
            $currentCustomer,
            $orderCollectionFactory,
            $orderConfig,
            $productRepository,
            $data,
            $reviewHelper
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
