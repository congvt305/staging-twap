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

    const STORE_ID = 'store_id';

    const TITLE = 'title';

    const CONTENT = 'content';

    const DESCRIPTION = 'description';

    const TYPE = 'type';

    const THUMBNAIL_IMAGE = 'thumbnail_image';

    const THUMBNAIL_ALT = 'thumbnail_alt';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const IS_ACTIVE = 'is_active';

//    const COMING_SOON = 'coming_soon';
//
//    const CITY = 'city';
//
//    const COUNTRY_ID = 'country_id';
//
//    const URL_KEY = 'urlkey';

    const SORT_ORDER = 'sort_order';

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
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * Get Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set Store ID
     *
     * @param int $storeId
     * @return MagazineInterface
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
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getContent();

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
     * @return MagazineInterface
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
     * Set Thumbnail Image
     *
     * @param string $thumbnailAlt
     * @return MagazineInterface
     */
    public function setThumbnailAlt($thumbnailAlt);

    /**
     * Get Thumbnail Image
     *
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

    public function setType($type);

    /**
     * @return string
     */
    public function getType();

//    /**
//     * @param string $comingSoon
//     * @return $this
//     */
//    public function setComingSoon($comingSoon);
//
//    /**
//     * @return string
//     */
//    public function getComingSoon();
//
//    /**
//     * @param string $city
//     * @return $this
//     */
//    public function setCity($city);
//
//    /**
//     * @return string
//     */
//    public function getCity();
//
//    /**
//     * @param string $countryId
//     * @return $this
//     */
//    public function setCountryId($countryId);
//
//    /**
//     * @return string
//     */
//    public function getCountryId();
//
//    /**
//     * @param string $urlKey
//     * @return $this
//     */
//    public function setUrlKey($urlKey);
//
//    /**
//     * @return string
//     */
//    public function getUrlKey();
}
