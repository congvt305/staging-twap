<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 2/7/20
 * Time: 1:50 PM
 */
namespace Eguana\EventManager\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\Page as PageAlias;
use Magento\Framework\View\Result\PageFactory;

/**
 * This class is used to add load the Ajax Call which shows next 9 record
 *
 * Class AjaxCall
 */
class AjaxCall extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * AjaxCall constructor.
     * @param PageFactory $resultPageFactory
     * @param ResultFactory $resultFactory
     * @param Context $context
     */
    public function __construct(
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }
    /**
     * This method is used to load layout and render information
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            /** @var PageAlias $response */
            $response = $this->resultPageFactory->create();
            $response = $response->getLayout()->getBlock('eventmanager.ajax.result')->toHtml();
            $response = $this->getResponse()->setBody($response);
        } elseif (!$this->_request->isAjax()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/events');
            return $resultRedirect;
        }
    }
}
