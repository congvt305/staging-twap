<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 21/9/20
 * Time: 1:50 PM
 */
namespace Eguana\LinePay\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Helper\Image;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteModel;

/**
 * Class Quote
 *
 * Get current quote and item class
 */
class Quote
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Quote constructor.
     * @param CheckoutSession $checkoutSession
     * @param ProductFactory $productFactory
     * @param Image $imageHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductFactory $productFactory,
        Image $imageHelper,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger,
        DateTime $dateTime
    ) {
        $this->checkoutSession                   = $checkoutSession;
        $this->productFactory                    = $productFactory;
        $this->imageHelper                       = $imageHelper;
        $this->quoteRepository                   = $quoteRepository;
        $this->logger                            = $logger;
        $this->dateTime                          = $dateTime;
    }

    /**
     * Get quote reserve order id
     *
     * @return mixed|string|null
     * @return string
     * @throws \Exception
     */
    public function getReservedOrder()
    {
        try {
            $this->checkoutSession->getQuote()->reserveOrderId();
            $timestamp = $this->dateTime->timestamp();
            $origReserveId = $this->checkoutSession->getQuote()->getReservedOrderId();
            $reserveId = $origReserveId.$timestamp;
            $currentQuote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
            $currentQuote->setData('reserved_order_id', $origReserveId);
            $this->quoteRepository->save($currentQuote);
            return $reserveId;
        } catch (\Exception $e) {
            $this->logger->error('Linepay-Error: ' . $e->getMessage());
            throw new \Exception('Something went wrong during the checkout process. Please try again');
        }
    }

    /**
     * Get quote id
     * @return int|null
     */
    public function getQuoteId()
    {
        try {
            return $this->checkoutSession->getQuote()->getId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return null;
    }

    /**
     * Get current quote
     * @return CartInterface|QuoteModel|null
     */
    public function getQuote()
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return null;
    }

    /**
     * Get quote items package
     * @return array
     */
    public function getQuoteItemsPackage()
    {
        $products = [];
        $package = [];
        $packageAmount = null;
        $quote = null;
        try {
            $quote = $this->getQuote();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $priceInPoints = null;
        $subtotal = null;
        foreach ($quote->getAllVisibleItems() as $item) {

            if ($item->getProductType()  == 'bundle') {
                if ($item->getProduct()->getPriceType()) {
                    $prodArr = $this->getBundleProductWithDynamicPrice($item);
                    $priceInPoints = $priceInPoints + $prodArr['priceInPoints'];
                    $subtotal = $subtotal + (int)$prodArr['subtotal'];
                    unset($prodArr['subtotal']);
                    unset($prodArr['priceInPoints']);
                    $products[] = $prodArr;
                } else {
                    if ($item->getChildren()) {
                        foreach ($item->getChildren() as $childItem) {
                            $prodArr = $this->getProducts($childItem);
                            $priceInPoints = $priceInPoints + $prodArr['priceInPoints'];
                            $subtotal = $subtotal + (int)$prodArr['subtotal'];
                            unset($prodArr['subtotal']);
                            unset($prodArr['priceInPoints']);
                            $products[] = $prodArr;
                        }
                    } else {
                        $prodArr = $this->getProducts($item);
                        $priceInPoints = $priceInPoints + $prodArr['priceInPoints'];
                        $subtotal = $subtotal + (int)$prodArr['subtotal'];
                        unset($prodArr['subtotal']);
                        unset($prodArr['priceInPoints']);
                        $products[] = $prodArr;
                    }
                }
            } elseif ($item->getChildren()) {
                foreach ($item->getChildren() as $childItem) {
                    $prodArr = $this->getProducts($childItem);
                    $priceInPoints = $priceInPoints + $prodArr['priceInPoints'];
                    $subtotal = $subtotal + (int)$prodArr['subtotal'];
                    unset($prodArr['subtotal']);
                    unset($prodArr['priceInPoints']);
                    $products[] = $prodArr;
                }
            } else {
                $prodArr = $this->getProducts($item);
                $priceInPoints = $priceInPoints + $prodArr['priceInPoints'];
                $subtotal = $subtotal + (int)$prodArr['subtotal'];
                unset($prodArr['subtotal']);
                unset($prodArr['priceInPoints']);
                $products[] = $prodArr;
            }
        }
        $package['id'] = '1';
        $package['amount'] = (int)$subtotal;
        $package['products'] = $products;
        $package['priceInPoints'] = $priceInPoints;
        return $package;
    }

    /**
     * Get bundle product data with dynamic price set to NO
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    private function getBundleProductWithDynamicPrice($item)
    {
        $product = [];
        $price = null;
        $product['id'] = $item->getItemId();
        $product['name'] = $item->getName();
        $orderProduct = $this->productFactory->create()->load($item->getProductId());
        $imageSrc = $this->imageHelper->init($orderProduct, 'product_thumbnail_image')->getUrl();
        $product['imageUrl'] = $imageSrc;
        $qty = $item->getQty();
        $discount = $item->getDiscountAmount();
        $finalPrice = $item->getPrice();
        $finalPrice = $finalPrice * $qty;
        $finalPrice = $finalPrice - $discount;
        $product['quantity'] = 1;
        $product['price'] = (int)$finalPrice;
        $subtotalPrice = (int)$finalPrice;
        $product['subtotal'] = (int)$subtotalPrice;
        $pointsPrice = $finalPrice - (int)$finalPrice;
        $product['priceInPoints'] = $pointsPrice;
        return $product;
    }

    /**
     * Get products
     * @param $item
     * @return array
     */
    private function getProducts($item)
    {
        $product = [];
        $price = null;
        $product['id'] = $item->getItemId();
        $product['name'] = $item->getName();
        $orderProduct = $this->productFactory->create()->load($item->getProductId());
        $imageSrc = $this->imageHelper->init($orderProduct, 'product_thumbnail_image')->getUrl();
        $product['imageUrl'] = $imageSrc;
        if ($item->getParentItem()) {
            if ($item->getParentItem()->getProductType()  == 'bundle') {
                $qty = $item->getParentItem()->getQty();
                $discount = $item->getDiscountAmount();
                $product['quantity'] = $qty;
            } else {
                $qty = $item->getParentItem()->getQty();
                $discount = $item->getParentItem()->getDiscountAmount();
                $product['quantity'] = $qty;
            }
        } else {
            $qty = $item->getQty();
            $discount = $item->getDiscountAmount();
            $product['quantity'] = $qty;
        }
        $finalPrice = $discount/$qty;
        if ($item->getParentItem() && $item->getParentItem()->getProductType()  == 'configurable') {
            $finalPrice = $item->getParentItem()->getPrice() - $finalPrice;
        } else {
            $finalPrice = $item->getPrice() - $finalPrice;
        }
        $product['price'] = (int)$finalPrice;
        $subtotalPrice = (int)$finalPrice * $qty;
        $product['subtotal'] = (int)$subtotalPrice;
        $pointsPrice = $finalPrice - (int)$finalPrice;
        $pointsPrice = $pointsPrice * $qty;
        $product['priceInPoints'] = $pointsPrice;
        return $product;
    }
}
