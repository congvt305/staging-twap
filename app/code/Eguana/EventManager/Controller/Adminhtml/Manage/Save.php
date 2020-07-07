<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 7:22 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Eguana\EventManager\Api\Data\EventManagerInterface as EventManagerInterfaceAlias;
use Eguana\EventManager\Model\EventManager;
use Magento\Backend\Model\View\Result\Redirect as RedirectAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Eguana\EventManager\Model\EventManagerFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Eguana\EventManager\Controller\Adminhtml\AbstractController;
use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Action for save button
 *
 * Class Save
 */
class Save extends AbstractController
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var EventManagerFactory
     */
    private $eventManagerFactory;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var UrlPersistInterface\Proxy
     */
    private $urlPersist;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataPersistorInterface $dataPersistor
     * @param EventManagerFactory|null $eventManagerFactory
     * @param EventManagerRepositoryInterface|null $eventManagerRepository
     * @param UrlPersistInterface\Proxy $urlPersist
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        EventManagerFactory $eventManagerFactory,
        EventManagerRepositoryInterface $eventManagerRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->eventManagerFactory = $eventManagerFactory;
        $this->eventManagerRepository = $eventManagerRepository;
        parent::__construct($context, $coreRegistry, $resultPageFactory);
    }

    /**
     * Save action
     *
     * @return ResultInterfaceAlias
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
            /** @var EventManager $model */
            $model = $this->eventManagerFactory->create();
            if (!(strtotime($generalData['start_date']) <= strtotime($generalData['end_date']))) {
                $model = $this->eventManagerRepository->getById($id);
                $this->messageManager
                    ->addErrorMessage(__('Start Date should be before End Date'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            }
            if ($id) {
                try {
                    $model = $this->eventManagerRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager
                        ->addErrorMessage(__('This event no longer exists.'));
                    return $this->processResultRedirect($model, $resultRedirect, $data);
                }
            }
            if (isset($generalData['thumbnail_image'])) {
                $generalData['thumbnail_image'] = 'EventManager/' .
                    $generalData['thumbnail_image'][0]['file'];
            }
            if (isset($generalData['store_id'])) {
                $generalData['store_id'] = implode(',', $generalData['store_id']);
            }
            $model->setData($generalData);
            try {
                $model->setUpdatedAt('');
                $this->eventManagerRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the event.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the event.')
                );
            }
            $this->dataPersistor->set('eguana_event_manager', $data);
            return $this->processResultRedirect($model, $resultRedirect, $data);
        }
        return $this->processResultRedirect($model, $resultRedirect, $data);
    }

    /**
     * Process result redirect
     *
     * @param EventManagerInterfaceAlias $model
     * @param RedirectAlias $resultRedirect
     * @param $model
     * @param $resultRedirect
     * @param $data
     * @return mixed
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newEvent = $this->eventManagerFactory->create(['data' => $data]);
            if (!(strtotime($newEvent->getStartDate()) <= strtotime($newEvent->getEndDate()))) {
                if (!$this->messageManager->getMessages() == 'Start Date should be before End Date') {
                    $this->messageManager
                        ->addErrorMessage(__('Start Date should be before End Date'));
                }
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
            }
            $newEvent->setId(null);
            $newEvent->setIsActive(false);
            $newEvent->setStoreId($model->getStoreId());
            $newEvent->setThumbnailImage($model->getThumbnailImage());
            $this->eventManagerRepository->save($newEvent);
            $this->messageManager->addSuccessMessage(__('You duplicated the event.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'entity_id' => $newEvent->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('eguana_event_manager');
        if ($this->getRequest()->getParam('back', false) === 'continue') {
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId(), '_current' => true]);
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
