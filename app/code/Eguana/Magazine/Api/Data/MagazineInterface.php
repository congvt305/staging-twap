<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 1:38 AM
 */
namespace Eguana\Magazine\Api\Data;

/**
 * interface MagazineInterface
 * @api
 */
interface MagazineInterface
{
    const ENTITY_ID = 'entity_id';
    const TITLE = 'title';
    const SHORT_DESCRIPTION = 'short_description';
    const CONTENT = 'content';
    const TYPE = 'type';
    const THUMBNAIL_IMAGE = 'thumbnail_image';
    const THUMBNAIL_ALT = 'thumbnail_alt';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const IS_ACTIVE = 'is_active';
    const SHOW_DATE = 'show_date';
    const SORT_ORDER = 'sort_order';

    /**
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id);

    /**
     * @return mixed
     */
    public function getEntityId();

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $shortDescription
     * @return $this
     */
    public function setShortDescription($shortDescription);

    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getContent();

    /**
     * Set Thumbnail Image
     * @param string $thumbnailAlt
     * @return MagazineInterface
     */
    public function setThumbnailAlt($thumbnailAlt);

    /**
     * Get Thumbnail Image
     * @return string
     */
    public function getThumbnailAlt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getIsActive();

    /**
     * @param $type
     * @return mixed
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param $showDate
     * @return mixed
     */
    public function setShowDate($showDate);

    /**
     * @return string
     */
    public function getShowDate();
}
