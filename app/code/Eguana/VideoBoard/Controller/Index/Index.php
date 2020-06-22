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
use Magento\Framework\Controller\Result\JsonFactory;
use Eguana\VideoBoard\Model\Pagination\VideoList;

/**
 * This class is used to add load the layout and render data
 *
 * Class Index
 * Eguana\VideoBoard\Controller\Index
 */
class Index extends Action
{
    /**
     * @var VideoList
     */
    private $videoList;

    /**
     * Index constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param VideoList $videoList
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        VideoList $videoList
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->videoList = $videoList;
    }
    /**
     * This method is used to load layout and render information
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $paginationStatus = $this->_request->getParam('count');
        if (isset($paginationStatus)) {
            $data = [
                'page'  => $paginationStatus,
                'listHtml'   => $this->videoList->getListofVideos($paginationStatus)
            ];
            $result = $this->resultJsonFactory->create();
            $result->setData($data);
            return $result;
        } else {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        }
    }
}
