<?php

namespace CJ\PointRedemption\Controller\Ajax;

use CJ\PointRedemption\Helper\Data;
use Magento\Catalog\Pricing\Render;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class FinalPrice extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * View constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        $blockHtml = '';
        $postData = $this->getRequest()->getPostValue();
        $productId = $postData['product_id'] ?? '';
        $isMembershipCategory = $postData['is_membership_category'] ?? false;
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            $this->registry->register('product', $product);
            $this->registry->register(Data::REGISTRY_KEY_IS_MEMBERSHIP_CATEGORY, $isMembershipCategory);
            $arguments = [
                'price_render' => 'product.price.render.default',
                'price_type_code' => 'final_price',
                'zone' => 'item_view'
            ];
            $blockHtml = $resultPage->getLayout()
                ->createBlock(Render::class, 'product.price.final', ['data' => $arguments])
                ->toHtml();
        }

        $result->setData(['output' => $blockHtml]);
        return $result;
    }
}
