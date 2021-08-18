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
    const REDEMPTION_COMPLETION_BLOCK = 'redemption_completion_block';
    const REDEMPTION_COMPLETION_MESSAGE = 'redemption_completion_message';
    const VVIP_LIST = 'vvip_list';
    const FIXED_TEXT_BANNER_INDEX = 'text_banner_index';
    const FIXED_TEXT_BANNER_SUCCESS = 'text_banner_success';
    const BG_COLOR_TEXT_BANNER = 'bg_color_text_banner';
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

    /**
     * Get Redemption Completion Page Block
     *
     * @return string
     */
    public function getRedemptionCompletionBlock();

    /**
     * Set Redemption Completion Block
     *
     * @param int $blockId
     * @return RedemptionInterface
     */
    public function setRedemptionCompletionBlock($blockId);

    /**
     * Get Redemption Completion Page Message
     *
     * @return string
     */
    public function getRedemptionCompletionMessage();

    /**
     * Set Redemption Completion Message
     *
     * @param string $message
     * @return RedemptionInterface
     */
    public function setRedemptionCompletionMessage($message);

    /**
     * @return string
     */
    public function getVvipList();

    /**
     * @param string $list
     * @return $this
     */
    public function setVvipList($list);

    /**
     * @return string
     */
    public function getTextBannerIndex();

    /**
     * @param string $text
     * @return $this
     */
    public function setTextBannerIndex($text);

    /**
     * @return string
     */
    public function getTextBannerSuccess();

    /**
     * @param string $text
     * @return $this
     */
    public function setTextBannerSuccess($text);

    /**
     * @return string
     */
    public function getBgColorTextBanner();

    /**
     * @param string $color
     * @return $this
     */
    public function setBgColorTextBanner($color);
}
