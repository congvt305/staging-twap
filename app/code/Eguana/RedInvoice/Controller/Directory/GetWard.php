<?php

namespace Eguana\RedInvoice\Controller\Directory;

use Magento\Framework\App\Action\Context;
use Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory as WardCollection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class GetWard extends Action
{

    /**
     * @var WardCollection
     */
    protected WardCollection $wardCollection;

    /**
     * @param Context $context
     * @param WardCollection $wardCollection
     */
    public function __construct(
        Context $context,
        WardCollection $wardCollection
    ) {
        $this->wardCollection = $wardCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $cityId = (int) $this->getRequest()->getParam('city_id');
        $collection = $this->wardCollection->create();
        $collection
            ->addFieldToSelect('ward_id')->addFieldToSelect('default_name')
            ->addFieldToFilter('city_id', $cityId)
            ->getSelect()->order('default_name asc');
        $collection->load();
        $wards = $collection->getData();

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["message" => "list of states", "suceess" => true, "wards" => $wards]);
        return $resultJson;
    }
}
