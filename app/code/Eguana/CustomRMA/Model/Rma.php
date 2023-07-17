<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 11/9/20
 * Time: 7:19 PM
 */
namespace Eguana\CustomRMA\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Api\RmaAttributesManagementInterface;
use Magento\Rma\Model\Item;
use Magento\Rma\Model\Item\Attribute\Source\Status;
use Magento\Rma\Model\Rma\EntityAttributesLoader;
use Magento\Bundle\Model\Product\Type;

/**
 * Class Rma
 *
 * Add bundle products errors on one line
 */
class Rma extends \Magento\Rma\Model\Rma
{
    /**
     * Serializer instance.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Rma item factory
     *
     * @var \Magento\Rma\Model\ItemFactory
     */
    protected $_rmaItemFactory;

    /**
     * Core session model
     *
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var array
     */
    protected $bundleItemsError = [];

    /**
     * Rma constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Rma\Helper\Data $rmaData
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Rma\Model\ItemFactory $rmaItemFactory
     * @param Item\Attribute\Source\StatusFactory $attrSourceFactory
     * @param \Magento\Rma\Model\GridFactory $rmaGridFactory
     * @param \Magento\Rma\Model\Rma\Source\StatusFactory $statusFactory
     * @param \Magento\Rma\Model\ResourceModel\ItemFactory $itemFactory
     * @param \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $itemsFactory
     * @param \Magento\Rma\Model\ResourceModel\Shipping\CollectionFactory $rmaShippingFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\Quote\Address\RateFactory $quoteRateFactory
     * @param \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $ordersFactory
     * @param \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory
     * @param \Magento\Shipping\Model\ShippingFactory $shippingFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param RmaAttributesManagementInterface $metadataService
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     * @param EntityAttributesLoader|null $attributesLoader
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Rma\Helper\Data $rmaData,
        \Magento\Framework\Session\Generic $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Rma\Model\ItemFactory $rmaItemFactory,
        \Magento\Rma\Model\Item\Attribute\Source\StatusFactory $attrSourceFactory,
        \Magento\Rma\Model\GridFactory $rmaGridFactory,
        \Magento\Rma\Model\Rma\Source\StatusFactory $statusFactory,
        \Magento\Rma\Model\ResourceModel\ItemFactory $itemFactory,
        \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $itemsFactory,
        \Magento\Rma\Model\ResourceModel\Shipping\CollectionFactory $rmaShippingFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\Address\RateFactory $quoteRateFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $ordersFactory,
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Shipping\Model\ShippingFactory $shippingFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RmaAttributesManagementInterface $metadataService,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        EntityAttributesLoader $attributesLoader = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $rmaData,
            $session,
            $storeManager,
            $eavConfig,
            $rmaItemFactory,
            $attrSourceFactory,
            $rmaGridFactory,
            $statusFactory,
            $itemFactory,
            $itemsFactory,
            $rmaShippingFactory,
            $quoteFactory,
            $quoteRateFactory,
            $quoteItemFactory,
            $orderFactory,
            $ordersFactory,
            $rateRequestFactory,
            $shippingFactory,
            $escaper,
            $localeDate,
            $messageManager,
            $metadataService,
            $resource,
            $resourceCollection,
            $data
        );
        $objectManager = ObjectManager::getInstance();
        $this->serializer = $serializer ?: $objectManager->get(Json::class);
        $this->messageManager = $messageManager;
        $this->_rmaItemFactory = $rmaItemFactory;
        $this->_session = $session;
    }

    /**
     * Prepares Item's data
     * @see \Magento\Rma\Model\Rma::_preparePost
     * @param array $item
     * @return array
     */
    protected function _preparePost($item)
    {
        $bundleItemErrors = [];
        $errors = false;
        $preparePost = [];
        $qtyKeys = ['qty_authorized', 'qty_returned', 'qty_approved'];

        ksort($item);
        foreach ($item as $key => $value) {
            if ($key == 'order_item_id') {
                $preparePost['order_item_id'] = (int)$value;
            } elseif ($key == 'qty_requested') {
                $preparePost['qty_requested'] = is_numeric($value) ? $value : 0;
            } elseif (in_array($key, $qtyKeys)) {
                if (is_numeric($value)) {
                    $preparePost[$key] = (double)$value;
                } else {
                    $preparePost[$key] = '';
                }
            } elseif ($key == 'resolution') {
                $preparePost['resolution'] = (int)$value;
            } elseif ($key == 'condition') {
                $preparePost['condition'] = (int)$value;
            } elseif ($key == 'reason') {
                $preparePost['reason'] = (int)$value;
            } elseif ($key == 'reason_other' && !empty($value)) {
                $preparePost['reason_other'] = $value;
            } else {
                $preparePost[$key] = $value;
            }
        }

        $order = $this->getOrder();
        $realItem = $order->getItemById($preparePost['order_item_id']);

        $stat = Status::STATE_PENDING;
        if (!empty($preparePost['status'])) {
            /** @var $status Status */
            $status = $this->_attrSourceFactory->create();
            if ($status->checkStatus($preparePost['status'])) {
                $stat = $preparePost['status'];
            }
        }

        $preparePost['status'] = $stat;

        $preparePost['product_name'] = $realItem->getName();
        $preparePost['product_sku'] = $realItem->getSku();
        $preparePost['product_admin_name'] = $this->_rmaData->getAdminProductName($realItem);
        $preparePost['product_admin_sku'] = $this->_rmaData->getAdminProductSku($realItem);
        $preparePost['product_options'] = $this->serializer->serialize($realItem->getProductOptions());
        $preparePost['is_qty_decimal'] = $realItem->getIsQtyDecimal();

        if ($preparePost['is_qty_decimal']) {
            $preparePost['qty_requested'] = (double)$preparePost['qty_requested'];
        } else {
            $preparePost['qty_requested'] = (int)$preparePost['qty_requested'];

            foreach ($qtyKeys as $key) {
                if (!empty($preparePost[$key])) {
                    $preparePost[$key] = (int)$preparePost[$key];
                }
            }
        }

        if (isset($preparePost['qty_requested']) && $preparePost['qty_requested'] <= 0) {
            $errors = true;
        }

        foreach ($qtyKeys as $key) {
            if (isset($preparePost[$key]) && !is_string($preparePost[$key]) && $preparePost[$key] <= 0) {
                $errors = true;
            }
        }
        if($realItem->getParentItem()){
            if ($realItem->getParentItem()->getProductType() == 'bundle') {
                if ($errors) {
                    $this->bundleItemsError[$realItem->getParentItem()->getProductId()][$realItem->getProductId()] = $preparePost['product_name'];
                }
            }
        } else {
            if ($errors) {
                $this->messageManager->addError(
                    __('There is an error in quantities for item %1.', $preparePost['product_name'])
                );
            }
        }

        return $preparePost;
    }

