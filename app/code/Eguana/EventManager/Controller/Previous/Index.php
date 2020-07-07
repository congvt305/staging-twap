<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 1/7/20
 * Time: 7:40 PM
 */
namespace Eguana\EventManager\Controller\Previous;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * This class is used to create page for previous events
 * Class Index
 */
class Index extends Action
{
    /**
     * Index constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * This method is used to create page to show list of previous events
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
