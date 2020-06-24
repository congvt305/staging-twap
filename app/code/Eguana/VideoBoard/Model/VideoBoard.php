<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 9/6/20
 * Time: 8:04 PM
 */

namespace Eguana\VideoBoard\Model;

use Magento\Framework\App\ObjectManager as ObjectManagerAlias;
use Magento\Framework\Model\AbstractModel;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard as VideoBoardResourceModel;
use Eguana\VideoBoard\Api\Data\VideoBoardInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;

/**
 * This model class is used for the Curd operation of video board
 *
 * Class VideoBoard
 */
class VideoBoard extends AbstractExtensibleModel implements VideoBoardInterface, IdentityInterface
{
    /**
     * @var CACHE_TAG
     */
    const CACHE_TAG = 'videoBoard';

    /**
     * @var STATUS_ENABLED
     */
    const STATUS_ENABLED = 1;

    /**
     * @var STATUS_DISABLED
     */
    const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    private $cacheTag = self::CACHE_TAG;

    /**
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * Empty construtor
     */

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(VideoBoardResourceModel::class);
        $objectManager = ObjectManagerAlias::getInstance();
        /* Get store manager */
        $this->storeManager = $objectManager
            ->get(StoreManagerInterfaceAlias::class);
    }

    /**
     * Get custom attributes codes
     *
     * @return array|string[]
     */
    public function getCustomAttributesCodes()
    {
        return [
            'entity_id',
            'video_title',
            'video_url',
            'description',
            'thumbnail_image',
            'store_id',
            'is_active',
            'created_at',
            'updated_at',
        ];
    }
    /**
     * get identities
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id)
    {
        return $this->setData(self::ENTITY_ID, $entity_id);
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return VideoBoardInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        return $this->setData(self::URL, $url);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string $thumbnailImage
     * @return $this
     */
    public function setThumbnailImage($thumbnailImage)
    {
        return $this->setData(self::THUMBNAIL_IMAGE, $thumbnailImage);
    }

    /**
     * @return string
     */
    public function getThumbnailImage()
    {
        return $this->getData(self::THUMBNAIL_IMAGE);
    }

    /**
     * It will return the thumbanil image URL
     *
     * @return string
     */
    public function getThumbnailImageURL()
    {
        if ($this->getThumbnailImage() == '') {
            return '';
        }
        return $this->getMediaUrl($this->getThumbnailImage());
    }

    /**
     * Get file url
     *
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl($file)
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT);
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return string
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
