<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 3:15 PM
 */
namespace Eguana\NewsBoard\Api\Data;

/**
 * Interface class having getter\setter
 * interface NewsInterface
 */
interface NewsInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const NEWS_ID = 'news_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const CATEGORY = 'category';
    const THUMBNAIL_IMAGE = 'thumbnail_image';
    const IS_ACTIVE = 'is_active';
    const DATE = 'date';
    const IDENTIFIER = 'identifier';
    const META_TITLE = 'meta_title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    /**#@-*/

    /**
     * Set Id
     *
     * @param int $news_id
     * @return NewsInterface
     */
    public function setId($news_id);

    /**
     * Get Id
     *
     * @return int
     */
    public function getId();

    /**
     * set title
     *
     * @param string $title
     * @return NewsInterface
     */
    public function setTitle($title);

    /**
     * get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * set description
     *
     * @param string $description
     * @return NewsInterface
     */
    public function setDescription($description);

    /**
     * get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set Thumbnail Image
     *
     * @param string $thumbnailImage
     * @return NewsInterface
     */

    public function setThumbnailImage($thumbnailImage);

    /**
     * Get Thumbnail Image
     *
     * @return string
     */
    public function getThumbnailImage();

    /**
     * set date
     *
     * @param string $date
     * @return NewsInterface
     */
    public function setDate($date);

    /**
     * get date
     *
     * @return string
     */
    public function getDate();

    /**
     * Get Identifier
     *
     * @return string
     */
    public function getIdentifier() : string;

    /**
     * Set Identifier
     *
     * @param string $identifier
     * @return NewsInterface
     */
    public function setIdentifier($identifier);

    /**
     * Get Meta Title
     *
     * @return string|null
     */
    public function getMetaTitle() : string;

    /**
     * Set Meta Title
     *
     * @param string $metaTitle
     * @return NewsInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * Get Meta Keyword
     *
     * @return string|null
     */
    public function getMetaKeywords() : string;

    /**
     * Set Meta Keywords
     *
     * @param string $metaKeywords
     * @return NewsInterface
     */
    public function setMetaKeyowrds($metaKeywords);

    /**
     * Get Meta Description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Set Meta Description
     *
     * @param string $metaDescription
     * @return NewsInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Set Category
     *
     * @param string $category
     * @return NewsInterface
     */
    public function setCategory($category);

    /**
     * Get Category
     *
     * @return string|null
     */
    public function getCategory();

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return NewsInterface
     */
    public function setCreationTime($creationTime);

    /**
     * get creation time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return NewsInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();

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
