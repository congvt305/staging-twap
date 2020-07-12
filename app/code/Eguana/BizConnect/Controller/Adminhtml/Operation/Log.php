<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/14/20, 3:48 PM
 *
 */

namespace Eguana\BizConnect\Controller\Adminhtml\Operation;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;
use Magento\Framework\View\Result\PageFactory;

class Log extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_BizConnect::operation_log';
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var string
     */
    private $menuId;

    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        $menuId = 'Eguana_BizConnect::operation_log'
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->menuId = $menuId;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->initLayout();
        $this->_setActiveMenu($this->menuId);
        $page->getConfig()->getTitle()->prepend('Operation Log');
        return $page;
    }

}
