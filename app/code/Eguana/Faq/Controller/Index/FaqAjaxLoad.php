<?php
declare(strict_types=1);

namespace Eguana\Faq\Controller\Index;

use Eguana\Faq\Helper\Data;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class FaqAjaxLoad extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
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
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Data $faqHelper
     * @param \Magento\Framework\Registry $registry
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Data $faqHelper,
        \Magento\Framework\Registry $registry,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        JsonFactory $resultJsonFactory
    ) {
        $this->logger = $logger;
        $this->faqHelper = $faqHelper;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->resultJsonFactory = $resultJsonFactory;
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
        $resultJson = $this->resultJsonFactory->create();
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
            /** @var \Magento\Framework\View\Layout $layout */
            $layout = $this->_view->getLayout();
            $block = $layout
                ->createBlock('\Eguana\Faq\Block\Faq')
                ->setTemplate('Eguana_Faq::catalog/faq_ajax_load.phtml')
                ->setData('faq_list', $this->faqHelper->getFaqData());

            $response = [
                'status' => 'success',
                'content' => $block->toHtml(),
            ];
        } catch (\Exception $e) {
            /** @var array $response */
            $response = [
                'status' => 'error',
                'message' => __('An error occurred')
            ];
            $this->logger->critical('Error when call faq ajax: ' . $e);
        }

        return $resultJson->setData($response);
    }
}
