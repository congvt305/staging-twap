<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */

namespace Atome\MagentoPayment\Helper;

use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Atome\MagentoPayment\Model\Config\LocaleConfig;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class PaymentHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $paymentGatewayConfig;
    public $localeConfig;

    protected $objectManager;
    protected $storeManager;
    protected $productRepository;
    protected $commonHelper;

    protected $countryFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        PaymentGatewayConfig $paymentGatewayConfig,
        LocaleConfig $localeConfig,
        ObjectManagerInterface $objectManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        ProductRepositoryInterface $productRepositoryInterface,
        CommonHelper $commonHelper
    ) {
        parent::__construct($context);

        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->localeConfig = $localeConfig;
        $this->objectManager = $objectManagerInterface;
        $this->storeManager = $storeManagerInterface;
        $this->productRepository = $productRepositoryInterface;
        $this->commonHelper = $commonHelper;
    }

    public function build(Quote $quote)
    {
        $data = $this->buildRequestBody($quote);
        //$this->validate($data);
        return $data;
    }

    /**
    * @param array $data
    * @throws \Magento\Framework\Exception\LocalizedException
    */
    public function validate($data)
    {
        // TODO: sometimes, on the /checkout page, the address['save_in_address_book'] is empty, no real values in the address data, user have to re-fill the form
        // echo 'debug: ' . json_encode($data, JSON_PRETTY_PRINT);die;
        $billing = $data['billingAddress'];
        if (empty($billing['lines'])) {
            $errors[] = 'Address is required';
        }
        if (empty($billing['postCode']) || strlen(trim($billing['postCode'])) < 3) {
            $errors[] = 'Zip/Postal is required';
        }
        if (empty($billing['countryCode'])) {
            $errors[] = 'Country is required';
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(join($errors, '; ')));
        }
    }

    public function formatAmount($amount)
    {
        $intFactor  = $this->localeConfig->getIntFactor(100);

        $this->commonHelper->debug("[formatAmount] get int_factor form local config: " . $intFactor);

        $amount *= $intFactor;
        $amount = in_array($this->paymentGatewayConfig->getCountry(), ['id', 'vn']) ? ceil($amount) : round($amount);

        return intval($amount);
    }

    public function reverseFormatAmount($amount)
    {
        $intFactor  = $this->localeConfig->getIntFactor(100);

        $this->commonHelper->debug("[reverseFormatAmount] get int_factor form local config: " . $intFactor);

        $amount /= $intFactor;

        return in_array($this->paymentGatewayConfig->getCountry(), ['id', 'vn']) ? ceil($amount) : round($amount, 2);
    }

    protected function buildRequestBody(Quote $quote)
    {
        if ($this->paymentGatewayConfig->getCountry() === 'tw' && round($quote->getGrandTotal()) != $quote->getGrandTotal()) {
            throw new \Exception('The order total amount must be integer');
        }

        $params['callbackUrl'] = $quote->getStore()->getBaseUrl() . 'atome/payment/callback?quoteId=' . $quote->getEntityId();
        $params['paymentResultUrl'] = $quote->getStore()->getBaseUrl() . 'atome/payment/result?type=result&quoteId=' . $quote->getEntityId();
        $params['paymentCancelUrl'] = $quote->getStore()->getBaseUrl() . 'atome/payment/result?type=cancel&quoteId=' . $quote->getEntityId();

        $billingAddress = $quote->getBillingAddress();
        $shippingAddress = $quote->getShippingAddress();

        $customerNameParts = [];
        $firstName = $quote->getCustomerFirstname() ?: $billingAddress->getFirstname();
        $middleName = $quote->getCustomerMiddlename() ?: $billingAddress->getMiddlename();
        $lastName = $quote->getCustomerLastname() ?: $billingAddress->getLastname();
        if ($firstName) {
            $customerNameParts[] = $firstName;
        }
        if ($middleName) {
            $customerNameParts[] = $middleName;
        }
        if ($lastName) {
            $customerNameParts[] = $lastName;
        }
        $customerFullName = join(' ', $customerNameParts);

        // leave referenceId empty, server will generate new id every time.
        // pass merchantReferenceId to payment gateway
        $params['merchantReferenceId'] = $quote->getReservedOrderId();

        $params['customerInfo'] = [
            'fullName' => $customerFullName,
            'email' => $quote->getCustomerEmail(),
            'mobileNumber' => $shippingAddress->getTelephone() ?: $billingAddress->getTelephone(),
        ];

        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getParentItem()) {
                $product = $item->getProduct();
                $category_ids = $product->getCategoryIds();
                /** @var \Magento\Catalog\Helper\Image $imageHelper */
                $imageHelper = $this->objectManager->get('\Magento\Catalog\Helper\Image');

                $categories = [];
                if (count($category_ids) > 0) {
                    foreach ($category_ids as $category) {
                        $cat = $this->objectManager->create('Magento\Catalog\Model\Category')->load($category);
                        $categories[] = $cat->getName();
                    }
                }
                $params['items'][] = [
                    '_raw' => $item->getData(),
                    'itemId' => $item->getId(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQty(),
                    'price' => $this->formatAmount($item->getPrice()),
                    'originalPrice' => $this->formatAmount($item->getOriginalPrice()),

                    'sku' => $item->getSku(),
                    'pageUrl' => $product->getProductUrl(),
                    'imageUrl' => $imageHelper->init($product, 'product_page_image_small')->setImageFile($product->getImage())->getUrl(),
                    'categories' => $categories,
                ];
            }
        }

        /* base_grand_total is the base currency total for the order grand_total will be the grand total of the currency used to checkout.
        The store currency is GBP, base_grand_total = 10.00 in GBP
        Customer checks out in USD, grand_total is 15.00
        sub_total is pre-tax. */
        $params['shippingAddress'] = [
            '_raw'=>$shippingAddress->getData(),
            'lines' => $shippingAddress->getStreet(),
            'postCode' => $shippingAddress->getPostcode(),
            'countryCode' => $shippingAddress->getCountryId(),
        ];

        $params['billingAddress'] = [
            '_raw'=>$billingAddress->getData(),
            'lines' => $billingAddress->getStreet(),
            'postCode' => $billingAddress->getPostcode(),
            'countryCode' => $billingAddress->getCountryId(),
        ];

        $params['currency'] = $quote->getQuoteCurrencyCode();
        $params['amount'] = $this->formatAmount($quote->getGrandTotal());
        $params['taxAmount'] = $this->formatAmount($shippingAddress->getTaxAmount());
        $params['shippingAmount'] = $this->formatAmount($shippingAddress->getShippingAmount());
        $params['_raw'] = $quote->getData();
        $params['_model'] = get_class($quote);
        return $params;
    }

    public function buildFromOrder(Order $order)
    {
        if ($this->paymentGatewayConfig->getCountry() === 'tw' && round($order->getGrandTotal()) != $order->getGrandTotal()) {
            throw new \Exception('The order total amount must be integer');
        }

        $params['callbackUrl'] = $order->getStore()->getBaseUrl() . 'atome/payment/callback?orderId=' . $order->getEntityId();
        $params['paymentResultUrl'] = $order->getStore()->getBaseUrl() . 'atome/payment/result?type=result&orderId=' . $order->getEntityId();
        $params['paymentCancelUrl'] = $order->getStore()->getBaseUrl() . 'atome/payment/result?type=cancel&orderId=' . $order->getEntityId();

        $billingAddress = $order->getBillingAddress() ?: $this->objectManager->create(\Magento\Sales\Model\Order\Address::class);
        $shippingAddress = $order->getShippingAddress() ?: $this->objectManager->create(\Magento\Sales\Model\Order\Address::class);

        $customerNameParts = [];
        $firstName = $order->getCustomerFirstname() ?: $billingAddress->getFirstname();
        $middleName = $order->getCustomerMiddlename() ?: $billingAddress->getMiddlename();
        $lastName = $order->getCustomerLastname() ?: $billingAddress->getLastname();
        if ($firstName) {
            $customerNameParts[] = $firstName;
        }
        if ($middleName) {
            $customerNameParts[] = $middleName;
        }
        if ($lastName) {
            $customerNameParts[] = $lastName;
        }
        $customerFullName = join(' ', $customerNameParts);

        // leave referenceId empty, server will generate new id every time.
        // pass merchantReferenceId to payment gateway
        $params['merchantReferenceId'] = $order->getIncrementId();

        $params['customerInfo'] = [
            'fullName' => $customerFullName,
            'email' => $order->getCustomerEmail(),
            'mobileNumber' => $shippingAddress->getTelephone() ?: $billingAddress->getTelephone(),
        ];

        foreach ($order->getAllVisibleItems() as $item) {
            if (!$item->getParentItem()) {
                $product = $item->getProduct();
                $category_ids = $product->getCategoryIds();
                /** @var \Magento\Catalog\Helper\Image $imageHelper */
                $imageHelper = $this->objectManager->get('\Magento\Catalog\Helper\Image');

                $categories = [];
                if (count($category_ids) > 0) {
                    foreach ($category_ids as $category) {
                        $cat = $this->objectManager->create('Magento\Catalog\Model\Category')->load($category);
                        $categories[] = $cat->getName();
                    }
                }
                $params['items'][] = [
                    '_raw' => $item->getData(),
                    'itemId' => $item->getId(),
                    'name' => $item->getName(),
                    'quantity' => $item->getQty(),
                    'price' => $this->formatAmount($item->getPrice()),
                    'originalPrice' => $this->formatAmount($item->getOriginalPrice()),

                    'sku' => $item->getSku(),
                    'pageUrl' => $product->getProductUrl(),
                    'imageUrl' => $imageHelper->init($product, 'product_page_image_small')->setImageFile($product->getImage())->getUrl(),
                    'categories' => $categories,
                ];
            }
        }

        /* base_grand_total is the base currency total for the order grand_total will be the grand total of the currency used to checkout.
        The store currency is GBP, base_grand_total = 10.00 in GBP
        Customer checks out in USD, grand_total is 15.00
        sub_total is pre-tax. */
        $params['shippingAddress'] = [
            '_raw' => $shippingAddress->getData(),
            'lines' => $shippingAddress->getStreet(),
            'postCode' => $shippingAddress->getPostcode(),
            'countryCode' => $shippingAddress->getCountryId(),
        ];

        $params['billingAddress'] = [
            '_raw' => $billingAddress->getData(),
            'lines' => $billingAddress->getStreet(),
            'postCode' => $billingAddress->getPostcode(),
            'countryCode' => $billingAddress->getCountryId(),
        ];

        $params['currency'] = $order->getOrderCurrencyCode();
        $params['amount'] = $this->formatAmount($order->getGrandTotal());
        $params['taxAmount'] = $this->formatAmount($shippingAddress->getTaxAmount());
        $params['shippingAmount'] = $this->formatAmount($shippingAddress->getShippingAmount());
        $params['_raw'] = $order->getData();
        $params['_model'] = get_class($order);
        return $params;
    }
}
