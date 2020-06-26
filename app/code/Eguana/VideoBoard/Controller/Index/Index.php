<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:32 PM
 */
namespace Eguana\VideoBoard\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * This class is used to add load the layout and render data
 *
 * Class Index
 * Eguana\VideoBoard\Controller\Index
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
     * This method is used to load layout and render information
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
