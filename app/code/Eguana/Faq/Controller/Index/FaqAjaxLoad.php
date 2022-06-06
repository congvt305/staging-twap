<?php
declare(strict_types=1);

namespace Eguana\Faq\Controller\Index;

use Eguana\Faq\Helper\Data;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class FaqAjaxLoad extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var Data
     */
    private $faqHelper;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param Data $faqHelper
     * @param \Magento\Framework\Registry $registry
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        Data $faqHelper,
        \Magento\Framework\Registry $registry,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->logger = $logger;
        $this->resultFactory = $resultFactory;
        $this->faqHelper = $faqHelper;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * Add html ajax for faq in PLP, PDP
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        try {
            if ($this->faqHelper->isPdpPage()) {
                if (!$this->registry->registry('current_product')) {
                    $this->registry->register('current_product', $this->productRepository->getById($params['product_id']));
                }
            } else {
                if (!$this->registry->registry('current_category')) {
                    $this->registry->register('current_category', $this->categoryRepository->get($params['category_id']));
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error when call faq ajax: ' . $e);
        }

        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
