<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 1:50 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Api\Data;

/**
 * Interface RedemptionInterface
 * @api
 */
interface RedemptionInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const REDEMPTION_ID = 'redemption_id';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const PRECAUTIONS = 'precautions';
    const TOTAL_QTY = 'total_qty';
    const CMS_BLOCK = 'cms_block';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const IDENTIFIER = 'identifier';
    const META_TITLE = 'meta_title';
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const IMAGE = 'image';
    const IS_ACTIVE = 'is_active';
    const THANK_YOU_IMAGE = 'thank_you_image';
    /**#@-*/

    /**
     * Get Redemption Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Redemption Id
     *
     * @param int $redemptionId
     * @return RedemptionInterface
     */
    public function setId($redemptionId);

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle() : string;

    /**
     * Set Title
     *
     * @param string $title
     * @return RedemptionInterface
     */
    public function setTitle($title);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription() : string;

    /**
     * Set Description
     *
     * @param string $description
     * @return RedemptionInterface
     */
    public function setDescription($description);

    /**
     * Get Precautions
     *
     * @return mixed|null
     */
    public function getPrecautions() : string;

    /**
     * Set Precautions
     *
     * @param string $precautions
     * @return RedemptionInterface
     */
    public function setPrecautions($precautions);

    /**
     * Get Total Quantity
     *
     * @return string
     */
    public function getTotalQty() : string;

    /**
     * Set Total Quantity
     *
     * @param int $totalQty
     * @return RedemptionInterface
     */
    public function setTotalQty($totalQty);

    /**
     * Get Cms Block
     *
     * @return string
     */
    public function getCmsBlock() : string;

    /**
     * Set Cms Block
     *
     * @param int $cmsBlock
     * @return RedemptionInterface
     */
    public function setCmsBlock($cmsBlock);

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate() : string;

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return RedemptionInterface
     */
    public function setStartDate($startDate);

    /**
     * Get End Date
     *
     * @return string|null
     */
    public function getEndDate() : string;

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return RedemptionInterface
     */
    public function setEndDate($endDate);

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
     * @return RedemptionInterface
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
     * @return RedemptionInterface
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
     * @return RedemptionInterface
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
     * @return RedemptionInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get Image
     *
     * @return string|null
     */
    public function getImage() : string;

    /**
     * Set Image
     *
     * @param string $image
     * @return RedemptionInterface
     */
    public function setImage($image);

    /**
     * Get Is Active
     *
     * @return string
     */
    public function isActive() : string;

    /**
     * Set Is Active
     *
     * @param int|bool $isActive
     * @return RedemptionInterface
     */
    public function setIsActive($isActive);

    /**
     * Get Thank You Image
     *
     * @return string|null
     */
    public function getThankYouImage() : string;

    /**
     * Set Thank You Image
     *
     * @param string $image
     * @return RedemptionInterface
     */
    public function setThankYouImage($image);
}
