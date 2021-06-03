<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 11/5/21
 * Time: 3:13 PM
 */
namespace Eguana\Redemption\Controller\Details;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

/**
 * To show Complete page after redemption completed (redeem)
 *
 * Class Complete
 */
class Complete extends Action
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * Used to load layout and render information
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $redemptionId = $this->_request->getParam('redemption_id');
        if ($redemptionId) {
            return $this->pageFactory->create();
        } else {
            $redirect = $this->resultRedirectFactory->create();
            return $redirect->setUrl('/');
        }
    }
}
