<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 11:29 AM
 */
namespace Eguana\VideoBoard\Api\Data;

/**
 * interface VideoBoardInterface
 * @api
 */
interface VideoBoardInterface
{
    const ENTITY_ID = 'entity_id';

    const TITLE = 'video_title';

    const URL = 'video_url';

    const THUMBNAIL_IMAGE = 'thumbnail_image';

    const DESCRIPTION = 'description';

    const STORE_ID = 'store_id';

    const IS_ACTIVE = 'is_active';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * Get Store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return VideoBoardInterface
     */
    public function setStoreId($storeId);

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
     * @param string $url
     * @return $this
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * Set Thumbnail Image
     *
     * @param string $thumbnailImage
     * @return VideoBoardInterface
     */
    public function setThumbnailImage($thumbnailImage);

    /**
     * Get Thumbnail Image
     *
     * @return string
     */
    public function getThumbnailImage();

    /**
     * Get Thumbnail Image
     *
     * @return string
     */
    public function getThumbnailImageURL();

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
}
