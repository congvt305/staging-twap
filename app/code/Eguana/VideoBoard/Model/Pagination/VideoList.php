<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/6/20
 * Time: 6:56 PM
 */
namespace Eguana\VideoBoard\Model\Pagination;

use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is used to get next six videos list
 *
 * Class VideoList
 */
class VideoList
{
    /**
     * @var UrlInterface
     */
    private $urlInterface;
    /**
     * @var CollectionFactory
     */
    private $videoBoardCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * Constructor
     * @param CollectionFactory $videoBoardCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlInterface,
        CollectionFactory $videoBoardCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->videoBoardCollectionFactory = $videoBoardCollectionFactory;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * Get List of Videos with ajax
     * @param $noOfClicks
     * @return array
     */
    public function getListofVideos($noOfClicks)
    {
        $lintHtml =[];
        $count = 0;
        $size = $noOfClicks*6;
        $videosResult = $this->videoBoardCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();

        $videosResult->addFieldToFilter(
            "is_active",
            ["eq" => true]
        )->addFieldToFilter(
            ['store_id','store_id','store_id','store_id'],
            [["like" =>  '%' . $storeId . ',%'],
                ["like" =>  '%,' . $storeId . ',%'],
                ["like" =>  '%,' . $storeId . '%'],
                ["eq" => $storeId]]
        );
        $videosResult->setPageSize($size);
        if (!empty($videosResult)) {
            foreach ($videosResult as $key => $point) {
                $lintHtml[$count] = '
                <div style="margin-right: 4%; width: 29%; float: left;" class="video-board-list">
                <ul>
                <li style=" width: 100%;" class="video-board-item">
                <div class="video-board-item-content">
                    <div class="video-board-photo">
                        <div class="video-scale">
                            <a class="video-link" href="'.$this->urlInterface->getUrl().'videoboard/detail/index/id/'.$point['entity_id'].'" >
                                <div class="video-iframe">
                                    <img src="'.$this->urlInterface->getUrl('media').$point['thumbnail_image'].'"
                                         alt="Thumbnail Image" />
                                </div>
                            </a>
                        </div>

                        <div class="video-heading">
                            <a class="action view video-title"
                               href="'.$this->urlInterface->getUrl().'videoboard/detail/index/id/'.$point['entity_id'].'">
                                <p class="video-title">
                                    '.$point['video_title'].'
                                </p>
                            </a>
                            <p class="video-created-date">
                               '.$point['created_at'].'
                            </p>
                        </div>
                    </div>
                    </div>
                    </li>
                    </ul>' . "\n";
                $count++;
            }
        }
        return $lintHtml;
    }
}
