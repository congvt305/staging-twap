<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:22 PM
 */
namespace Eguana\CustomerBulletin\Api\Data;

/**
 * Interface class having getter\setter
 *
 * Note Interface
 */
interface NoteInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const NOTE_ID = 'note_id';
    const TICKET_ID = 'ticket_id';
    const USER_ID = 'user_id';
    const USER_TYPE = 'user_type';
    const NOTE_MESSAGE = 'note_message';
    const NOTE_ATTACHMENT = 'note_attachment';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    /**#@-*/

    /**
     * Get NOTE Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set NOTE Id
     *
     * @param int $noteId
     * @return NoteInterface
     */
    public function setId($noteId);

    /**
     * Get Ticket Id
     *
     * @return int|null
     */
    public function getTicketId();

    /**
     * Set Ticket Id
     *
     * @param int $ticketId
     * @return NoteInterface
     */
    public function setTicketId($ticketId);

    /**
     * Get User Id
     *
     * @return int|null
     */
    public function getUserId();

    /**
     * Set User Id
     *
     * @param int $userId
     * @return NoteInterface
     */
    public function setUserId($userId);

    /**
     * Get User Type
     *
     * @return int|null
     */
    public function getUserType();

    /**
     * Set User Type
     *
     * @param int $userType
     * @return NoteInterface
     */
    public function setUserType($userType);

    /**
     * Get Note Message
     *
     * @return string|null
     */
    public function getNoteMessage();

    /**
     * Set Note Message
     *
     * @param string $noteMessage
     * @return NoteInterface
     */
    public function setNoteMessage($noteMessage);

    /**
     * Set Note Attachment
     *
     * @param string $noteAttachment
     * @return NoteInterface
     */
    public function setNoteAttachment($noteAttachment);

    /**
     * Get NOte Attachment
     *
     * @return string|null
     */
    public function getNoteAttachment();

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return NoteInterface
     */
    public function setCreationTime($creationTime);

    /**
     * get created at time
     *
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return NoteInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();
}
