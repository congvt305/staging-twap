<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 10:50 AM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Model\ResourceModel\Event as EventResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * This model class is used for the Curd operation of Events
 *
 * Class Event
 */
class Event extends AbstractModel implements EventInterface, IdentityInterface
{
    /**#@+
     * Event cache tag
     */
    const CACHE_TAG = 'event-reservation';
    /**#@-*/

    /**#@+
     * Event's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(EventResource::class);
    }

    /**
     * Receive event store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Check if event identifier exist for specific store return event id if event exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Prepare event's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses() : array
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities() : array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get thumbnail
     *
     * @return string
     */
    public function getThumbnail()
    {
        return $this->getData(self::THUMBNAIL);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get block ID
     *
     * @return int|null
     */
    public function getBlockId()
    {
        return $this->getData(self::BLOCK_ID);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Get available slots
     *
     * @return int
     */
    public function getAvailableSlots()
    {
        return $this->getData(self::AVAILABLE_SLOTS);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get Success Image
     *
     * @return array|mixed|string|null
     */
    public function getSuccessImage()
    {
        return $this->getData(self::SUCCESS_IMAGE);
    }

    /**
     * Get sms content
     *
     * @return string|null
     */
    public function getSmsContent()
    {
        return $this->getData(self::SMS_CONTENT);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return EventInterface|Event
     */
    public function setId($id)
    {
        return $this->setData(self::EVENT_ID, $id);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return EventInterface|Event
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return EventInterface|Event
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return EventInterface|Event
     */
    public function setThumbnail($thumbnail)
    {
        return $this->setData(self::THUMBNAIL, $thumbnail);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return EventInterface|Event
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return EventInterface|Event
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set block ID
     *
     * @param int $blockId
     * @return EventInterface|Event
     */
    public function setBlockId($blockId)
    {
        return $this->setData(self::BLOCK_ID, $blockId);
    }

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return EventInterface|Event
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return EventInterface|Event
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return EventInterface|Event
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Set available slots
     *
     * @param int $availableSlots
     * @return EventInterface|Event
     */
    public function setAvailableSlots($availableSlots)
    {
        return $this->setData(self::AVAILABLE_SLOTS, $availableSlots);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return EventInterface|Event
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return EventInterface|Event
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set Success Image
     *
     * @param string $image
     * @return Event
     */
    public function setSuccessImage($image)
    {
        return $this->setData(self::SUCCESS_IMAGE, $image);
    }

    /**
     * Set sms content
     *
     * @param string $content
     * @return Event
     */
    public function setSmsContent($content)
    {
        return $this->setData(self::SMS_CONTENT, $content);
    }

    /**
     * Before Save method
     *
     * @return Event
     */
    public function beforeSave()
    {
        if ($this->hasDataChanges()) {
            $this->setUpdateTime(null);
        }

        return parent::beforeSave();
    }
}
