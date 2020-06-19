<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 6:26 PM
 */
namespace Eguana\VideoBoard\Controller\Details;

use Eguana\VideoBoard\Block\View;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

/**
 * Class Index
 * Controller to display detail about the video
 */
class Index extends Action
{
    /**
     * @var $videoBoard
     */
    private $videoBoard;

    /**
     * Construct
     *
     * @param Context $context
     * @param View  $videoBoard
     */
    public function __construct(
        Context $context,
        View $videoBoard
    ) {
        parent::__construct($context);
        $this->videoBoard = $videoBoard;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
