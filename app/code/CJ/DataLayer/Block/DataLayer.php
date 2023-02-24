<?php

namespace CJ\DataLayer\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class DataLayer
 */
class DataLayer extends \Amore\GaTagging\Block\GaTagging
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var  \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    protected $checkoutSession;

    protected $orderRepository;

    protected $categoryRepository;

    protected $selectionCollectionFactory;
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $selectionCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Registry $registry,
        \Amore\GaTagging\Helper\Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($productRepository, $messageManager, $orderRepository, $selectionCollectionFactory, $logger, $customerSession, $checkoutSession, $jsonSerializer, $registry, $helper, $context, $data);
    }

    public function getOrderId() {
        return $this->checkoutSession->getLastOrderId();
    }

    public function getCJPurchaseData()
    {
        $orderData = [
            'CJ_PURCHASE_PRICE' => 0,
            'CJ_PURCHASE_SHIPPING' => 0,
            'CJ_PURCHASE_PRDS' => []
        ];
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($orderId);
        $allItems = $order->getAllItems();
        $realItemsData = $this->_getOrderRealItemsData($allItems);
        foreach ($realItemsData as $item) {
            $orderData['CJ_PURCHASE_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $orderData['CJ_PURCHASE_PRICE'] = intval($order->getGrandTotal());
        $orderData['CJ_PURCHASE_SHIPPING'] = intval($order->getShippingAmount()) ?? 0;
        $orderData['CJ_PURCHASE_ORDER_ID'] = $orderId;
        return $orderData;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item[] $allItems
     * @return array
     */
    protected function _getOrderRealItemsData($allItems) {
        $products = [];
        $allItemsArr = [];
        foreach ($allItems as $item) {
            $allItemsArr[] = $item->getData();
        }
        foreach ($allItems as $item) {
            $product = [];
            if ($item->getProductType() !== 'simple') {
                continue;
            }

            $product['name'] = $item->getName();
            $product['id'] = $item->getSku();
            $product['price'] =  intval($item->getPrice());
            $product['quantity'] =intval($item->getQtyOrdered());
            if ($item->getParentItemId()) {
                //common child
                $parentItem = $allItems[array_search($item->getParentItemId(), array_column($allItemsArr, 'item_id'))];
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() === null) {
                    $product['price'] =  intval($item->getPrice()); // cat rule applied
                }
                //dynamic bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() === null) {
                    $product['price'] =  intval($item->getPrice()); // cat rule applied
                }
                //fixed bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() !== null) {
                    $bundleSelectionItems = $this->getBundleSelectionsFromQuoteItem($parentItem);
                    $productId = $item->getData('product_id');
                    $bundleSelectionTotal = 0;
                    $hasChidlenPrice = true;
                    foreach ($bundleSelectionItems as $bundleSelectionItem) {
                        $bundleSelectionTotal += $bundleSelectionItem['price'] * $bundleSelectionItem['qty'];
                    }
                    if (intval($bundleSelectionTotal) === 0) {
                        $hasChidlenPrice = false;
                        $children = $parentItem->getChildren();
                        foreach ($children as $child) {
                            $bundleSelectionTotal += $child->getProduct()->getPrice() * $child->getQty();
                        }
                        $proportionRate = $item->getProduct()->getPrice() / $bundleSelectionTotal;
                    }

                    if ($hasChidlenPrice) {
                        $proportionRate = $bundleSelectionItems[$productId]['price'] / $bundleSelectionTotal;
                    }
                    //전체 가격중에 이 상품이 차지하는 가격비율. 할인전 개당 가격 /전체 가격(할인전) * qty
                    $product['price'] =  intval($proportionRate * $parentItem->getProduct()->getPrice()); //cart rule not applied
                }

                //configurable's child
                if ($parentItem->getProductType() === 'configurable') {
                    $product['price'] =  intval($parentItem->getPrice()); // cat rule applied
                }
            }
            $products[] = $product;
        }
        return $products;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $parentItem
     */
    protected function getBundleSelectionsFromOrderItem($parentItem)
    {
        $selectionItems = [];
        $bundleOptions = $parentItem->getProductOptionByCode('bundle_options');
        $bundleOptionIds = array_keys($bundleOptions);
        $selectionCollection = $this->selectionCollectionFactory->create();
        $selectionCollection->setOptionIdsFilter($bundleOptionIds);
        foreach ($selectionCollection as $selection) {
            $selectionItems[$selection->getData('product_id')] = ['price' => $selection->getData('selection_price_value'), 'qty' => $selection->getData('selection_qty')];
        }
        return $selectionItems;
    }


    /**
     * {@inheritDoc}
     */
//    public function getProductInfo($product)
//    {
//        $productDataArr = [];
//        if ($product->getTypeId() === 'bundle') {
//            $productData = $this->_getBundleProductInfo($product);
//            foreach ($productData as $productDatum) {
//                $productDataArr[] = $this->jsonSerializer->serialize($productDatum);
//            }
//        } elseif ($product->getTypeId() === 'configurable') {
//            $productData = $this->_getConfigurableProductInfo($product);
//            foreach ($productData as $productDatum) {
//                $productDataArr[] = $this->jsonSerializer->serialize($productDatum);
//            }
//        } else {
//            $productData = $this->_getSimpleProductInfo($product);
//            $productDataArr[] = $this->jsonSerializer->serialize($productData);
//        }
//        return $productDataArr;
//    }



    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getBundleProductInfo($product): array
    {
        $productInfos = [];
        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $bundleType = $product->getTypeInstance();
        $optionIds = $bundleType->getOptionsIds($product);
        $selections = $bundleType->getSelectionsCollection($optionIds, $product);
        $selectionProducts = [];
        $selectionsTotal = 0;
        foreach ($selections as $selection) {
            $selectionProduct = $this->productRepository->getById($selection->getProductId());
            $selectionProducts[$selection->getProductId()]['product'] = $selectionProduct;
            $selectionProducts[$selection->getProductId()]['qty'] = $selection->getSelectionQty();
            $selectionsTotal += $selectionProduct->getPrice() * $selection->getSelectionQty();
        }
        foreach ($selectionProducts as $productId => $productInfo) {
            $product = $productInfo['product'];
            if ($selectionsTotal != 0) {
                $productInfos[] = $this->_getSimpleProductInfo($product, $productInfo['qty'], $product->getPrice() / $selectionsTotal * $productInfo['qty']);
            }
        }
        return $productInfos;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getConfigurableProductInfo($product): array
    {
        $productInfos = [];
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $childrenIds = reset($childrenIds);
        foreach ($childrenIds as $key => $childProductId) {
            $childProduct = $this->productRepository->getById($childProductId);
            $productInfos[] = $this->_getSimpleProductInfo($childProduct);
        }
        return $productInfos;
    }

    /**
     * @param $product
     * @param $qty
     * @param $rate
     * @return array
     */
    protected function _getSimpleProductInfo($product, $qty=null, $rate=null) : array
    {
        $productInfo = [];
        $productInfo['name'] = $product->getName();
        $productInfo['id'] = $product->getSku();
        $productInfo['price'] = intval($product->getPrice());
        $productInfo['category'] = $this->getProductCategory($product);
        $productInfo['quantity'] = $qty ? intval($qty) : 0;
        if ($rate) {
            $productInfo['rate'] = $rate;
        }
        return $productInfo;
    }

    /**
     * {@inheritDoc}
     */
    public function getProductCategory($product) {
        $productCategory = "";
        $categoryIds = $product->getCategoryIds();
        $storeId = $product->getStoreId();
        $firstCategoryId = reset($categoryIds);
        if ($firstCategoryId) {
            $catInstance = $this->categoryRepository->get($firstCategoryId, $storeId);
            $productCategory = $catInstance->getName();
        }

        return $productCategory;
    }

    public function getCurrentCurrency() {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
}
