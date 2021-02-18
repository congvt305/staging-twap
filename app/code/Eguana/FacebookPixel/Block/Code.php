<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 4:14 AM
 */
namespace Eguana\FacebookPixel\Block;

use Eguana\FacebookPixel\Helper\Data;
use Eguana\FacebookPixel\Model\SessionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Helper\Data as CoreData;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\PriceCurrency;

/**
 * Block class code to provide data
 *
 * Class Code
 */
class Code extends Template
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CoreData
     */
    private $catalogHelper;

    /**
     * @var PriceCurrency
     */
    private $price;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SessionFactory
     */
    private $fbPixelSession;

    /**
     * @param Context $context
     * @param Data $helper
     * @param Registry $coreRegistry
     * @param CoreData $catalogHelper
     * @param PriceCurrency $price
     * @param CheckoutSession $checkoutSession
     * @param SessionFactory $fbPixelSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        Registry $coreRegistry,
        CoreData $catalogHelper,
        PriceCurrency $price,
        CheckoutSession $checkoutSession,
        SessionFactory $fbPixelSession,
        array $data = []
    ) {
        $this->price            = $price;
        $this->helper           = $helper;
        $this->storeManager     = $context->getStoreManager();
        $this->coreRegistry     = $coreRegistry;
        $this->catalogHelper    = $catalogHelper;
        $this->fbPixelSession   = $fbPixelSession;
        $this->checkoutSession  = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * Is module enabled
     *
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->helper->isModuleEnabled();
    }

    /**
     * Get Facebook Pixel data
     *
     * @return array
     */
    public function getFacebookPixelData()
    {
        $data = [];
        try {
            $data['id'] = $this->helper->getPixelId();
            $data['full_action_name'] = $this->getRequest()->getFullActionName();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $data;
    }

    /**
     * Get product data
     *
     * @return false|int|string
     */
    public function getProduct()
    {
        $productData = 404;
        try {
            $data = $this->getFacebookPixelData();
            $action = $data['full_action_name'];
            if ($action == 'catalog_product_view' && $this->helper->isProductView()) {
                if ($this->getProductData() !== null) {
                    $productData = $this->helper->serializes($this->getProductData());
                }
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $productData;
    }

    /**
     * Get Order
     *
     * @return array|int
     */
    public function getOrder()
    {
        $orderData = 404;
        $data   = $this->getFacebookPixelData();
        $action = $data['full_action_name'];
        if ($action == 'checkout_onepage_success'
            || $action == 'onepagecheckout_index_success'
            || $action == 'multishipping_checkout_success') {
            $orderData = $this->getOrderData();
        }
        return $orderData;
    }

    /**
     * Returns data needed for purchase tracking.
     *
     * @return int|string
     */
    private function getOrderData()
    {
        $order = $this->checkoutSession->create()->getLastRealOrder();
        $orderId = $order->getIncrementId();

        if ($orderId && $this->helper->isPurchase()) {
            $customerEmail = $order->getCustomerEmail();
            if ($order->getShippingAddress()) {
                $addressData = $order->getShippingAddress();
            } else {
                $addressData = $order->getBillingAddress();
            }

            if ($addressData) {
                $customerData = $addressData->getData();
            } else {
                $customerData = null;
            }
            $product = [
                'content_ids' => [],
                'contents' => [],
                'value' => "",
                'currency' => "",
                'num_items' => 0,
                'email' => "",
                'address' => []
            ];

            $num_item = 0;
            foreach ($order->getAllVisibleItems() as $item) {
                $product['contents'][] = [
                    'id' => $item->getSku(),
                    'name' => $item->getName(),
                    'quantity' => (int)$item->getQtyOrdered(),
                    'item_price' => $item->getPrice()
                ];
                $product['content_ids'][] = $item->getSku();
                $num_item += round($item->getQtyOrdered());
            }
            $data = [
                'content_ids' => $product['content_ids'],
                'contents' => $product['contents'],
                'content_type' => 'product',
                'value' => number_format(
                    $order->getGrandTotal(),
                    2,
                    '.',
                    ''
                ),
                'num_items' => $num_item,
                'currency' => $order->getOrderCurrencyCode(),
                'email' => $customerEmail,
                'phone' => $this->getValueByKey($customerData, 'telephone'),
                'firtname' => $this->getValueByKey($customerData, 'firstname'),
                'lastname' => $this->getValueByKey($customerData, 'lastname'),
                'city' => $this->getValueByKey($customerData, 'city'),
                'country' => $this->getValueByKey($customerData, 'country_id'),
                'st' => $this->getValueByKey($customerData, 'region'),
                'zipcode' => $this->getValueByKey($customerData, 'postcode')
            ];
            return $this->helper->serializes($data);
        } else {
            return 404;
        }
    }

    /**
     * Get value by key
     *
     * @param $array
     * @param $key
     * @return string
     */
    private function getValueByKey($array, $key)
    {
        if (!empty($array) && isset($array[$key])) {
            return $array[$key];
        }
        return '';
    }

    /**
     * Get Product Data
     *
     * @return array
     */
    private function getProductData()
    {
        $data = [];
        if (!$this->helper->isProductView()) {
            return $data;
        }

        try {
            $currentProduct = $this->coreRegistry->registry('current_product');
            $data['content_name'] = $this->helper
                ->escapeSingleQuotes($currentProduct->getName());
            $data['content_ids'] = $this->helper
                ->escapeSingleQuotes($currentProduct->getSku());
            $data['content_type'] = 'product';
            $data['value'] = $this->formatPrice(
                $this->helper->getProductPrice($currentProduct)
            );
            $data['currency'] = $this->helper->getCurrentCurrencyCode();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $data;
    }

    /**
     * Returns formated price
     *
     * @param string $price
     * @param string $currencyCode
     * @return float|string
     */
    private function formatPrice($price, $currencyCode = '')
    {
        $formatedPrice = $this->price->round($price);

        if ($currencyCode) {
            return $formatedPrice . ' ' . $currencyCode;
        } else {
            return $formatedPrice;
        }
    }
}
