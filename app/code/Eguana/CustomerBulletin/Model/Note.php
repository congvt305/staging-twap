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

use Eguana\CustomerBulletin\Api\Data\NoteInterface;
use Eguana\CustomerBulletin\Model\ResourceModel\Note as ResourceModel;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Note model class having getter setter
 */
class Note extends AbstractExtensibleModel implements NoteInterface
{
    /**
     * @var CACHE_TAG
     */
    const CACHE_TAG = 'eguana_ticket';

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
     * Retrieve Note id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(NoteInterface::NOTE_ID);
    }

    /**
     * Retrieve Ticket ID
     *
     * @return int|null
     */
    public function getTicketId()
    {
        return (bool)$this->getData(NoteInterface::TICKET_ID);
    }

    /**
     * Retrieve User ID
     *
     * @return int|null
     */
    public function getUserId()
    {
        return (bool)$this->getData(NoteInterface::USER_ID);
    }

    /**
     * Retrieve User Type
     *
     * @return int|null
     */
    public function getUserType()
    {
        return (bool)$this->getData(NoteInterface::USER_TYPE);
    }

    /**
     * Retrieve Note Creation Time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(NoteInterface::CREATION_TIME);
    }

    /**
     * Retrieve Note Update Time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(NoteInterface::UPDATE_TIME);
    }

    /**
     * Retrieve Note Attachment
     *
     * @return mixed|string|null
     */
    public function getNoteAttachment()
    {
        return $this->getData(NoteInterface::NOTE_ATTACHMENT);
    }

    /**
     * Retrieve Note Message
     *
     * @return mixed|string|null
     */
    public function getNoteMessage()
    {
        return $this->getData(NoteInterface::NOTE_MESSAGE);
    }

    /**
     * Set Note ID
     *
     * @param int $noteId
     * @return NoteInterface
     */
    public function setId($noteId)
    {
        return $this->setData(NoteInterface::NOTE_ID, $noteId);
    }

    /**
     * Set Ticket ID
     *
     * @param int $ticketId
     * @return NoteInterface
     */
    public function setTicketId($ticketId)
    {
        return $this->setData(NoteInterface::TICKET_ID, $ticketId);
    }

    /**
     * Set User ID
     *
     * @param int $userId
     * @return NoteInterface
     */
    public function setUserId($userId)
    {
        return $this->setData(NoteInterface::USER_ID, $userId);
    }

    /**
     * Set User Type
     *
     * @param int $userType
     * @return NoteInterface
     */
    public function setUserType($userType)
    {
        return $this->setData(NoteInterface::USER_TYPE, $userType);
    }

    /**
     * Set Note Message
     *
     * @param $noteMessage
     * @return NoteInterface
     */
    public function setNoteMessage($noteMessage)
    {
        return $this->setData(NoteInterface::NOTE_MESSAGE, $noteMessage);
    }

    /**
     * Set Note Attachment
     *
     * @param $noteAttachment
     * @return NoteInterface
     */
    public function setNoteAttachment($noteAttachment)
    {
        return $this->setData(NoteInterface::NOTE_ATTACHMENT, $noteAttachment);
    }

    /**
     * set creation Date
     *
     * @param string $creationTime
     * @return NoteInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(NoteInterface::CREATION_TIME, $creationTime);
    }

    /**
     * set updation date
     *
     * @param string $updateTime
     * @return NoteInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->getData(NoteInterface::UPDATE_TIME, $updateTime);
    }
}
