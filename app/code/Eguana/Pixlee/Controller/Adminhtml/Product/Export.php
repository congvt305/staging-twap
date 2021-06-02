<?php
/**
 * Copyright © 2015 Pixlee
 * @author teemingchew
 */

namespace Eguana\Pixlee\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\HTTP\Client\Curl;

class Export extends \Pixlee\Pixlee\Controller\Adminhtml\Product\Export
{
    protected $resultJsonFactory;
    protected $_pixleeData;
    protected $_logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Request\Http $request,
        \Pixlee\Pixlee\Helper\Data $pixleeData,
        \Pixlee\Pixlee\Helper\Logger\PixleeLogger $logger
    ) {
        parent::__construct($context, $resultJsonFactory, $request, $pixleeData, $logger);
        //parent::__construct($context);
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->request            = $request;
        $this->_pixleeData        = $pixleeData;
        $this->_logger            = $logger;
        $this->_curl              = new Curl;
    }



    public function execute()
    {
        $websiteId = $this->request->getParam('website_id');
        $this->_pixleeData->exportProducts($websiteId);
    }
}
