<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Eguana\CustomerBulletin\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\Session;

/**
 * This controller will display black page
 *
 * Class Page
 */
class Index extends Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Data $helperData,
        LoggerInterface $logger,
        Session $customerSession,
        RedirectFactory $redirectFactory,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->helperData = $helperData;
        $this->customerSession   = $customerSession;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * To create blank page, execute method will be called
     *
     * @return ResponseInterface|ResultInterface|Page
     */

    public function execute()
    {
        if ($this->getEnableValue() == 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setUrl('/customer/account/login');
            $this->messageManager->addErrorMessage('Please Login Yourself');
            return $resultRedirect;
        }
        return $this->pageFactory->create();
    }

    /**
     * Get the value of configuration
     *
     * @return mixed
     */
    public function getEnableValue()
    {
        return $this->helperData->getGeneralConfig('configuration/enabled');
    }
}
