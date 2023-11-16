<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\Sales\Api\OrderRepository;

use Amasty\CheckoutCore\Model\ResourceModel\OrderCustomFields\Collection;
use Amasty\CheckoutCore\Model\ResourceModel\OrderCustomFields\CollectionFactory;
use Amasty\CheckoutGraphQl\Model\Utils\Address\CustomAttributesStorage;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddCAToStorage
{
    /**
     * @var CollectionFactory
     */
    private $orderCustomFieldsCollection;

    /**
     * @var CustomAttributesStorage
     */
    private $customAttributesStorage;

    public function __construct(
        CollectionFactory $orderCustomFieldsCollection,
        CustomAttributesStorage $customAttributesStorage
    ) {
        $this->orderCustomFieldsCollection = $orderCustomFieldsCollection;
        $this->customAttributesStorage = $customAttributesStorage;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $orderSearchResult
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $orderSearchResult
    ) {
        $orderIds = $orderSearchResult->getAllIds();

        if (empty($orderIds)) {
            return $orderSearchResult;
        }

        /** @var Collection $orderCustomFieldsCollection */
        $orderCustomFieldsCollection = $this->orderCustomFieldsCollection->create();
        $orderCustomFieldsCollection->addFieldToFilter('order_id', ['in' => $orderIds]);
        $this->customAttributesStorage->setData($orderCustomFieldsCollection->getItems());

        return $orderSearchResult;
    }
}
