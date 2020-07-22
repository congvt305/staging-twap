<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:18 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Eguana\Magazine\Api\Data\MagazineInterface as MagazineInterfaceAlias;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Controller\Adminhtml\AbstractController;
use Eguana\Magazine\Helper\Data as DataAlias;
use Eguana\Magazine\Model\Magazine;
use Eguana\Magazine\Model\MagazineFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect as RedirectAlias;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewrite as UrlRewriteAlias1;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlRewrite as UrlRewriteAlias;
use Magento\UrlRewrite\Model\UrlRewriteFactory as UrlRewriteFactoryAlias;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Action for save button
 * Class Save
 */
class Save extends AbstractController
{
    /**
     * @var DataAlias
     */
    private $helperData;

    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var MagazineFactory
     */
    private $magazineFactory;

    /**
     * @var MagazineRepositoryInterface
     */
    private $magazineRepository;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @var UrlRewriteFactoryAlias
     */
    private $urlRewriteFactory;

    /**
     * @var UrlPersistInterface\Proxy
     */
    private $urlPersist;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param MagazineFactory|null $magazineFactory
     * @param MagazineRepositoryInterface|null $magazineRepository
     * @param UrlPersistInterface\Proxy $urlPersist
     * @param UrlRewriteAlias $urlRewriteFactory
     * @param DataAlias $helperData
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        MagazineFactory $magazineFactory,
        MagazineRepositoryInterface $magazineRepository,
        DataAlias $helperData,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->magazineFactory = $magazineFactory;
        $this->magazineRepository = $magazineRepository;
        $this->helperData= $helperData;
        $this->timezone = $timezone;
        $this->logger = $logger;
        parent::__construct($context, $resultPageFactory);
    }
    /**
     * Save action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var RedirectAlias $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $generalData = $data;

            if (isset($generalData['active']) && $generalData['active'] === '1') {
                $generalData['is_active'] = 1;
            }

            if (empty($generalData['entity_id'])) {
                $generalData['entity_id'] = null;
            }
            $id = $generalData['entity_id'];

            /** @var Magazine $model */
            $model = $this->magazineFactory->create();
            $generalData['show_date'] = $this->changeDateFormat($generalData['show_date']);

            if ($id) {
                try {
                    $model = $this->magazineRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager
                        ->addErrorMessage(__('This magazine no longer exists.'));
                    return $this->processResultRedirect($model, $resultRedirect, $data);
                }
            }

            if (isset($generalData['thumbnail_image'])) {
                $generalData['thumbnail_image'] = 'Magazine/' .
                    $generalData['thumbnail_image'][0]['file'];
            }
            $model->setData($generalData);

            try {
                $model->setUpdatedAt('');
                $this->magazineRepository->save($model);
                $this->messageManager->addSuccess(__('Magazine has been successfully saved.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->logger->debug($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the magazine.')
                );
            }

            $this->dataPersistor->set('eguana_magazine', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
    }
    /**
     * Process result redirect
     *
     * @param MagazineInterfaceAlias $model
     * @param RedirectAlias $resultRedirect
     * @param array $data
     * @return RedirectAlias
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newMagazine = $this->magazineFactory->create(['data' => $data]);
            $newMagazine->setId(null);
            $newMagazine->setIsActive(false);
            $newMagazine->setStoreId($model->getStoreId());
            $newMagazine->setThumbnailImage($model->getThumbnailImage());
            $this->magazineRepository->save($newMagazine);
            $this->messageManager->addSuccessMessage(__('You duplicated the magazine.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'entity_id' => $newMagazine->getEntityId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_magazine');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit/', ['entity_id' => $model->getEntityId(), '_current' => true]);
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
        try {
            return $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}
