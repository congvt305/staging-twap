<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Controller\Inventory;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\ProductRepositoryInterface;
use CJ\CatalogProduct\Model\GetProductQtyLeft;

/**
 * Class ValidateQty
 */
class ValidateQty extends Action implements HttpGetActionInterface
{
    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var GetProductQtyLeft
     */
    private GetProductQtyLeft $getProductQtyLeft;

    /**
     * @param Context $context
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     * @param GetProductQtyLeft $getProductQtyLeft
     */
    public function __construct(
        Context $context,
        Configurable $configurable,
        ProductRepositoryInterface $productRepository,
        GetProductQtyLeft $getProductQtyLeft
    ) {
        parent::__construct($context);
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->getProductQtyLeft = $getProductQtyLeft;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultFactory = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if (!$this->getRequest()->isAjax()) {
            return $resultFactory->setData([
                'is_in_stock' => false
            ]);
        }

        $requestQty = (float)$this->getRequest()->getParam('qty', 1);
        $bundleOptions = $this->getRequest()->getParam('bundle_option', []);
        $product = $this->getProductToValidate();
        if (!$product || $product->getTypeId() == Configurable::TYPE_CODE) {
            return $resultFactory->setData([
                'is_in_stock' => false
            ]);
        }


        $result = $this->getProductQtyLeft->execute($product, $requestQty, $bundleOptions);

        return $resultFactory->setData($result);
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product|null
     */
    protected function getProductToValidate()
    {
        $productId = $this->getRequest()->getParam('product');
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return null;
        }

        $superAttributes = $this->getRequest()->getParam('super_attribute', []);
        if (!empty($superAttributes)) {
            $product = $this->configurable->getProductByAttributes($superAttributes, $product);
        }

        return $product;
    }
}
