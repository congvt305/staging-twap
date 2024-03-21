<?php

namespace CJ\Sitemap\Model;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapConfigReaderInterface;
use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Robots\Model\Config\Value;
use Magento\Sitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Magento\Sitemap\Model\SitemapItemInterface;

class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var \CJ\Sitemap\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Sitemap\Helper\Data $sitemapData
     * @param Filesystem $filesystem
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory
     * @param \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $modelDate
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \CJ\Sitemap\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     * @param ItemProviderInterface|null $itemProvider
     * @param SitemapConfigReaderInterface|null $configReader
     * @param \Magento\Sitemap\Model\SitemapItemInterfaceFactory|null $sitemapItemFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context                             $context,
        \Magento\Framework\Registry                                  $registry,
        \Magento\Framework\Escaper                                   $escaper,
        \Magento\Sitemap\Helper\Data                                 $sitemapData,
        Filesystem                                                   $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory  $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory         $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime                  $modelDate,
        \Magento\Store\Model\StoreManagerInterface                   $storeManager,
        \Magento\Framework\App\RequestInterface                      $request,
        \Magento\Framework\Stdlib\DateTime                           $dateTime,
        \CJ\Sitemap\Helper\Data                                      $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource      $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb                $resourceCollection = null,
        array                                                        $data = [],
        DocumentRoot                                                 $documentRoot = null,
        ItemProviderInterface                                        $itemProvider = null,
        SitemapConfigReaderInterface                                 $configReader = null,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory           $sitemapItemFactory = null
    )
    {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data,
            $documentRoot,
            $itemProvider,
            $configReader,
            $sitemapItemFactory
        );
    }

    /**
     * @return $this|Sitemap
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function generateXml()
    {
        $this->_initSitemapItems();
        $excludeUrls = $this->helper->getExcludeUrls($this);
        /** @var $item SitemapItemInterface */
        foreach ($this->_sitemapItems as $item) {
            if (in_array($item->getUrl(), $excludeUrls)) {
                continue;
            }

            $xml = $this->_getSitemapRow(
                $item->getUrl(),
                $item->getUpdatedAt(),
                $item->getChangeFrequency(),
                $item->getPriority(),
                $item->getImages()
            );

            if ($this->_isSplitRequired($xml) && $this->_sitemapIncrement > 0) {
                $this->_finalizeSitemap();
            }

            if (!$this->_fileSize) {
                $this->_createSitemap();
            }

            $this->_writeSitemapRow($xml);
            // Increase counters
            $this->_lineCount++;
            $this->_fileSize += strlen($xml);
        }

        $this->_finalizeSitemap();

        if ($this->_sitemapIncrement == 1) {
            // In case when only one increment file was created use it as default sitemap
            $sitemapPath = $this->getSitemapPath() !== null ? rtrim($this->getSitemapPath(), '/') : '';
            $path = $sitemapPath . '/' . $this->_getCurrentSitemapFilename($this->_sitemapIncrement);
            $destination = $sitemapPath . '/' . $this->getSitemapFilename();

            $this->_directory->renameFile($path, $destination);
        } else {
            // Otherwise create index file with list of generated sitemaps
            $this->_createSitemapIndex();
        }

        $this->setSitemapTime($this->_dateModel->gmtDate('Y-m-d H:i:s'));
        $this->save();

        return $this;
    }
}
