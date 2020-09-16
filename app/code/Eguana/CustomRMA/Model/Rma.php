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
     * Workaround method to check which status needs confirmation email to the customer
     *
     * By design only \Magento\Rma\Model\Item\Attribute\Source\Status::STATE_AUTHORIZED has such email
     * but other statuses also need it
     *
     * @param string $status
     * @return bool
     */
    private function isStatusNeedsAuthEmail($status): bool
    {
        $statusesNeedsEmail = [
            Status::STATE_AUTHORIZED,
            Status::STATE_RECEIVED,
            Status::STATE_APPROVED,
            Status::STATE_REJECTED,
            Status::STATE_DENIED
        ];

        return in_array($status, $statusesNeedsEmail);
    }

    /**
     * Creates rma items collection by passed data
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

                $itemModel->setData($itemPost)->prepareAttributes($itemPost, $key);
                // @codingStandardsIgnoreStart
                $errors = array_merge($itemModel->getErrors(), $errors);
                // @codingStandardsIgnoreEnd
                if ($errors) {
                    $errorKeys['tabs'] = 'items_section';
                }

                $itemModels[] = $itemModel;

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

        return $this->getItems();
    }

}
