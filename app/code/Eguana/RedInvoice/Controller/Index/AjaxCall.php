<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 28/5/21
 * Time: 7:47 PM
 */
namespace Eguana\RedInvoice\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultFactory;
use Eguana\Directory\Model\ResourceModel\City\CollectionFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;

/**
 * This class is used to get the city list with ajax call
 * Class AjaxCall
 */
class AjaxCall extends Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var CollectionFactory
     */
    private $cityCollectionFactory;

    /**
     * AjaxCall constructor.
     * @param Context $context
     * @param CollectionFactory $cityCollectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $cityCollectionFactory
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    /**
     * This method is used to get the cities list for the given state id
     * @return ResponseInterfaceAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        $whoteData = $this->context->getRequest()->getParams();
        $regionId = (int)$whoteData['selectedValue'];
        $citiesCollection = $this->cityCollectionFactory->create();
        $citiesCollection->addFieldToFilter(
            "main_table.region_id",
            ["eq" => $regionId]
        );
        $cities = $citiesCollection->getData();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["message" => "list of states", "suceess" => true, "cities" => $cities]);
        return $resultJson;
    }
}
