<?php
declare(strict_types=1);

namespace CJ\Sms\Model;

use CJ\Sms\Api\Data\SmsHistoryInterface;
use CJ\Sms\Model\ResourceModel\SmsHistory as SmsHistoryResourceModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SmsHistory extends AbstractExtensibleModel implements IdentityInterface, SmsHistoryInterface
{

    /**
     *
     */
    const CACHE_TAG = 'cj_sms_history';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Constructor to initialize ResourceModel
     */
    public function _construct()
    {
        $this->_init(SmsHistoryResourceModel::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     * @return SmsHistory
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Get telephone
     *
     * @return mixed|string|null
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * Set store id
     *
     * @param $storeId
     * @return SmsHistory
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get store id
     *
     * @return mixed|string|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     *
     *
     * @param $limitNumber
     * @return SmsHistory
     */
    public function setLimitNumber($limitNumber)
    {
        return $this->setData(self::LIMIT_NUMBER, $limitNumber);
    }

    /**
     * @return mixed|string|null
     */
    public function getLimitNumber()
    {
        return $this->getData(self::LIMIT_NUMBER);
    }

    /**
     * @param $createdAt
     * @return SmsHistory
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return mixed|string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
}
