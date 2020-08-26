<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/8/20
 * Time: 3:24 PM
 */
namespace Eguana\CustomCatalog\Plugin\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\Index\Cart as CartAlias;
use Magento\Wishlist\Model\ItemFactory;
use Psr\Log\LoggerInterface;

/**
 * This class is consist of method which is used to redirect to PDP when click on
 * add to cart button on bundle product
 *
 * Class Cart
 */
class Cart
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Cart constructor.
     * @param ResultFactory $resultFactory
     * @param ItemFactory $itemFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultFactory $resultFactory,
        ItemFactory $itemFactory,
        LoggerInterface $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->itemFactory = $itemFactory;
        $this->logger = $logger;
    }

    /**
     * aroundExecute method
     * This method is used to check if the product is bundle then redirect it to the PDP
     * @param CartAlias $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(CartAlias $subject, callable $proceed)
    {
        $itemId = (int)$subject->getRequest()->getParam('item');
        try {
            $item = $this->itemFactory->create()->load($itemId);
            $productType = $item->getProduct()->getTypeId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        if ($productType == 'bundle') {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($item->getProductUrl());
        } else {
            return $result = $proceed();
        }
    }
}
