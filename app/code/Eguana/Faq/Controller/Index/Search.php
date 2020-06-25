<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistor;

/**
 * class Search
 * get params from phtml file
 */
class Search extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var DataPersistor
     */
    private $dataPersistor;

    /**
     * Search constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataPersistor $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistor $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * get search word
     *
     * set search word using dataPersistor class
     */
    public function execute()
    {
        $searchValue = $this->getRequest()->getParam('faqSearchVal');

        if (!$searchValue) {
            $this->_redirect('faq/index/index');
        }

        $this->dataPersistor->set('searchValue', trim($searchValue));

        return $this->resultPageFactory->create();
    }
}
