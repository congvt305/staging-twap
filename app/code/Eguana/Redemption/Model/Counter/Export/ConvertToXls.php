<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 28/10/20
 * Time: 2:22 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model\Counter\Export;

use Eguana\Redemption\Model\Counter;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Model class to export data in xls
 *
 * Class ConvertToXls
 */
class ConvertToXls
{
    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var ExcelFactory
     */
    private $excelFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @var SearchResultIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Counter
     */
    private $counterModel;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param Counter $counterModel
     * @param MetadataProvider $metadataProvider
     * @param ExcelFactory $excelFactory
     * @param SearchResultIteratorFactory $iteratorFactory
     * @param DataPersistorInterface $dataPersistor
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        Counter $counterModel,
        MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        SearchResultIteratorFactory $iteratorFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->filter = $filter;
        $this->counterModel = $counterModel;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Returns Filters with options
     *
     * @return array
     */
    private function getOptions() : array
    {
        if (!$this->options) {
            $this->options = $this->metadataProvider->getOptions();
        }
        return $this->options;
    }

    /**
     * Returns DB fields list
     *
     * @return array
     * @throws LocalizedException
     */
    private function getFields()
    {
        try {
            if (!$this->fields) {
                $component = $this->filter->getComponent();
                $this->fields = $this->metadataProvider->getFields($component);
            }
        } catch (\Exception $exception) {
            throw new LocalizedException(
                __('Could not get counter fields: %1', $exception->getMessage()),
                $exception
            );
        }
        return $this->fields;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @return array
     * @throws LocalizedException
     */
    public function getRowData(DocumentInterface $document) : array
    {
        return $this->metadataProvider->getRowData($document, $this->getFields(), $this->getOptions());
    }

    /**
     * Returns XML file
     *
     * @return array
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function getXlsFile()
    {
        $redemptionId = $this->dataPersistor->get('current_redemption_id');

        $component = $this->filter->getComponent();

        $name = microtime();
        $file = 'export/' . $component->getName() . $name . '.xls';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
        $searchResultItems = $searchResult->getItems();

        $filteredItems = [];
        foreach ($searchResultItems as $item) {
            /** @var Counter $item */
            if ($item->getRedemptionId() == $redemptionId) {
                $status = $item->getStatus();
                $availableStatuses = $this->counterModel->getAvailableStatuses();
                $item->setStatus($availableStatuses[$status]);
                $filteredItems[] = $item;
            }
        }

        $this->prepareItems($component->getName(), $filteredItems);

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $filteredItems]);

        /** @var Excel $excel */
        $excel = $this->excelFactory->create([
            'iterator' => $searchResultIterator,
            'rowCallback' => [$this, 'getRowData'],
        ]);

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xls');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }

    /**
     * Prepare Items
     *
     * @param string $componentName
     * @param array $items
     */
    private function prepareItems($componentName, array $items = [])
    {
        foreach ($items as $document) {
            $this->metadataProvider->convertDate($document, $componentName);
        }
    }
}
