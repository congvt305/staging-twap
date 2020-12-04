<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 19/10/20
 * Time: 11:00 AM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

/**
 * This class is used for Mass delete to delete multiple rows at time
 *
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * MassDelete constructor.
     *
     * @param LoggerInterface $logger
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        UrlRewriteCollectionFactory $urlRewriteCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $result = "";
        try {
            $collectionFilter = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collectionFilter->getSize();
            /**
             * @var Redemption $redemption
             */
            foreach ($collectionFilter as $redemption) {
                $urlcollection = $this->urlRewriteCollection->create();
                $collection = $urlcollection
                    ->addFieldToFilter('target_path', [
                        'eq' => 'redemption/details/index/redemption_id/' .$redemption->getId()
                    ])
                    ->addFieldToFilter('request_path', ['eq' => $redemption->getIdentifier()]);
                if ($redemption->getStoreId(0) == '0') {
                    $storeManagerDataList = $this->storeManager->getStores();
                    $data['store_id'] = [];
                    foreach ($storeManagerDataList as $key => $value) {
                        $data['store_id'][] = $key;
                    }
                    $collection = $urlcollection->addStoreFilter($data);
                } else {
                    $collection = $urlcollection->addStoreFilter($redemption->getStoreId());
                }
                foreach ($collection->getItems() as $item) {
                    $item->delete();
                }
                $redemption->delete();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 redemption(s) have been deleted.', $collectionSize)
            );
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result = $resultRedirect->setPath('*/*/');
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
