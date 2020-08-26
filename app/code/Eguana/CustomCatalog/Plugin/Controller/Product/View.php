<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 25/8/20
 * Time: 12:06 PM
 */
namespace Eguana\CustomCatalog\Plugin\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Product\View as ViewProduct;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is used to add after plugin to remove error message
 * from bundle product when add to cart
 *
 * Class View
 */
class View
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * View constructor.
     * @param Http $request
     * @param ManagerInterface $messageManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Http $request,
        ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * afterExecute method
     * This after plugin is used to remove error message when adding a bundle product in cart
     * @param ViewProduct $subject
     * @param $result
     * @return mixed
     */
    public function afterExecute(ViewProduct $subject, $result)
    {
        $productId = (int) $this->request->getParam('id');
        $specifyOptions = $this->request->getParam('options');

        try {
            $productType = $this->productRepository->getById($productId)->getTypeId();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        if ($productType == 'bundle') {
            if ($specifyOptions) {
                foreach ($this->messageManager->getMessages()->getItems() as $type => $messages) {
                    if ($messages->getType() == 'notice') {
                        $messages->setIdentifier('bundle_product_error_message');
                        $this->messageManager->getMessages()->deleteMessageByIdentifier('bundle_product_error_message');
                    }
                }
            }
        }
        return $result;
    }
}
