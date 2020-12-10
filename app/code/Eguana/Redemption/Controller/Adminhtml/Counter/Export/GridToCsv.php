<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/11/20
 * Time: 9:51 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Counter\Export;

use Eguana\Redemption\Model\Counter;
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
use Magento\Framework\Exception\FileSystemException;

/**
 * Class to export user counter list data in csv form
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
     * @var Counter
     */
    private $counterModel;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param Counter $counterModel
     * @param MetadataProvider $metadataProvider
     * @param DataPersistorInterface $dataPersistor
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        Counter $counterModel,
        MetadataProvider $metadataProvider,
        DataPersistorInterface $dataPersistor
    ) {
        $this->filter = $filter;
        $this->counterModel = $counterModel;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->fileFactory = $fileFactory;
        $this->dataPersistor = $dataPersistor;
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
            $redemptionId = $this->dataPersistor->get('current_redemption_id');

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
                /** @var Counter $document */
                if ($document->getRedemptionId() == $redemptionId) {
                    $status = $document->getStatus();
                    $availableStatuses = $this->counterModel->getAvailableStatuses();
                    $document->setStatus($availableStatuses[$status]);
                    $this->metadataProvider->convertDate($document, $component->getName());
                    $stream->writeCsv($this->metadataProvider->getRowData($document, $fields, $options));
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
