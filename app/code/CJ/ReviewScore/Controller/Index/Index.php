<?php

namespace CJ\ReviewScore\Controller\Index;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;

class Index extends Action implements HttpGetActionInterface
{

    /**
     * @var JsonFactory
     */
    private $_resultJsonFactory;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var SummaryCollectionFactory
     */
    private $_summaryCollectionFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param StoreManagerInterface $storeManager
     * @param SummaryCollectionFactory $summaryCollectionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        StoreManagerInterface $storeManager,
        SummaryCollectionFactory $summaryCollectionFactory
    )
    {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_storeManager = $storeManager;
        $this->_summaryCollectionFactory = $summaryCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return Json
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $entityIds = $this->getRequest()->getParam('entity_id');
        $storeId = $this->_storeManager->getStore()->getId();
        $data['data'] = [];
        if (!empty($entityIds)) {
            try {
                $productEntityArray = explode(',', $entityIds);
                $summaryCollection = $this->_summaryCollectionFactory->create();
                $summaryCollection->addEntityFilter($productEntityArray)->addStoreFilter($storeId);
                $existDataEntity = [];

                //handle exist data entity
                foreach ($summaryCollection->getData() as $item) {
                    $itemData['entity_id'] = $item['entity_pk_value'];
                    $itemData['rating'] = $item['rating_summary'] / 20;
                    $itemData['review_cnt'] = (int)$item['reviews_count'];
                    $data['data'][] = $itemData;
                    $existDataEntity[] = $item['entity_pk_value'];
                }

                //handle no data entity
                $noDataEntity = array_diff($productEntityArray, $existDataEntity);
                foreach ($noDataEntity as $entity) {
                    $itemData['entity_id'] = $entity;
                    $itemData['rating'] = 0;
                    $itemData['review_cnt'] = 0;
                    $data['data'][] = $itemData;
                }
            } catch (Exception $e) {
                $data['error'] = $e->getMessage();
            }
        }
        $result = $this->_resultJsonFactory->create();
        return $result->setData($data);
    }
}
