<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 8:22 PM
 */
namespace Eguana\NewsBoard\Controller\Adminhtml\Manage;

use Eguana\NewsBoard\Controller\Adminhtml\AbstractController;
use Eguana\NewsBoard\Model\NewsFactory;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * This class is used to delete the news record
 *
 * Class Delete
 */
class Delete extends AbstractController
{
    /**
     * @var NewsFactory
     */
    private $newsFactory;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     * @param PageFactory $resultPageFactory
     * @param NewsFactory $newsFactory
     * @param UrlPersistInterface $urlPersist
     * @param StoreManagerInterface $storeManager
     * @param NewsRepositoryInterface $newsRepository
     */
    public function __construct(
        Context $context,
        UrlRewriteCollectionFactory $urlRewriteCollection,
        PageFactory $resultPageFactory,
        UrlPersistInterface $urlPersist,
        NewsFactory $newsFactory,
        StoreManagerInterface $storeManager,
        NewsRepositoryInterface $newsRepository
    ) {
        $this->newsFactory = $newsFactory;
        $this->urlPersist       = $urlPersist;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->newsRepository = $newsRepository;
        $this->storeManager = $storeManager;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * execute the delete action
     * @return ResponseInterfaceAlias|RedirectAlias|ResultInterfaceAlias
     */

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('news_id');
        if ($id) {
            try {
                /** @var NewsBoard $model */
                $model = $this->newsRepository->getById($id);
                $model->delete();

                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $model->getId(),
                    UrlRewrite::ENTITY_TYPE => 'custom',
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::TARGET_PATH => 'news/index/detail/news_id/' . $model->getId()
                ]);

                $this->messageManager->addSuccessMessage(__('News was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }
        $this->messageManager->addErrorMessage(__('News could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
