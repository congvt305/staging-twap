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

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Controller\Adminhtml\AbstractController;
use Eguana\Magazine\Model\Magazine;
use Eguana\Magazine\Model\MagazineFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;

/**
 * Action for save button
 *
 * Class Save
 */
class Save extends AbstractController
{
    /**
     * @var \Eguana\Magazine\Helper\Data
     */
    private $helperData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

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
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory
     */
    protected $resourceUrlRewriteFactory;

    /**
     * @var UrlPersistInterface\Proxy
     */
    protected $urlPersist;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param MagazineFactory|null $magazineFactory
     * @param MagazineRepositoryInterface|null $magazineRepository
     * @param UrlPersistInterface\Proxy $urlPersist
     * @param \Magento\UrlRewrite\Model\UrlRewrite $urlRewriteFactory
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $resourceUrlRewriteFactory
     * @param \Eguana\Magazine\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        MagazineFactory $magazineFactory,
        MagazineRepositoryInterface $magazineRepository,
        \Eguana\Magazine\Helper\Data $helperData,
        LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->magazineFactory = $magazineFactory;
        $this->magazineRepository = $magazineRepository;
        $this->helperData= $helperData;
        $this->logger = $logger;

        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }
    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
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

            if (isset($generalData['store_id'])) {
                $generalData['store_id'] = implode(',', $generalData['store_id']);
            }
            $model->setData($generalData);

            try {
                $model->setUpdatedAt('');

                $this->magazineRepository->save($model);
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->logger->debug($exception->getMessage());
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
     * @param \Eguana\Magazine\Api\Data\MagazineInterface $model
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirect
     * @param array $data
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
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
                    'entity_id' => $newMagazine->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_magazine');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
