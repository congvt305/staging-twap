<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/11/20
 * Time: 12:11 AM
 */

namespace Eguana\NewsBoard\Controller\Adminhtml\Manage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\Result;

/**
 * This class is used to to show counter list according to the store view
 * Class AjaxCall
 */
class AjaxCall extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AjaxCall constructor.
     *
     * @param ResultFactory $resultFactory
     * @param Context $context
     * @param LoggerInterface $logger
     * @param NewsConfiguration $newsConfiguration
     */
    public function __construct(
        ResultFactory $resultFactory,
        Context $context,
        LoggerInterface $logger,
        NewsConfiguration $newsConfiguration
    ) {
        $this->resultFactory = $resultFactory;
        $this->context = $context;
        $this->logger = $logger;
        $this->newsConfiguration = $newsConfiguration;
        parent::__construct($context);
    }
    /**
     * This method is used to get the counter store list from store locator according
     * to the store view selection
     *
     * @return ResponseInterface|ResultInterface|void
     */
    /**
     * @return ResponseInterface|ResultInterface|Result\Layout
     */
    public function execute()
    {
        $resultJson = '';
        try {
            if ($this->_request->isAjax()) {
                $storeId = $this->context->getRequest()->getParam('store_id');
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData(
                    [
                        "message" => "Category according to store view",
                        "suceess" => true,
                        "category" => $this->newsConfiguration->getCategoryValue('category', $storeId)
                    ]
                );
                return $resultJson;
            } elseif (!$this->_request->isAjax()) {
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/');
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $resultJson;
    }
}
