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
 * interface TicketInterface
 */
interface TicketInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TICKET_ID = 'ticket_id';
    const CUSTOMER_ID = 'customer_id';
    const MESSAGE = 'message';
    const EMERGENCY = 'emergency';
    const ACCOUNT = 'account';
    const SUBJECT = 'subject';
    const STATUS = 'status';
    const CATEGORY = 'category';
    const STORE_ID = 'store_id';
    const ATTACHMENT = 'attachment';
    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    /**#@-*/

    /**
     * Get Ticket Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Ticket Id
     *
     * @param int $ticket_id
     * @return TicketInterface
     */
    public function setId($ticket_id);

    /**
     * Get Customer Id
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set Customer Id
     *
     * @param int $customer_id
     * @return TicketInterface
     */
    public function setCustomerId($customer_id);

    /**
     * Get Store View
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set Store View
     *
     * @param int $StoreId
     * @return TicketInterface
     */
    public function setStoreId($StoreId);

    /**
     * Set Account
     *
     * @param string $account
     * @return TicketInterface
     */
    public function setAccount($account);

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMassege();

    /**
     * Set Message
     *
     * @param string $message
     * @return TicketInterface
     */
    public function setMassage($message);

    /**
     * Get Account
     *
     * @return string|null
     */
    public function getAccount();

    /**
     * Set Subject
     *
     * @param string $subject
     * @return TicketInterface
     */
    public function setSubject($subject);

    /**
     * Get Subject
     *
     * @return string|null
     */
    public function getSubject();

    /**
     * Set Category
     *
     * @param string $category
     * @return TicketInterface
     */
    public function setCategory($category);

    /**
     * Get Category
     *
     * @return string|null
     */
    public function getCategory();

    /**
     * Set Attachment
     *
     * @param string $attachment
     * @return TicketInterface
     */
    public function setAttachment($attachment);

    /**
     * Get Category
     *
     * @return string|null
     */
    public function getAttachment();

    /**
     * Set Status
     *
     * @param int $status
     * @return TicketInterface
     */
    public function setStatus($status);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return TicketInterface
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
     * @return TicketInterface
     */
    public function setUpdateTime($updateTime);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime();
}
