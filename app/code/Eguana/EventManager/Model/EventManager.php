<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 3:35 PM
 */
namespace Eguana\EventManager\Model;

use Magento\Framework\Model\AbstractModel;
use Eguana\EventManager\Model\ResourceModel\EventManager as EventManagerResourceModel;
use Eguana\EventManager\Api\Data\EventManagerInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\UrlInterface;
use Magento\Framework\Filesystem;

/**
 * This model class is used for the Curd operation of Events
 *
 * Class EventManager
 */
class EventManager extends AbstractExtensibleModel implements EventManagerInterface
{
    /**
     * @var STATUS_ENABLED
     */
    const STATUS_ENABLED = 1;

    /**
     * @var STATUS_DISABLED
     */
    const STATUS_DISABLED = 0;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(EventManagerResourceModel::class);
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
            'event_title',
            'description',
            'thumbnail_image',
            'is_active',
            'start_date',
            'end_date',
            'created_at',
            'updated_at',
        ];
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
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
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
