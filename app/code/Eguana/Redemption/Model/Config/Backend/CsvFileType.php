<?php

namespace Eguana\Redemption\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use \Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\Storage\WriterInterface;
use \Magento\Store\Model\ScopeInterface;

class CsvFileType extends File
{
    const FOLDER_NAME = 'pos_number';
    const CONFIGURATION_PATH_POS_NUMBER = 'redemption/configuration/pos_numbers';

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvReader;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Reader\Xlsx
     */
    protected $xlsxReader;

    public function __construct(
        \Magento\Framework\Model\Context                                           $context,
        \Magento\Framework\Registry                                                $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface                         $config,
        \Magento\Framework\App\Cache\TypeListInterface                             $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory                           $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem                                                                 $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource                    $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb                              $resourceCollection = null,
        \Magento\Framework\File\Csv                                                $csvReader,
        WriterInterface                                                            $configWriter,
        \PhpOffice\PhpSpreadsheet\Reader\Xlsx                                      $xlsxReader,
        array                                                                      $data = []
    )
    {
        $this->csvReader = $csvReader;
        $this->configWriter = $configWriter;
        $this->xlsxReader = $xlsxReader;
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
    }

    /**
     * @return string[]
     */
    public function _getAllowedExtensions()
    {
        return ['xlsx'];
    }

    /**
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ($fileName = $this->getValue()) {
            $mediaPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
            $filePath = $mediaPath . self::FOLDER_NAME . '/' . $fileName;
            $this->xlsxReader->setReadDataOnly(true);
            $sheetData = $this->xlsxReader->load($filePath);
            $posNumbers = $sheetData->getActiveSheet()->toArray('', true, true, false);
            $posNumbers = implode(',', array_map(function ($item) {
                return $item[0];
            }, array_values($posNumbers)));
            if ($posNumbers) {
                $this->configWriter->save(
                    self::CONFIGURATION_PATH_POS_NUMBER,
                    $posNumbers,
                    ScopeInterface::SCOPE_WEBSITES,
                    $this->getScopeId()
                );
            }
        }
    }
}
