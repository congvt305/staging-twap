<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/23/20
 * Time: 7:52 AM
 */
namespace Eguana\Magazine\ViewModel\Index;

use Eguana\Magazine\Model\ResourceModel\Magazine\Collection;
use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for get magazine data for the listing page
 *
 * Class MagazineList
 */
class MagazineList implements ArgumentInterface
{
    const BANNER_TYPE = 1;
    const IMAGE_TYPE = 2;
    const VIDEO_TYPE = 3;
    const DETAIL_URL = 'magazine/detail/index/id/';
    const MONTHLY_DETAILS_URL = 'magazine/index/index/month/';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * MagazineList constructor.
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param UrlInterface $urlInterface
     * @param DateTime $dateTime
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManagerInterface,
        UrlInterface $urlInterface,
        DateTime $dateTime,
        RequestInterface $requestInterface,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->urlInterface = $urlInterface;
        $this->dateTime = $dateTime;
        $this->requestInterface = $requestInterface;
        $this->logger = $logger;
    }

    /**
     * this functional will give main banner deatils
     * @return array
     */
    private function getLargeThumbnail($type)
    {
        $result = [];
        $magazineCollection = $this->getMagazineCollection($type);
        try {
            return $magazineCollection;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * This will return image magazine array
     * @return array
     */
    public function getImageMagazineCollection()
    {
        $imageCollection = $this->getMagazineCollection(self::IMAGE_TYPE);
        $month = $this->requestInterface->getParam('month');
        $year = $this->requestInterface->getParam('year');
        if (isset($month) &&  isset($year)) {
            $startDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '31'.' 00:00:00');
        } else {
            $startDate = $this->dateTime->gmtDate('y-m-'.'1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate('y-m-'.'31'.' 00:00:00');
        }
        $imageCollection ->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        return $imageCollection;
    }

    /**
     * This will return image magazine array
     * @return array
     */
    public function getVideoMagazineCollection()
    {
        $imageCollection = $this->getMagazineCollection(self::VIDEO_TYPE);
        return $imageCollection->getData();
    }

    /**
     * this function will give us magazine collection
     * @param null $type
     * @param $dateSort
     * @return Collection
     */
    public function getMagazineCollection($type = null, $dateSort = null)
    {
        try {
            $storeId =  $this->storeManagerInterface->getStore()->getId();
            $magazineCollection = $this->collectionFactory->create();
            $magazineCollection->addFieldToFilter(
                'type',
                ['eq' => $type]
            )->addFieldToFilter(
                ['store_id','store_id','store_id','store_id'],
                [["like" => '%' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . '%'],
                    ["in" => ['0', $storeId]]]
            )->setOrder(
                "sort_order",
                'ASC'
            );
        } catch (\Exception $exception) {
            $this->logger->error($e->getMessage());
        }
        return $magazineCollection;
    }

    /**
     * get magazine deatil url
     * @param $id
     * @return string
     */
    public function getItemDetailsUrl($id)
    {
        return self::DETAIL_URL . $id;
    }

    /**
     * get thumbnail src for the image magazine
     * @param $thumnail
     * @return string
     */
    public function getThumbnailSrc($thumnail)
    {
        try {
            $file = ltrim(str_replace('\\', '/', $thumnail), '/');
            $thumbnailSrc = $this->storeManagerInterface->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . $file;
        } catch (\Exception $exception) {
            $this->logger->error($e->getMessage());
        }
        return $thumbnailSrc;
    }

    /**
     * this functional will give main banner deatils
     * @return array
     */
    public function getVideoThumbnail()
    {
        $collection = $this->getLargeThumbnail(self::VIDEO_TYPE);
        $month = $this->requestInterface->getParam('month');
        $year = $this->requestInterface->getParam('year');
        if (isset($month) && isset($year)) {
            $startDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '31'.' 00:00:00');
        } else {
            $startDate = $this->dateTime->gmtDate('y-m-'.'1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate('y-m-'.'31'.' 00:00:00');
        }
        $collection ->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        return $collection;
    }

    /**
     * This function will return video banner thumbnail
     * @return array
     */
    public function getBannerThumbnail()
    {
        $month = $this->requestInterface->getParam('month');
        $year = $this->requestInterface->getParam('year');
        if ($month && $year) {
            $startDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '31'.' 00:00:00');
        } else {
            $startDate = $this->dateTime->gmtDate('y-m-'.'1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate('y-m-'.'31'.' 00:00:00');
        }

        $collection = $this->getLargeThumbnail(self::BANNER_TYPE);
        $collection ->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        return $collection;
    }

    public function getMonthFromDate($time)
    {
        $month = $this->dateTime->gmtDate("m", $time);
        $year = $this->dateTime->gmtDate("y", $time);
        $date = $this->dateTime->gmtDate('Y-m-d H:i:s', $year . '-' . $month . '-' . 1 . ' 00:00:00');
        return $date;
    }

    /**
     * @return CollectionFactory
     */
    public function getMagazineTotalCollection()
    {
        try {
            $month = $this->requestInterface->getParam('month');
            $year = $this->requestInterface->getParam('year');
            if (isset($month) && isset($year)) {
                $endDate = $this->dateTime->gmtDate($year . '-' . $month . '-' . 1 . ' 00:00:00');
            } else {
                $endDate = $this->dateTime->gmtDate('y-m-'.'1'.' 00:00:00');
            }
            $storeId =  $this->storeManagerInterface->getStore()->getId();
            $magazineCollection = $this->collectionFactory->create();
            $magazineCollection->addFieldToFilter(
                ['store_id','store_id','store_id','store_id'],
                [["like" => '%' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . ',%'],
                    ["like" => '%,' . $storeId . '%'],
                    ["in" => ['0', $storeId]]]
            )->setOrder(
                "show_date",
                'DESC'
            )->addFieldToFilter(
                'show_date',
                ['lteq' => $endDate]
            );
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $magazineCollection;
    }

    /**
     * get monthly deatils url
     * @param $date
     * @return string
     */
    public function getMonthlyMagazineUrl($date)
    {
        $month = $this->dateTime->gmtDate("m", $date);
        $year = $this->dateTime->gmtDate("y", $date);
        $date = self::MONTHLY_DETAILS_URL . $month . '/year/' . $year;
        return $date;
    }

    /**
     * @return CollectionFactory
     */
    public function getMonthWiseMagazineCollection()
    {
        $storeId =  $this->storeManagerInterface->getStore()->getId();
        $magazineCollection = $this->collectionFactory->create();
        $month = $this->requestInterface->getParam('month');
        $year = $this->requestInterface->getParam('year');
        $startDate = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $year . '-' . $month . '-' . 1 . ' 00:00:00'
        );
        $endDate = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $year . '-' . $month . '-' . 31 . ' 00:00:00'
        );
        $magazineCollection->addFieldToFilter(
            ['store_id','store_id','store_id','store_id'],
            [["like" => '%' . $storeId . ',%'],
                ["like" => '%,' . $storeId . ',%'],
                ["like" => '%,' . $storeId . '%'],
                ["in" => ['0', $storeId]]]
        )->setOrder(
            "show_date",
            'DESC'
        )->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );

        return $magazineCollection;
    }

    /**
     * @return CollectionFactory
     */
    public function getSliderThumnai($date)
    {

        $collectionByStore = $this->getMagazineCollection(self::BANNER_TYPE);
        $month = $this->dateTime->gmtDate('m', $date);
        $year = $this->dateTime->gmtDate('y', $date);

        if (isset($month) &&  isset($year)) {
            $startDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '1'.' 00:00:00');
            $endDate = $this->dateTime->gmtDate($year.'-'.$month.'-' . '31'.' 00:00:00');
        } else {
            $startDate = $this->dateTime->gmtDate('y-m-'.'1'.' 00:00:00');
            $endDate = $date;
        }
        $startDate = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $year . '-' . $month . '-' . 1 . ' 00:00:00'
        );
        $collectionByStore->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        if (!empty($collectionByStore->getData())) {
            $collectionByStore->setPageSize('1')->load();
            return $collectionByStore;
        }
        $collectionByStore = $this->getMagazineCollection(self::IMAGE_TYPE);
        $collectionByStore->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        if (!empty($collectionByStore->getData())) {
            $collectionByStore->setPageSize('1')->load();
            return $collectionByStore;
        }
        $collectionByStore = $this->getMagazineCollection(self::VIDEO_TYPE);
        $collectionByStore->addFieldToFilter(
            'show_date',
            ['gteq' => $startDate]
        )->addFieldToFilter(
            'show_date',
            ['lteq' => $endDate]
        );
        if (!empty($collectionByStore->getData())) {
            $collectionByStore->setPageSize('1')->load();
            return $collectionByStore;
        }
    }
}
