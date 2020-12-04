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

namespace Eguana\NewsBoard\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * This controller will display black page
 *
 * Class Page
 */
class Index extends Action
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param NewsConfiguration $newsConfiguration
     * @param LoggerInterface $logger
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        NewsConfiguration $newsConfiguration,
        LoggerInterface $logger,
        RedirectFactory $redirectFactory,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->newsConfiguration = $newsConfiguration;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * To create blank page, execute method will be called
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */

    public function execute()
    {
        if ($this->getEnableValue() == 0) {
            return $this->redirectFactory->create()->setPath('/');
        }
        return $this->pageFactory->create();
    }

    /**
     * Get Enable value of module from configuration
     *
     * @return mixed
     */
    public function getEnableValue()
    {
        $config = $this->newsConfiguration->getConfigValue('enabled');;
        return $this->newsConfiguration->getConfigValue('enabled');
    }
}
