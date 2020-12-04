<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 20/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Controller\Adminhtml\AbstractController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

/**
 * This class is used to delete the Redemption record
 *
 * Class Delete
 */
class Delete extends AbstractController
{
    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     * @param RedemptionRepositoryInterface|null $redemptionRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        UrlRewriteCollectionFactory $urlRewriteCollection,
        RedemptionRepositoryInterface $redemptionRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->redemptionRepository = $redemptionRepository;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $resultPageFactory
        );
    }

    /**
     * execute the delete action
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('redemption_id');

        if ($id) {
            try {
                /** @var Redemption $model */
                $model = $this->redemptionRepository->getById($id);
                $model->delete();
                $urlcollection = $this->urlRewriteCollection->create();
                $collection = $urlcollection
                    ->addFieldToFilter('target_path', [
                        'eq' => 'redemption/details/index/redemption_id/' .$model->getId()
                    ])
                    ->addFieldToFilter('request_path', ['eq' => $model->getIdentifier()]);
                if ($model->getStoreId(0) == '0') {
                    $storeManagerDataList = $this->storeManager->getStores();
                    $data['store_id'] = [];
                    foreach ($storeManagerDataList as $key => $value) {
                        $data['store_id'][] = $key;
                    }
                    $collection = $urlcollection->addStoreFilter($data);
                } else {
                    $collection = $urlcollection->addStoreFilter($model->getStoreId());
                }
                foreach ($collection->getItems() as $item) {
                    $item->delete();
                }
                $this->messageManager->addSuccessMessage(__('Redemption was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }
        $this->messageManager->addErrorMessage(__('Redemption could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
