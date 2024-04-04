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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
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
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var \Eguana\GWLogistics\Model\QuoteCvsLocationRepository
     */
    private $quoteCvsLocationRepository;

    /**
     * Quote constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param ProductFactory $productFactory
     * @param Image $imageHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param MessageManagerInterface $messageManager
     * @param \Eguana\GWLogistics\Model\QuoteCvsLocationRepository $quoteCvsLocationRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductFactory $productFactory,
        Image $imageHelper,
        CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger,
        DateTime $dateTime,
        MessageManagerInterface $messageManager,
        \Eguana\GWLogistics\Model\QuoteCvsLocationRepository $quoteCvsLocationRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->messageManager = $messageManager;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
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
            $origReserveId = $this->checkoutSession->getQuote()->getReservedOrderId();
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $currentQuote = $this->quoteRepository->get($quoteId);
            $shippingAddress = $currentQuote->getShippingAddress();
            $billingAddress = $currentQuote->getBillingAddress();

            //in case have shipping address but missing billing address
            if ($shippingAddress->getFirstname() && $shippingAddress->getLastname() && $shippingAddress->getStreet()
                && (!$billingAddress->getFirstname() || !$billingAddress->getLastname() || !$billingAddress->getStreet())
            ) {
                $billingAddress = $this->reAssignDataToAddress($shippingAddress, $billingAddress);
                $currentQuote->setBillingAddress($billingAddress);
            }

            //in case have billing address but missing shipping address
            if ($billingAddress->getFirstname() && $billingAddress->getLastname() && $billingAddress->getStreet()
                && (!$shippingAddress->getFirstname() || !$shippingAddress->getLastname() || !$shippingAddress->getStreet())
            ) {
                $shippingAddress = $this->reAssignDataToAddress($billingAddress, $shippingAddress);
                $currentQuote->setShippingAddress($shippingAddress);
            }

            //In case missing cvs location
            if ($shippingAddress->getShippingMethod() == 'gwlogistics_CVS' && !$currentQuote->getShippingAddress()->getCvsLocationId()) {
                $cvsLocation = $this->quoteCvsLocationRepository->getByQuoteId($quoteId);
                if ($cvsLocation->getLocationId()) {
                    $shippingAddress->setCvsLocationId($cvsLocation->getLocationId());
                    $currentQuote->setShippingAddress($shippingAddress);
                }
            }

            $currentQuote->setData('reserved_order_id', $origReserveId);
            $this->quoteRepository->save($currentQuote);

            //Check new quote after save
            $newQuote = $this->quoteRepository->get($quoteId);
            $newShippingAddress = $newQuote->getShippingAddress();
            $newBillingAddress = $newQuote->getBillingAddress();

            if (!$newShippingAddress->getFirstname() || !$newShippingAddress->getLastname() || !$newShippingAddress->getStreet()[0]
                || !$newBillingAddress->getFirstname() || !$newBillingAddress->getLastname() || !$newBillingAddress->getStreet()[0]
            ) {
                throw new LocalizedException(__('The shipping address is missing. Set the address and try again.'));
            }

            //In case missing cvs location
            if ($newShippingAddress->getShippingMethod() == 'gwlogistics_CVS' && !$newQuote->getShippingAddress()->getCvsLocationId()) {
                throw new LocalizedException(__('Cannot find the CVS store location. Please try to choose CVS store again if it still error, please contact our CS Center'));
            }
            //End check new quote

            return $origReserveId;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
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
                $qty = $item->getParentItem()->getQty() * $item->getQty();
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

    /**
     * Reassign data
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param \Magento\Quote\Api\Data\AddressInterface $addressNeedToReAssign
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    private function reAssignDataToAddress($address, $addressNeedToReAssign)
    {
        $addressNeedToReAssign->setEmail($address->getEmail());
        $addressNeedToReAssign->setFirstname($address->getFirstname());
        $addressNeedToReAssign->setCompany($address->getCompany());
        $addressNeedToReAssign->setLastname($address->getLastname());
        $addressNeedToReAssign->setStreet($address->getStreet());
        $addressNeedToReAssign->setCity($address->getCity());
        $addressNeedToReAssign->setRegion($address->getRegion());
        $addressNeedToReAssign->setRegionId($address->getRegionId());
        $addressNeedToReAssign->setPostCode($address->getPostCode());
        $addressNeedToReAssign->setCountryId($address->getCountryId());
        $addressNeedToReAssign->setTelephone($address->getTelephone());
        $addressNeedToReAssign->setCustomerId($address->getCustomerId());
        return $addressNeedToReAssign;
    }
}
