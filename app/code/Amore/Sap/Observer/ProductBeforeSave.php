<?php

namespace Amore\Sap\Observer;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use CJ\Catalog\Helper\Data as Helper;

class ProductBeforeSave implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ProductRepository $productRepository,
        Helper $helper
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            $price = $product->getPrice();
            if ($product->getTypeId() == 'simple') {
                if ($price == null) {
                    $product->setIsFreeGift(null); // remove store view config
                } elseif ($price == 0) {
                    $product->setIsFreeGift(1);
                } else {
                    $product->setIsFreeGift(0);
                }
            }

            //remove special characters in product name when saving it
            if ($this->helper->isEnabledRemoveSpecialCharacter()) {
                $productNameWithoutSpecial = $product->getName();
                $listSpecialCharacters = $this->helper->getSpecialCharacterList();
                foreach ($listSpecialCharacters as $specialCharacter) {
                    $productNameWithoutSpecial = str_replace($specialCharacter, '', $productNameWithoutSpecial);
                }
                $product->setName(trim($productNameWithoutSpecial));
            }
        } catch (\Exception $exception) {
            $this->logger->error('Cannot update product free gift: ' . $exception->getMessage());
        }
    }

}
