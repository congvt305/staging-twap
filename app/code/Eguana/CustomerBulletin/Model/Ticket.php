<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Model;

use Eguana\CustomerBulletin\Api\Data\TicketInterface;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket as ResourceModel;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Ticket model class having getter setter
 */
class Ticket extends AbstractExtensibleModel implements TicketInterface
{
    /**
     * @var CACHE_TAG
     */
    const CACHE_TAG = 'eguana_ticket';

    /**#@+
     * ticket's statuses
     */
    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;
    const STATUS_HOLD = 2;
    /**#@-*/

    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve Ticket id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(TicketInterface::TICKET_ID);
    }

    /**
     * Retrieve Customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(TicketInterface::CUSTOMER_ID);
    }

    /**
     * Retrieve ticket account
     *
     * @return string
     */
    public function getAccount()
    {
        return $this->getData(TicketInterface::ACCOUNT);
    }

    /**
     * Retrieve ticket Subject
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->getData(TicketInterface::SUBJECT);
    }

    /**
     * Retrieve ticket Stuatus
     *
     * @return int|null
     */
    public function getStatus()
    {
        return (bool)$this->getData(TicketInterface::STATUS);
    }

    /**
     * Retrieve ticket Category
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->getData(TicketInterface::CATEGORY);
    }

    /**
     * Retrieve ticket Creation Time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(TicketInterface::CREATION_TIME);
    }

    /**
     * Retrieve ticket Update Time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(TicketInterface::UPDATE_TIME);
    }

    /**
     * Retrieve ticket Attachment
     *
     * @return mixed|string|null
     */
    public function getAttachment()
    {
        return $this->getData(TicketInterface::ATTACHMENT);
    }
    /**
     * Retrieve ticket Message
     *
     * @return mixed|string|null
     */
    public function getMassege()
    {
        return $this->getData(TicketInterface::MESSAGE);
    }
    /**
     * Retrieve ticket Store View
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData(TicketInterface::STORE_ID);
    }

    /**
     * Set Ticket ID
     *
     * @param int $ticket_id
     * @return TicketInterface
     */
    public function setId($ticket_id)
    {
        return $this->setData(TicketInterface::TICKET_ID, $ticket_id);
    }

    /**
     * Set Customer ID
     *
     * @param int $customer_id
     * @return TicketInterface
     */
    public function setCustomerId($customer_id)
    {
        return $this->setData(TicketInterface::CUSTOMER_ID, $customer_id);
    }

    /**
     * Set Account
     *
     * @param $account
     * @return TicketInterface
     */
    public function setAccount($account)
    {
        return $this->setData(TicketInterface::ACCOUNT, $account);
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return TicketInterface
     */
    public function setSubject($subject)
    {
        return $this->setData(TicketInterface::SUBJECT, $subject);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return TicketInterface
     */
    public function setStatus($status)
    {
        return $this->setData(TicketInterface::STATUS, $status);
    }

    /**
     * Set Category
     *
     * @param string $category
     * @return TicketInterface
     */
    public function setCategory($category)
    {
        return $this->setData(TicketInterface::CATEGORY, $category);
    }

    /**
     * Set store View
     *
     * @param int $StoreId
     * @return TicketInterface
     */
    public function setStoreId($StoreId)
    {
        return $this->setData(TicketInterface::STORE_ID, $StoreId);
    }

    /**
     * Set Attechment
     *
     * @param string $attachment
     * @return TicketInterface
     */
    public function setAttachment($attachment)
    {
        return $this->setData(TicketInterface::ATTACHMENT, $attachment);
    }

    /**
     * Set Message
     *
     * @param string $message
     * @return TicketInterface
     */
    public function setMassage($message)
    {
        return $this->setData(TicketInterface::MESSAGE, $message);
    }

    /**
     * set creation datetime
     *
     * @param string $creation_Time
     * @return TicketInterface
     */
    public function setCreationTime($creation_Time)
    {
        return $this->setData(TicketInterface::CREATION_TIME, $creation_Time);
    }

    /**
     * set updation time
     *
     * @param string $update_Time
     * @return TicketInterface
     */
    public function setUpdateTime($update_Time)
    {
        return $this->getData(TicketInterface::UPDATE_TIME, $update_Time);
    }

    /**
     * Prepare Ticket's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_OPEN => __('Open'), self::STATUS_HOLD => __('Hold'), self::STATUS_CLOSE => __('Clos')];
    }
}
