<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 27/10/20
 * Time: 1:05 AM
 */
namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\Data\CounterInterface;
use Eguana\Redemption\Model\ResourceModel\Counter as CounterResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Counter Model to Set CounterInterface Getter Setter values and initialize Resource model in model
 *
 * Class Redemption
 */
class Counter extends AbstractModel implements CounterInterface, IdentityInterface
{
    /**
     * Redemption cache tag
     */
    const CACHE_TAG = 'eguana_redemption';

    /**#@+
     * Counter's Statuses
     */
    const STATUS_REGISTRATION   = 1;
    const STATUS_REDEMPTION     = 2;
    const STATUS_EXPIRED        = 3;
    const STATUS_ENABLED        = 1;
    const STATUS_DISABLED       = 0;
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
        $this->_init(CounterResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId(), self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get Entity Id
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param int $entityId
     * @return Counter
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get Redemption Id
     *
     * @return string|null
     */
    public function getRedemptionId()
    {
        return $this->getData(self::REDEMPTION_ID);
    }

    /**
     * Set Redemption Id
     *
     * @param string $redemptionId
     * @return Counter
     */
    public function setRedemptionId($redemptionId)
    {
        return $this->setData(self::REDEMPTION_ID, $redemptionId);
    }

    /**
     * Get Redeem Date
     *
     * @return string|null
     */
    public function getRedeemDate() : string
    {
        return $this->getData(self::REDEEM_DATE);
    }

    /**
     * Set Redeem Date
     *
     * @param string $redeemDate
     * @return Counter
     */
    public function setRedeemDate($redeemDate)
    {
        return $this->setData(self::REDEEM_DATE, $redeemDate);
    }

    /**
     * Get Customer Name
     *
     * @return string|null
     */
    public function getCustomerName() : string
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    /**
     * Set Customer Name
     *
     * @param string $customerName
     * @return Counter
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail() : string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set Email
     *
     * @param string $email
     * @return Counter
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get Telephone
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * Set Telephone
     *
     * @param string $telephone
     * @return Counter
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Get Counter Id
     *
     * @return string|null
     */
    public function getCounterId() : string
    {
        return $this->getData(self::COUNTER_ID);
    }

    /**
     * Set Counter Id
     *
     * @param string $counterId
     * @return Counter
     */
    public function setCounterId($counterId)
    {
        return $this->setData(self::COUNTER_ID, $counterId);
    }

    /**
     * Get Line Id
     *
     * @return string|null
     */
    public function getLineId()
    {
        return $this->getData(self::LINE_ID);
    }

    /**
     * Set Line Id
     *
     * @param string $lineId
     * @return Counter
     */
    public function setLineId($lineId)
    {
        return $this->setData(self::LINE_ID, $lineId);
    }

    /**
     * Get Registration Date
     *
     * @return string
     */
    public function getRegistrationDate() : string
    {
        return $this->getData(self::REGISTRATION_DATE);
    }

    /**
     * Set Registration Date
     *
     * @param string $registrationDate
     * @return Counter
     */
    public function setRegistrationDate($registrationDate)
    {
        return $this->setData(self::REGISTRATION_DATE, $registrationDate);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus() : string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int|bool $status
     * @return Counter
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * Set Token
     *
     * @param $token
     * @return Counter
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get UTM source
     *
     * @return string|null
     */
    public function getUtmSource()
    {
        return $this->getData(self::UTM_SOURCE);
    }

    /**
     * Set UTM source
     *
     * @param string $utmSource
     * @return Counter
     */
    public function setUtmSource($utmSource)
    {
        return $this->setData(self::UTM_SOURCE, $utmSource);
    }

    /**
     * Get UTM Medium
     *
     * @return string|null
     */
    public function getUtmMedium()
    {
        return $this->getData(self::UTM_MEDIUM);
    }

    /**
     * Set UTM Medium
     *
     * @param string $utmMedium
     * @return Counter
     */
    public function setUtmMedium($utmMedium)
    {
        return $this->setData(self::UTM_MEDIUM, $utmMedium);
    }

    /**
     * Get UTM Content
     *
     * @return string|null
     */
    public function getUtmContent()
    {
        return $this->getData(self::UTM_CONTENT);
    }

    /**
     * Set UTM Content
     *
     * @param string $utmContent
     * @return Counter
     */
    public function setUtmContent($utmContent)
    {
        return $this->setData(self::UTM_CONTENT, $utmContent);
    }

    /**
     * Get Creation Time
     *
     * @return string|null
     */
    public function getCreationTime() : string
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Set Creation Time
     *
     * @param string $creationTime
     * @return Counter
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Get Update Time
     *
     * @return string|null
     */
    public function getUpdateTime() : string
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set Update Time
     *
     * @param string $updateTime
     * @return Counter
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Prepare Counter email's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_REGISTRATION    => __('Registration'),
            self::STATUS_REDEMPTION   => __('Redemption'),
            self::STATUS_EXPIRED   => __('Expired')
        ];
    }
}