    /**
     * Creates rma items collection by passed data
     * @see \Magento\Rma\Model\Rma::_createItemsCollection
     * @param array $data
     * @return array|false|\Magento\Rma\Api\Data\ItemInterface[]|Item[]|mixed
     */
    protected function _createItemsCollection($data)
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        $order = $this->getOrder();
        $itemModels = [];
        $errors = [];
        $errorKeys = [];
        $parentId = '';
        foreach ($data['items'] as $key => $item) {
            if (isset($item['items'])) {
                $itemModel = $firstModel = false;
                $files = $f = [];
                foreach ($item['items'] as $id => $qty) {
                    if ($itemModel) {
                        $firstModel = $itemModel;
                    }
                    /** @var $itemModel Item */
                    $itemModel = $this->_rmaItemFactory->create();
                    $subItem = $item;
                    unset($subItem['items']);
                    $subItem['order_item_id'] = $id;
                    $subItem['qty_requested'] = $qty;

                    $itemPost = $this->_preparePost($subItem);

                    $f = $itemModel->setData($itemPost)->prepareAttributes($itemPost, $key);

                    /* Copy image(s) to another bundle items */
                    if (!empty($f)) {
                        $files = $f;
                    }
                    if (!empty($files) && $firstModel) {
                        foreach ($files as $code) {
                            $itemModel->setData($code, $firstModel->getData($code));
                        }
                    }
                    // @codingStandardsIgnoreStart
                    $errors = array_merge($itemModel->getErrors(), $errors);
                    // @codingStandardsIgnoreEnd

                    $itemModels[] = $itemModel;
                }
            } else {
                /** @var $itemModel Item */
                $itemModel = $this->_rmaItemFactory->create();
                if (isset($item['entity_id']) && $item['entity_id']) {
                    $itemModel->load($item['entity_id']);
                    if ($itemModel->getEntityId()) {
                        if (empty($item['reason'])) {
                            $item['reason'] = $itemModel->getReason();
                        }

                        if (empty($item['reason_other'])) {
                            $item['reason_other'] =
                                $itemModel->getReasonOther() === null ? '' : $itemModel->getReasonOther();
                        }

                        if (empty($item['condition'])) {
                            $item['condition'] = $itemModel->getCondition();
                        }

                        if (empty($item['qty_requested'])) {
                            $item['qty_requested'] = $itemModel->getQtyRequested();
                        }
                    }
                }

                $itemPost = $this->_preparePost($item);
                $itemPost['rma_entity_id'] = $itemModel->getRmaEntityId();
                $itemModel->setData($itemPost)->prepareAttributes($itemPost, $key);
                // @codingStandardsIgnoreStart
                $errors = array_merge($itemModel->getErrors(), $errors);
                // @codingStandardsIgnoreEnd
                if ($errors) {
                    $errorKeys['tabs'] = 'items_section';
                }

                $itemModels[] = $itemModel;
                if ($realItem = $order->getItemById($itemModel->getOrderItemId())) {
                    //set qty_authorized, qty_returned, qty_approved for bundle product
                    if ($realItem->getParentItem() && $realItem->getParentItem()->getProductType() === Type::TYPE_CODE) {
                        $qtyKeys = ['qty_authorized', 'qty_returned', 'qty_approved'];
                        $bundleRmaItem = $this->_itemsFactory->create()
                            ->addFieldToFilter('order_item_id', $realItem->getParentItem()->getItemId())
                            ->addFieldToFilter('rma_entity_id', $itemModel->getRmaEntityId())
                            ->getFirstItem();
                        if ($bundleRmaItem->getId() && $bundleRmaItem->getId() != $parentId && !array_key_exists($bundleRmaItem->getId(), $data['items'])) {
                            $parentId = $bundleRmaItem->getId();
                            foreach ($qtyKeys as $qtyKey) {
                                if ($itemModel->getData($qtyKey)) {
                                    $bundleRmaItem->setData($qtyKey, $bundleRmaItem->getQtyRequested());
                                }
                                $bundleRmaItem->setData($key, $itemModel->getData($key));
                            }
                            $bundleRmaItem->setStatus($itemModel->getStatus());
                            $itemModels[] = $bundleRmaItem;
                        }
                    }
                    //Add children item of bundle product
                    if (!$itemModel->getEntityId() && $realItem->getProductType() === Type::TYPE_CODE) {
                        $orderItemIds = array_column($data, 'order_item_id');

                        //Ignore if item exist in request
                        foreach ($this->getBundleChilds($item, $realItem->getItemId()) as $bundleItem) {
                            if (!in_array($bundleItem->getOrderItemId(), $orderItemIds)) {
                                $itemModels[] = $bundleItem;
                            }
                        }
                    }
                }

                if ($this->isStatusNeedsAuthEmail($itemModel->getStatus())
                    && $itemModel->getOrigData(
                        'status'
                    ) !== $itemModel->getStatus()
                ) {
                    $this->setIsSendAuthEmail(1);
                }
            }
        }
        foreach ($this->bundleItemsError as $childItems) {
            $message = '';
            $message = implode(',', $childItems);
            $this->messageManager->addError(__('There is an error in quantities for item %1.', $message));
        }
        $result = $this->_checkPost($itemModels, $order->getId());
        if ($result !== true) {
            list($result, $errorKey) = $result;
            $errors = array_merge($result, $errors);
            $errorKeys = array_merge($errorKey, $errorKeys);
        }

