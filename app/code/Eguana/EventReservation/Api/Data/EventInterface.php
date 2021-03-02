<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 14/10/20
 * Time: 01:00 PM
 */
namespace Eguana\EventReservation\Api\Data;

/**
 * Interface class having getter\setter
 *
 * Event Reservation Interface
 */
interface EventInterface
{
    /**#@+
     * Constants for keys of data array.
     */
    const EVENT_ID                  = 'event_id';
    const TITLE                     = 'title';
    const THUMBNAIL                 = 'thumbnail';
    const IS_ACTIVE                 = 'is_active';
    const DESCRIPTION               = 'description';
    const BLOCK_ID                  = 'block_id';
    const IDENTIFIER                = 'identifier';
    const META_TITLE                = 'meta_title';
    const META_KEYWORDS             = 'meta_keywords';
    const META_DESCRIPTION          = 'meta_description';
    const AVAILABLE_SLOTS           = 'available_slots';
    const CREATION_TIME             = 'creation_time';
    const UPDATE_TIME               = 'update_time';
    const SUCCESS_IMAGE             = 'success_image';
    /**#@-*/

    /**
     * Get event ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get thumbnail
     *
     * @return string|null
     */
    public function getThumbnail();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get block ID
     *
     * @return int|null
     */
    public function getBlockId();

    /**
     * Get identifier
     *
     * @return string|null
     */
    public function getIdentifier();

    /**
     * Get meta title
     *
     * @return string|null
     */
    public function getMetaTitle();

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Get available slots
     *
     * @return int|null
     */
    public function getAvailableSlots();

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Get Success Image
     *
     * @return string|null
     */
    public function getSuccessImage();

    /**
     * Set event ID
     *
     * @param int $id
     * @return EventInterface
     */
    public function setId($id);

    /**
     * Set title
     *
     * @param string $title
     * @return EventInterface
     */
    public function setTitle($title);

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return EventInterface
     */
    public function setIsActive($isActive);

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     * @return EventInterface
     */
    public function setThumbnail($thumbnail);

    /**
     * Set description
     *
     * @param string $description
     * @return EventInterface
     */
    public function setDescription($description);

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return EventInterface
     */
    public function setIdentifier($identifier);

    /**
     * Set block ID
     *
     * @param int $blockId
     * @return EventInterface
     */
    public function setBlockId($blockId);

    /**
     * Set meta title
     *
     * @param string $metaTitle
     * @return EventInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return EventInterface
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return EventInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Set available slots
     *
     * @param int $availableSlots
     * @return EventInterface
     */
    public function setAvailableSlots($availableSlots);

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return EventInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return EventInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Set Success Image
     *
     * @param string $image
     * @return EventInterface
     */
    public function setSuccessImage($image);
}
