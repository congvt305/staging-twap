<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 10:00 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Controller\Adminhtml\AbstractController;
use Eguana\EventReservation\Model\Event;
use Eguana\EventReservation\Model\EventFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteFactory as ResourceUrlRewriteFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Psr\Log\LoggerInterface;

/**
 * Save event action class
 *
 * Class Save
 */
class Save extends AbstractController implements HttpPostActionInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

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
     * @var Event
     */
    private $eventModel;

    /**
     * @var UrlRewriteCollectionFactory
     */
    private $urlRewriteCollection;

    /**
     * @param Context $context
     * @param Event $eventModel
     * @param PageFactory $resultPageFactory
     * @param DataPersistorInterface $dataPersistor
     * @param EventFactory $eventFactory
     * @param EventRepositoryInterface $eventRepository
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param LoggerInterface $logger
     * @param ResourceUrlRewriteFactory $resourceUrlRewriteFactory
     * @param UrlRewriteCollectionFactory $urlRewriteCollection
     */
    public function __construct(
        Context $context,
        Event $eventModel,
        PageFactory $resultPageFactory,
        DataPersistorInterface $dataPersistor,
        EventFactory $eventFactory,
        EventRepositoryInterface $eventRepository,
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        LoggerInterface $logger,
        ResourceUrlRewriteFactory $resourceUrlRewriteFactory,
        UrlRewriteCollectionFactory $urlRewriteCollection
    ) {
        $this->logger = $logger;
        $this->eventModel = $eventModel;
        $this->urlPersist = $urlPersist;
        $this->eventFactory = $eventFactory;
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->urlRewriteCollection = $urlRewriteCollection;
        $this->resourceUrlRewriteFactory = $resourceUrlRewriteFactory;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Save event action
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $generalData = $data;
            $generalData['title'] = trim($generalData['title']);
            if (isset($generalData['is_active']) && $generalData['is_active'] === 'true') {
                $generalData['is_active'] = Event::STATUS_ENABLED;
            }
            if (empty($generalData['event_id'])) {
                $generalData['event_id'] = null;
            }

            if (empty($generalData['identifier'])) {
                $generalData['identifier'] = str_replace(" ", "-", strtolower($generalData['title']));
            }

            $model = $this->eventFactory->create();
            if ($generalData['event_id']) {
                try {
                    $model = $this->eventRepository->getById($generalData['event_id']);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This event no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }

                if ($model->getIdentifier() != $generalData['identifier'] ||
                    $model->getStoreId()[0] != $generalData['store_id']) {
                    $urlExist = $this->checkIdentifierExist($generalData['identifier'], $generalData['store_id']);

                    if ($urlExist) {
                        $this->dataPersistor->set('event_reservation_form', $data);
                        return $resultRedirect->setPath('*/*/edit', [
                            'event_id' => $generalData['event_id']
                        ]);
                    }
                }
            } else {
                $urlExist = $this->checkIdentifierExist($generalData['identifier'], $generalData['store_id']);

                if ($urlExist) {
                    $data['store_id'] = '';
                    $this->dataPersistor->set('event_reservation_form', $data);
                    return $resultRedirect->setPath('*/*/new');
                }
            }

            if (strpos($generalData['thumbnail'][0]['url'], 'eventreservation/tmp/thumbnail/') !== false) {
                if (isset($generalData['thumbnail'])) {
                    if (isset($generalData['thumbnail'][0]['file'])) {
                        $generalData['thumbnail'] = 'eventreservation/tmp/thumbnail/' .
                            $generalData['thumbnail'][0]['file'];
                    } else {
                        $imageName = (explode("/media/", $generalData['thumbnail'][0]['url']));
                        $generalData['thumbnail'] = $imageName[1];
                    }
                }
            } else {
                $imageName = (explode("/media/", $generalData['thumbnail'][0]['url']));
                $generalData['thumbnail'] = $imageName[1];
            }

            $model->setData($generalData);

            try {
                $this->eventRepository->save($model);
                $this->saveUrlRewrite($generalData, $model);

                $this->messageManager->addSuccessMessage(__('You saved the event.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Exception $e) {
                $this->logger->info('Event saving issue:' . $e->getMessage());
            }

            $this->dataPersistor->set('event_reservation_form', $data);
            return $resultRedirect->setPath('*/*/edit', [
                'event_id' => $this->getRequest()->getParam('event_id')
            ]);
        }
        return $resultRedirect->setPath('*/*/');
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
     * @param EventInterface $model
     * @param Redirect $resultRedirect
     * @param array $data
     * @return mixed
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {
        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newEvent = $this->eventFactory->create(['data' => $data]);
            $newEvent->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newEvent->setIdentifier($identifier);
            $newEvent->setIsActive(Event::STATUS_DISABLED);
            $newEvent->setThumbnail($model->getThumbnail());

            $this->eventRepository->save($newEvent);
            $newData = [
                'store_id'      => $data['store_id'],
                'identifier'    => $identifier
            ];
            $this->saveUrlRewrite($newData, $newEvent);
            $this->messageManager->addSuccessMessage(__('You duplicated the event.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'event_id' => $newEvent->getId(),
                    '_current' => true
                ]
            );
        }
        $this->dataPersistor->clear('event_reservation_form');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath(
                '*/*/edit',
                ['event_id' => $model->getId(), '_current' => true]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     *  Check whether event identifier is numeric
     *
     * @param $urlKey
     * @return false|int
     */
    private function isNumericIdentifier($urlKey)
    {
        return preg_match('/^[0-9]+$/', $urlKey);
    }

    /**
     *  Check whether event identifier is valid
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
     * @param Event $model
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
                UrlRewrite::TARGET_PATH => 'event/reservation/index/id/' . $model->getId()
            ]);

            $urlRewriteModel = $this->urlRewriteFactory->create();
            $resourceUrlRewriteModel = $this->resourceUrlRewriteFactory->create();

            $urlRewriteModel->setIsSystem(0);
            $urlRewriteModel->setIdPath($model->getIdentifier() . '-' . uniqid());
            $urlRewriteModel->setEntityType('custom')
                ->setRequestPath($urlKey)
                ->setTargetPath('event/reservation/index/id/' . $model->getId())
                ->setRedirectType(0)
                ->setStoreId($storeId)
                ->setEntityId($model->getId())
                ->setDescription($model->getMetaDescription());
            $resourceUrlRewriteModel->save($urlRewriteModel);
        } catch (\Exception $e) {
            $this->logger->info('Event url rewrite saving issue:' . $e->getMessage());
        }
    }
}
