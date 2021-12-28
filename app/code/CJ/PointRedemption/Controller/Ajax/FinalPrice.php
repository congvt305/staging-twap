<?php

namespace CJ\PointRedemption\Controller\Ajax;

use CJ\PointRedemption\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Data
     */
    protected $pointRedemptionHelper;

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
        ProductRepositoryInterface $productRepository,
        Data $pointRedemptionHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->pointRedemptionHelper = $pointRedemptionHelper;
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
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            $isAjaxRequestFromPointRedemptionPDP = (int)$this->pointRedemptionHelper->isAjaxRequestFromPointRedemptionPDP();
            $this->registry->register('product', $product);
            $arguments = [
                'price_render' => 'product.price.render.default',
                'price_type_code' => 'final_price',
                'zone' => 'item_view'
            ];
            $blockHtml = $resultPage->getLayout()
                ->createBlock(Render::class, 'product.price.final', ['data' => $arguments])
                ->toHtml();
            $isPointRedemptionHtml = '<input type="hidden" name="is_point_redemption" value="' . $isAjaxRequestFromPointRedemptionPDP . '">';
            $blockHtml = $blockHtml . $isPointRedemptionHtml;
        }

        $result->setData(['output' => $blockHtml]);
        return $result;
    }
}
