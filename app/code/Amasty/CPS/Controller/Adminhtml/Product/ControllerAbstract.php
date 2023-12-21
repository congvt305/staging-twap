<?php

namespace Amasty\CPS\Controller\Adminhtml\Product;

use Amasty\CPS\Model\BrandProduct;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class ControllerAbstract extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Amasty\CPS\Model\Product\AdminhtmlDataProvider
     */
    protected $dataProvider;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var BrandProduct
     */
    protected $brandProduct;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Amasty\CPS\Model\Product\AdminhtmlDataProvider $adminhtmlDataProvider,
        \Amasty\CPS\Model\BrandProduct $brandProduct,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->dataProvider = $adminhtmlDataProvider;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->brandProduct = $brandProduct;
    }
}
