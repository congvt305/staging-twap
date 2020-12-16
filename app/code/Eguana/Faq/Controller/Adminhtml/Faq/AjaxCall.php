<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/12/20
 * Time: 9:11 PM
 */
namespace Eguana\Faq\Controller\Adminhtml\Faq;

use Eguana\Faq\Model\FaqConfiguration\FaqConfiguration;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * To show categories according to the store view
 *
 * Class AjaxCall
 */
class AjaxCall extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var FaqConfiguration
     */
    private $faqConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param FaqConfiguration $faqConfiguration
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        FaqConfiguration $faqConfiguration
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->resultFactory = $resultFactory;
        $this->faqConfiguration = $faqConfiguration;
        parent::__construct($context);
    }
    /**
     * To get categories according to the store view
     *
     * @return ResponseInterface|ResultInterface|string
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            if ($this->_request->isAjax()) {
                $storeId = $this->context->getRequest()->getParam('store_id');
                $resultJson->setData(
                    [
                        "suceess" => true,
                        "category" => $this->faqConfiguration->getCategoryValue($storeId)
                    ]
                );
            } else {
                $resultJson->setData(["suceess" => false]);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $resultJson->setData(["suceess" => false]);
        }
        return $resultJson;
    }
}
