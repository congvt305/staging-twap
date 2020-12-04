<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/11/20
 * Time: 9:51 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation\Export;

use Eguana\EventReservation\Model\UserReservation;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class to export user reservations list data in csv form
 *
 * Class GridToCsv
 */
class GridToCsv extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var UserReservation
     */
    private $userReservationModel;

    /**
     * @var ReservationValidation
     */
    private $reservationValidation;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param UserReservation $userReservationModel
     * @param MetadataProvider $metadataProvider
     * @param DataPersistorInterface $dataPersistor
     * @param ReservationValidation $reservationValidation
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        UserReservation $userReservationModel,
        MetadataProvider $metadataProvider,
        DataPersistorInterface $dataPersistor,
        ReservationValidation $reservationValidation
    ) {
        $this->filter = $filter;
        $this->userReservationModel = $userReservationModel;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->fileFactory = $fileFactory;
        $this->dataPersistor = $dataPersistor;
        $this->reservationValidation = $reservationValidation;
        parent::__construct($context);
    }

    /**
     * Export list data to csv form
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $eventId = $this->dataPersistor->get('current_event_id');

            $component = $this->filter->getComponent();

            $name = microtime();
            $file = 'export/' . $component->getName() . $name . '.csv';

            $this->filter->prepareComponent($component);
            $this->filter->applySelectionOnTargetProvider();

            $component->getContext()->getDataProvider()->setLimit(0, 0);

            /** @var SearchResultInterface $searchResult */
            $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

            /** @var DocumentInterface[] $searchResultItems */
            $searchResultItems = $searchResult->getItems();

            $fields = $this->metadataProvider->getFields($component);
            $options = $this->metadataProvider->getOptions();

            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($this->metadataProvider->getHeaders($component));

            foreach ($searchResultItems as $document) {
                /** @var UserReservation $document */
                if ($document->getEventId() == $eventId) {
                    $storeIds = $this->reservationValidation->availableCountersForEvent($eventId);

                    if (in_array($document->getOfflineStoreId(), $storeIds)) {
                        $status = $document->getStatus();
                        $availableStatuses = $this->userReservationModel->getAvailableStatuses();
                        $document->setStatus($availableStatuses[$status]);

                        $agreement = $document->getAgreement();
                        $agreementOptions = $this->userReservationModel->getAgreementOptions();
                        $document->setAgreement($agreementOptions[$agreement]);

                        $this->metadataProvider->convertDate($document, $component->getName());
                        $stream->writeCsv($this->metadataProvider->getRowData($document, $fields, $options));
                    }
                }
            }

            $stream->unlock();
            $stream->close();
            return $this->fileFactory->create('export.csv', [
                'type' => 'filename',
                'value' => $file,
                'rm' => true
            ], 'var');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
    }
}