        $eMessages = $this->messageManager->getMessages()->getErrors();
        if (!empty($errors) || !empty($eMessages)) {
            $this->_session->setRmaFormData($data);
            if (!empty($errorKeys)) {
                $this->_session->setRmaErrorKeys($errorKeys);
            }
            if (!empty($errors)) {
                foreach ($errors as $message) {
                    $this->messageManager->addError($message);
                }
            }
            return false;
        }
        $this->setItems($itemModels);

        $itemStatuses = [];
        foreach ($this->getItems() as $rmaItem) {
            $itemStatuses[] = $rmaItem->getData('status');
        }
        $this->setStatus($this->_statusFactory->create()->getStatusByItems($itemStatuses))->setIsUpdate(1);
        return $this->getItems();
    }

    /**
     * @param array $item
     * @param int $orderItemId
     * @return Item[]
     */
    private function getBundleChilds($item, $orderItemId)
    {
        $result = [];
        foreach ($this->getOrder()->getItems() as $orderItem) {
            if ($orderItem->getParentItemId() == $orderItemId) {
                $itemModel = $this->_rmaItemFactory->create();
                $item['order_item_id'] = $orderItem->getItemId();
                $item['qty_requested'] = $itemModel->getReturnableQty($this->getOrder()->getId(), $orderItem->getItemId());
                $itemPost = $this->_preparePost($item);
                $key = 'bundle_child_'.$orderItem->getItemId();//Key using for matching $_FILES, this unique string is no-use
                $itemModel->setData($itemPost)->prepareAttributes($itemPost, $key);
                $result[] = $itemModel;
            }
        }
        return $result;
    }

}
