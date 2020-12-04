<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:22 PM
 */
namespace Eguana\NewsBoard\Controller\Adminhtml\Manage;

use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Eguana\NewsBoard\Controller\Adminhtml\AbstractController;
use Eguana\NewsBoard\Model\News;
use Eguana\NewsBoard\Model\NewsFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as RedirectAlias;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as ResourceUrlRewriteFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Psr\Log\LoggerInterface;

/**
 * Action for save button
 *
 * Class Save
 */
class Save extends AbstractController implements HttpPostActionInterface
{

    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var NewsFactory
     */
    private $newsFactory;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var ResourceUrlRewriteFactory
     */
    private $resourceUrlRewriteFactory;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var News
     */
    private $newsModel;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * Save constructor.
     * @param Context $context
     * @param News $newsModel
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistor
     * @param NewsFactory $newsFactory
     * @param NewsRepositoryInterface $newsRepository
     * @param TimezoneInterface $timezone
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param LoggerInterface $logger
     * @param ResourceUrlRewriteFactory $resourceUrlRewriteFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        News $newsModel,
        UrlRewriteCollectionFactory $urlRewriteCollection,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        NewsFactory $newsFactory,
        NewsRepositoryInterface $newsRepository,
        TimezoneInterface $timezone,
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        LoggerInterface $logger,
        ResourceUrlRewriteFactory $resourceUrlRewriteFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->newsModel = $newsModel;
        $this->newsFactory = $newsFactory;
        $this->newsRepository = $newsRepository;
        $this->timezone = $timezone;
        $this->resourceUrlRewriteFactory = $resourceUrlRewriteFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->logger = $logger;
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Save news action
     *
     * @return RedirectAlias|ResponseInterface|ResultInterfaceAlias|mixed
     */
    public function execute()
    {
        $model = '';
        /** @var RedirectAlias $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $generalData = $data;
            if (isset($generalData['is_active']) && $generalData['is_active'] === '1') {
                $generalData['is_active'] = 1;
            }
            if (empty($generalData['news_id'])) {
                $generalData['news_id'] = null;
            }
            $id = $generalData['news_id'];
            $model = $this->newsFactory->create();
            $generalData['date'] = $this->changeDateFormat($generalData['date']);

            if ($id) {
                try {
                    $model = $this->newsRepository->getById($id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('This news no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }

                if (isset($model) && $generalData['identifier']) {
                    $urlKeyCheck = $generalData['identifier'];
                    $storeDiff = array_diff($generalData['store_id'], $model['store_id']);
                    $urlcollection = $this->urlRewriteCollection->create();
                    $urlcollection->addFieldToFilter('request_path', ['eq' => $urlKeyCheck]);
                    if ($model->getIdentifier() != $generalData['identifier']) {
                        $collection = $urlcollection->addStoreFilter($generalData['store_id']);
                    } else {
                        $collection = $urlcollection->addStoreFilter($storeDiff);
                    }
                    if (count($collection) > 0) {
                        $this->messageManager->addErrorMessage(
                            __('The value specified in the URL Key is already exists.
                        Please use a unique identifier key')
                        );
                        return $resultRedirect->setPath('*/*/edit', [
                            'news_id' => $id
                        ]);
                    }
                }
            } else {
                $urlKeyCheck = $generalData['identifier'];
                if (empty($urlKeyCheck)) {
                    $urlKeyCheck = str_replace(" ", "-", strtolower($generalData['title']));
                }
                $generalData['identifier'] = $urlKeyCheck;
                $urlcollection = $this->urlRewriteCollection->create();
                $urlcollection->addFieldToFilter('request_path', ['eq' => $urlKeyCheck]);
                $collection = $urlcollection->addStoreFilter($generalData['store_id']);
                if (count($collection) > 0) {
                    $this->messageManager->addErrorMessage(
                        __('The value specified in the URL Key is already exists.
                        Please use a unique identifier key')
                    );
                    $this->dataPersistor->set('news_add_form', $data);
                    return $resultRedirect->setPath('*/*/new');
                }
            }
            $storeIds = $generalData['store_id'];
            $categories = $generalData['category'];
            if (count($storeIds) != count($categories)) {
                if (count($storeIds) > count($categories)) {
                    if (!isset($generalData['news_id'])) {
                        $this->messageManager
                            ->addErrorMessage(__('No of Selected Stores Are Greater Than No of Selected Categories'));
                        $this->dataPersistor->set('news_add_form', $generalData);
                        return $resultRedirect
                            ->setPath('*/*/new');
                    } else {
                        $this->messageManager
                            ->addErrorMessage(__('No of Selected Stores Are Greater Than No of Selected Categories'));
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'news_id' => $generalData['news_id'],
                                '_current' => true
                            ]
                        );
                    }
                } else {
                    if (!isset($generalData['news_id'])) {
                        $this->messageManager
                            ->addErrorMessage(__('No of Selected Categories Are Greater Than No of Selected Stores'));
                        $this->dataPersistor->set('news_add_form', $generalData);
                        return $resultRedirect
                            ->setPath('*/*/new');
                    } else {
                        $this->messageManager
                            ->addErrorMessage(__('No of Selected Categories Are Greater Than No of Selected Stores'));
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'news_id' => $generalData['news_id'],
                                '_current' => true
                            ]
                        );
                    }
                }
            } else {
                foreach ($storeIds as $storeId) {
                    $index = 1;
                    foreach ($categories as $category) {
                        $noOfCategories = count($categories);
                        $categoryId = explode('.', $category);
                        if ($categoryId[0] == $storeId) {
                            break;
                        }
                        $storename = '';
                        try {
                            $storename = $this->storeManager->getStore($storeId)->getName();
                        } catch (\Exception $e) {
                            $this->logger->error($e->getMessage());
                        }
                        if ($noOfCategories == $index) {
                            if (!isset($generalData['news_id'])) {
                                $this->messageManager
                                    ->addErrorMessage(__(
                                        'category is not selected against ' .
                                        $storename
                                    ));
                                $this->dataPersistor->set('news_add_form', $generalData);
                                return $resultRedirect
                                    ->setPath('*/*/new');
                            } else {
                                $this->messageManager
                                    ->addErrorMessage(__(
                                        'category is not selected against ' .
                                        $storename
                                    ));
                                return $resultRedirect->setPath(
                                    '*/*/edit',
                                    [
                                        'news_id' => $generalData['news_id'],
                                        '_current' => true
                                    ]
                                );
                            }
                        }
                        $index++;
                    }
                }
            }
            if (strpos($generalData['thumbnail_image'][0]['url'], 'NewsBoard/') !== false) {
                if (isset($generalData['thumbnail_image'])) {
                    if (isset($generalData['thumbnail_image'][0]['file'])) {
                        $generalData['thumbnail_image'] = 'NewsBoard/' .
                            $generalData['thumbnail_image'][0]['file'];
                    } else {
                        $imageName = (explode("/media/", $generalData['thumbnail_image'][0]['url']));
                        $generalData['thumbnail_image'] = $imageName[1];
                    }
                }
            } else {
                $imageName = (explode("/media/", $generalData['thumbnail_image'][0]['url']));
                $generalData['thumbnail_image'] = $imageName[1];
            }
            $model->setData($generalData);
            try {
                $urlKey = $generalData['identifier'];
                if (empty($urlKey)) {
                    $urlKey = str_replace(" ", "-", strtolower($model->getTitle()));
                }
                if (!$this->isValidIdentifier($urlKey)) {
                    if (!isset($generalData['news_id'])) {
                        $this->messageManager
                            ->addErrorMessage(__(
                                "The news URL key can't use capital letters or disallowed symbols. "
                                . "Remove the letters and symbols and try again."
                            ));
                        $this->dataPersistor->set('news_add_form', $generalData);
                        return $resultRedirect
                            ->setPath('*/*/new');
                    } else {
                        $this->messageManager
                            ->addErrorMessage(__(
                                "The news URL key can't use capital letters or disallowed symbols. "
                                . "Remove the letters and symbols and try again."
                            ));
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'news_id' => $generalData['news_id'],
                                '_current' => true
                            ]
                        );
                    }
                }

                if ($this->isNumericIdentifier($urlKey)) {
                    if (!isset($generalData['news_id'])) {
                        $this->messageManager
                            ->addErrorMessage(__(
                                "The news URL key can't use only numbers. Add letters or words and try again."
                            ));
                        $this->dataPersistor->set('news_add_form', $generalData);
                        return $resultRedirect
                            ->setPath('*/*/new');
                    } else {
                        $this->messageManager
                            ->addErrorMessage(__(
                                "The news URL key can't use only numbers. Add letters or words and try again."
                            ));
                        return $resultRedirect->setPath(
                            '*/*/edit',
                            [
                                'news_id' => $generalData['news_id'],
                                '_current' => true
                            ]
                        );
                    }
                }
                $model->setIdentifier($urlKey);
                $model->setUpdateTime('');
                $this->newsRepository->save($model);
                $generalData['identifier'] = $urlKey;
                $this->saveUrlRewrite($generalData, $model);
                $this->messageManager->addSuccessMessage(__('You saved the news.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the news.')
                );
            }
            $this->dataPersistor->set('news_add_form', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
    }

    /**
     * Process result redirect
     *
     * @param $model
     * @param $resultRedirect
     * @param $data
     * @return mixed
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newNews = $this->newsFactory->create(['data' => $data]);
            $newNews->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newNews->setIdentifier($identifier);
            $newNews->setIsActive(false);
            $newNews->setThumbnailImage($model->getThumbnailImage());

            $this->newsRepository->save($newNews);
            $newData = [
                'store_id'      => $data['store_id'],
                'identifier'    => $identifier
            ];
            $this->saveUrlRewrite($newData, $newNews);
            $this->messageManager->addSuccessMessage(__('You duplicated the news.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'news_id' => $newNews->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('news_add_form');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath(
                '*/*/edit',
                ['news_id' => $model->getId(), '_current' => true]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    private function changeDateFormat($date)
    {
        $dateTime = '';
        try {
            $dateTime = $this->timezone->date($date)->format(self::DATE_FORMAT);
            return $dateTime;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $dateTime;
    }

    /**
     *  Check whether news identifier is numeric
     *
     * @param $urlKey
     * @return false|int
     */
    private function isNumericIdentifier($urlKey)
    {
        return preg_match('/^[0-9]+$/', $urlKey);
    }

    /**
     *  Check whether news identifier is valid
     *
     * @param $urlKey
     * @return false|int
     */
    private function isValidIdentifier($urlKey)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $urlKey);
    }

    /**
     * Save URL rewrites
     *
     * @param $data
     * @param News $model
     */
    private function saveUrlRewrite($data, $model)
    {
        try {
            $urlKey = $data['identifier'];
            if ($data['store_id'][0] == 0) {
                $storeManagerDataList = $this->storeManager->getStores();
                $data['store_id'] = [];
                foreach ($storeManagerDataList as $key => $value) {
                    $data['store_id'][] = $key;
                }
            }
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $model->getId(),
                UrlRewrite::ENTITY_TYPE => 'custom',
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::TARGET_PATH => 'news/index/detail/news_id/' . $model->getId()
            ]);
            //Save seo url against each store selected
            foreach ($data['store_id'] as $storeId) {

                /** @var \Magento\UrlRewrite\Model\UrlRewrite */
                $urlRewriteModel = $this->urlRewriteFactory->create();
                /** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite */
                $resourceUrlRewriteModel = $this->resourceUrlRewriteFactory->create();

                /* this url is not created by system so set as 0 */
                $urlRewriteModel->setIsSystem(0);
                /* unique identifier - set random unique value to id path */
                $urlRewriteModel->setIdPath($model->getIdentifier() . '-' . uniqid());
                $urlRewriteModel->setEntityType('custom')
                    ->setRequestPath($urlKey)
                    ->setTargetPath('news/index/detail/news_id/' . $model->getId())
                    ->setRedirectType(0)
                    ->setStoreId($storeId)
                    ->setEntityId($model->getId())
                    ->setDescription($model->getMetaDescription());
                $resourceUrlRewriteModel->save($urlRewriteModel);
            }
        } catch (\Exception $e) {
            $this->logger->info('news url rewrite saving issue:' . $e->getMessage());
        }
    }
}
