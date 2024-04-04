<?php

namespace CJ\TotalInquiry\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 * @package CJ\TotalInquiry\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var Session
     */
    protected Session $session;
    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;
    /**
     * @var ProductRepository
     */
    protected ProductRepository $productRepository;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param Session $session
     * @param OrderFactory $orderFactory
     * @param ProductRepository $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Session $session,
        OrderFactory $orderFactory,
        ProductRepository $productRepository,
        LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->orderFactory = $orderFactory;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getCustomerId() {
        return $this->session->getCustomer()->getId();
    }

    /**
     * @return array
     */
    public function getPurchaseProduct() {
        $orders = $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $this->getCustomerId());
        $products = [];
        foreach ($orders as $order) {
            foreach ($order->getAllVisibleItems() as $item) {
                try {
                    $product = $this->productRepository->getById($item->getProductId());
                    $products[$product->getId()] = $product->getSku();
                } catch (\Exception $exception) {
                    $this->logger->error($exception->getMessage());
                }
            }
        }

        return $products;
    }
}
