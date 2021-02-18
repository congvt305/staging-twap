<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 3:52 PM
 */
namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Eguana\Redemption\Model\ResourceModel\Redemption as RedemptionResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

/**
 * Redemption Model to Set RedemptionInterface Getter Setter values and initialize Resource model in model
 *
 * Class Redemption
 */
class Redemption extends AbstractModel implements RedemptionInterface, IdentityInterface
{
    /**
     * Redemption cache tag
     */
    const CACHE_TAG = 'eguana_redemption';

    /**#@+
     * Redemption's Statuses
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
        $this->_init(RedemptionResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Redemption Id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::REDEMPTION_ID);
    }

    /**
     * Set Redemption Id
     *
     * @param int $redemptionId
     * @return Redemption
     */
    public function setId($redemptionId)
    {
        return $this->setData(self::REDEMPTION_ID, $redemptionId);
    }

    /**
     * Get Redemption Title
     *
     * @return string|null
     */
    public function getTitle() : string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Redemption Title
     *
     * @param string $title
     * @return Redemption
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get Redemption Description
     *
     * @return string|null
     */
    public function getDescription() : string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set Redemption Description
     *
     * @param string $description
     * @return Redemption
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get Precautions
     *
     * @return string|null
     */
    public function getPrecautions() : string
    {
        return $this->getData(self::PRECAUTIONS);
    }

    /**
     * Set Precautions
     *
     * @param string $precautions
     * @return Redemption
     */
    public function setPrecautions($precautions)
    {
        return $this->setData(self::PRECAUTIONS, $precautions);
    }

    /**
     * Get Total Quantity
     *
     * @return string
     */
    public function getTotalQty() : string
    {
        return $this->getData(self::TOTAL_QTY);
    }

    /**
     * Set Total Quantity
     *
     * @param int $totalQty
     * @return Redemption
     */
    public function setTotalQty($totalQty)
    {
        return $this->setData(self::TOTAL_QTY, $totalQty);
    }

    /**
     * Get Cms Block
     *
     * @return string
     */
    public function getCmsBlock() : string
    {
        return $this->getData(self::CMS_BLOCK);
    }

    /**
     * Set Cms Block
     *
     * @param int $cmsBlock
     * @return Redemption
     */
    public function setCmsBlock($cmsBlock)
    {
        return $this->setData(self::CMS_BLOCK, $cmsBlock);
    }

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate() : string
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return Redemption
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get End Date
     *
     * @return string|null
     */
    public function getEndDate() : string
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return Redemption
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get Url Key
     *
     * @return string
     */
    public function getIdentifier() : string
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Set Url Key
     *
     * @param string $identifier
     * @return Redemption
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Get Meta Title
     *
     * @return string|null
     */
    public function getMetaTitle() : string
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * Set Meta Title
     *
     * @param string $metaTitle
     * @return Redemption
     */
    public function setMetaTitle($metaTitle)
    {
        return $this->setData(self::META_TITLE, $metaTitle);
    }

    /**
     * Get Meta Keyword
     *
     * @return string|null
     */
    public function getMetaKeywords() : string
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Set Meta Keyword
     *
     * @param string $metaKeywords
     * @return Redemption
     */
    public function setMetaKeyowrds($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Get Meta Description
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set Meta Description
     *
     * @param string $metaDescription
     * @return Redemption
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get Image
     *
     * @return string|null
     */
    public function getImage() : string
    {
        return $this->getData(self::IMAGE);
    }

    /**
     * Set Image
     *
     * @param string $image
     * @return Redemption
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function isActive() : string
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * Set Status
     *
     * @param int|bool $isActiver
     * @return Redemption
     */
    public function setIsActive($isActiver)
    {
        return $this->setData(self::IS_ACTIVE, $isActiver);
    }

    /**
     * Get Thank You Image
     *
     * @return string|null
     */
    public function getThankYouImage() : string
    {
        return $this->getData(self::THANK_YOU_IMAGE);
    }

    /**
     * Set Thank You Image
     *
     * @param string $image
     * @return Redemption
     */
    public function setThankYouImage($image)
    {
        return $this->setData(self::THANK_YOU_IMAGE, $image);
    }

    /**
     * Retrieve Store Ids
     *
     * @return mixed|null
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare Block's Statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Check if page identifier exist for specific store return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $result = '';
        try {
            $result = $this->_getResource()->checkIdentifier($identifier, $storeId);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $result;
    }
}
