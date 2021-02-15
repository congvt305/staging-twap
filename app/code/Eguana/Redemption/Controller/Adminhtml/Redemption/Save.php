<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Controller\Adminhtml\AbstractController;
use Eguana\Redemption\Model\Redemption;
use Eguana\Redemption\Model\RedemptionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as ResourceUrlRewriteFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
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
     * @var Redemption
     */
    private $redemptionModel;

    /**
     * @var RedemptionFactory
     */
    private $redemptionFactory;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var ResourceUrlRewriteFactory
     */
    private $resourceUrlRewriteFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Redemption $redemptionModel
     * @param RedemptionFactory|null $redemptionFactory
     * @param RedemptionRepositoryInterface|null $redemptionRepository
     * @param TimezoneInterface $timezone
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param LoggerInterface $logger
     * @param ResourceUrlRewriteFactory $resourceUrlRewriteFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        Redemption $redemptionModel,
        RedemptionFactory $redemptionFactory,
        RedemptionRepositoryInterface $redemptionRepository,
        TimezoneInterface $timezone,
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        LoggerInterface $logger,
        ResourceUrlRewriteFactory $resourceUrlRewriteFactory,
        UrlRewriteCollectionFactory $urlRewriteCollection,
        StoreManagerInterface $storeManager
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->redemptionModel = $redemptionModel;
        $this->redemptionFactory = $redemptionFactory;
        $this->redemptionRepository = $redemptionRepository;
        $this->timezone = $timezone;
        $this->resourceUrlRewriteFactory = $resourceUrlRewriteFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlPersist = $urlPersist;
        $this->logger = $logger;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $resultPageFactory
        );
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (isset($data['counter_total_seats'])) {
                $data['counter_seats'] = !is_array($data['counter_total_seats']) ?
                    [(int)$data['counter_total_seats']] : $data['counter_total_seats'];
            }
            $generalData = $data;
            if (isset($generalData['active']) && $generalData['active'] === '1') {
                $generalData['is_active'] = 1;
            }
            if (empty($generalData['redemption_id'])) {
                $generalData['redemption_id'] = null;
            }
            $generalData['title'] = trim($generalData['title']);
            if (empty($generalData['identifier'])) {
                $generalData['identifier'] = str_replace(" ", "-", strtolower($generalData['title']));
            }

            $generalData['store_id'] = $generalData['store_id_name'];
            $id = $generalData['redemption_id'];
            /** @var Redemption $model */
            $model = $this->redemptionFactory->create();
            if (!$id) {
                $urlExist = $this->checkIdentifierExist($generalData['identifier'], $generalData['store_id']);

                if ($urlExist) {
                    $data['store_id_name'] = '';
                    $this->dataPersistor->set('eguana_redemption', $data);
                    return $resultRedirect->setPath('*/*/new');
                }

            }
            if ($id) {
                try {
                    $model = $this->redemptionRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This redemption no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                if ($model->getIdentifier() != $generalData['identifier'] ||
                    $model->getStoreId()[0] != $generalData['store_id']) {
                    $urlExist = $this->checkIdentifierExist($generalData['identifier'], $generalData['store_id']);

                    if ($urlExist) {
                        return $resultRedirect->setPath('*/*/edit', [
                            'redemption_id' => $id
                        ]);
                    }
                }

            }
            if (!(strtotime($generalData['start_date']) <= strtotime($generalData['end_date']))) {
                $this->messageManager
                    ->addErrorMessage(__('Start Date should be before End Date'));
                $this->dataPersistor->set('eguana_redemption', $data);
                if ($id) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'redemption_id' => $id
                    ]);
                } else {
                    return $resultRedirect->setPath('*/*/new');
                }
            }
            $generalData['start_date'] = $this->changeDateFormat($generalData['start_date']);
            $generalData['end_date'] = $this->changeDateFormat($generalData['end_date']);
            if ($id) {
                try {
                    $model = $this->redemptionRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager
                        ->addErrorMessage(__('This redemption no longer exists.'));
                    return $this->processResultRedirect($model, $resultRedirect, $data);
                }
            }
            if (strpos($generalData['image'][0]['url'], 'redemption/tmp/feature/') !== false) {
                if (isset($generalData['image'])) {
                    if (isset($generalData['image'][0]['file'])) {
                        $generalData['image'] = 'redemption/tmp/feature/' .
                            $generalData['image'][0]['file'];
                    } else {
                        $imageName = (explode("/media/", $generalData['image'][0]['url']));
                        $generalData['image'] = $imageName[1];
                    }
                }
            } else {
                $imageName = (explode("/media/", $generalData['image'][0]['url']));
                $generalData['image'] = $imageName[1];
            }
            if (isset($generalData['thank_you_image'][0]['url'])) {
                $imageName = (explode("/media/", $generalData['thank_you_image'][0]['url']));
                $generalData['thank_you_image'] = $imageName[1];
            } elseif (isset($generalData['thank_you_image'][0]['file'])) {
                $generalData['thank_you_image'] = 'redemption/tmp/feature/' .
                    $generalData['thank_you_image'][0]['file'];
            } else {
                $generalData['thank_you_image'] = '';
            }
            $model->setData($generalData);
            try {
                $this->redemptionRepository->save($model);
                $this->saveUrlRewrite($generalData, $model);

                $this->messageManager->addSuccessMessage(__('You saved the redemption.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the redemption.')
                );
            }
            $this->dataPersistor->set('eguana_redemption', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
    }

    /**
     * Check either identifier exists or not
     *
     * @param $identifier
     * @param $storeId
     * @return bool
     */
    private function checkIdentifierExist($identifier, $storeId)
    {
        if (!$this->isValidIdentifier($identifier)) {
            $this->messageManager->addErrorMessage(
                __(
                    "The event URL key can't use capital letters or disallowed symbols. "
                    . "Remove the letters and symbols and try again."
                )
            );
            return true;
        }

        if ($this->isNumericIdentifier($identifier)) {
            $this->messageManager->addErrorMessage(
                __("The event URL key can't use only numbers. Add letters or words and try again.")
            );
            return true;
        }

        $urlcollection = $this->urlRewriteCollection->create();
        $collection = $urlcollection->addFieldToFilter('request_path', ['eq' => $identifier]);
        $collection = $urlcollection->addStoreFilter($storeId);
        if (count($collection) > 0) {
            $this->messageManager->addErrorMessage(
                __('The value specified in the URL Key is already exists.
                        Please use a unique identifier key')
            );
            return true;
        }

        return false;
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
            $newRedemption = $this->redemptionFactory->create(['data' => $data]);
            if (!(strtotime($newRedemption->getStartDate()) <= strtotime($newRedemption->getEndDate()))) {
                if (!$this->messageManager->getMessages() == 'Start Date should be before End Date') {
                    $this->messageManager
                        ->addErrorMessage(__('Start Date should be before End Date'));
                }
                return $resultRedirect->setPath('*/*/edit', ['redemption_id' => $model->getId(), '_current' => true]);
            }
            $newRedemption->setId(null);
            $newRedemption->setIsActive(false);
            $newRedemption->setThankYouImage($model->getThankYouImage());
            $newRedemption->setImage($model->getImage());
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newRedemption->setIdentifier($identifier);
            $newRedemption->setStartDate($this->changeDateFormat($newRedemption->getStartDate()));
            $newRedemption->setEndDate($this->changeDateFormat($newRedemption->getEndDate()));
            $this->redemptionRepository->save($newRedemption);
            $data['identifier'] = $identifier;
            $data['store_id'] = $model->getStoreId();
            $this->saveUrlRewrite($data, $newRedemption);
            $this->messageManager->addSuccessMessage(__('You duplicated the redemption.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'redemption_id' => $newRedemption->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_redemption');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['redemption_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * This method is used to change the date format
     *
     * @param $date
     * @return string
     */
    private function changeDateFormat($date)
    {
        try {
            $formatedDate = $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $formatedDate;
    }

    /**
     *  Check whether redemption identifier is numeric
     *
     * @param $urlKey
     * @return false|int
     */
    private function isNumericIdentifier($urlKey)
    {
        return preg_match('/^[0-9]+$/', $urlKey);
    }

    /**
     *  Check whether redemption identifier is valid
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
     * @param Redemption $model
     */
    private function saveUrlRewrite($data, $model)
    {
        try {
            $urlKey = $data['identifier'];
            $storeId = $data['store_id'];
            $this->urlPersist->deleteByData([
                UrlRewrite::ENTITY_ID => $model->getId(),
                UrlRewrite::ENTITY_TYPE => 'custom',
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::TARGET_PATH => 'redemption/details/index/redemption_id/' . $model->getId()
            ]);
            //Save seo url against each store selected
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
                    ->setTargetPath('redemption/details/index/redemption_id/' . $model->getId())
                    ->setRedirectType(0)
                    ->setStoreId($storeId)
                    ->setEntityId($model->getId())
                    ->setDescription($model->getMetaDescription());
                $resourceUrlRewriteModel->save($urlRewriteModel);
        } catch (\Exception $e) {
            $this->logger->info('Redemption url rewrite saving issue:' . $e->getMessage());
        }
    }
}
