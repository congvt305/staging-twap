<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 *
 * PHP version 7.3
 *
 * @category PHP_FILE
 * @package  Eguana
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */

namespace Amore\CustomerRegistration\Api\Data;

/**
 * Interface CustomerInterface
 *
 * @category PHP
 * @package  Eguana
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */
interface CustomerInterface
{
    const CUSTOMER_ID = 'customer_id';
    const PARTNER_ID = 'partner_id';
    const CUSTOMER_GROUP_CODE = 'customer_group_code';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lasstname';
    const GENDER = 'gender';
    const EMAIL = 'email';
    const DOB = 'dob';
    const CREATED_AT = 'created_at';
    const MOBILE_NUMBER = 'mobile_number';
    const EMAIL_SUBSCRIPTION_STATUS = 'email_subscription_status';
    const SMS_SUBSCRIPTION_STATUS = 'sms_subscription_status';
    const DM_SUBSCRIPTION_STATUS = 'dm_subscription_status';
    const DM_REGION = 'dm_region';
    const DM_DISTRICT = 'dm_district';
    const DM_DETAILED_ADDRESS = 'dm_detailed_address';
    const DM_ZIPCODE = 'dm_zipcode';
    const FAVORITE_STORE = 'favorite_store';
    const REFFERER_CODE = 'referrer_code';

    /**
     * Get the customer id
     * It will get the customer id of the customer who is going to the update
     *
     * @return int | null
     */
    public function getCustomerId();

    /**
     * Get the partner Id
     * It will get the partner Id  of the customer who is going to the update
     *
     * @return string | null
     */
    public function getPartnerId();

    /**
     * Get the customer gorup code
     * It will get the customer gorup code of the customer who is going to the update
     *
     * @return string | null
     */
    public function getCustomerGroupCode();

    /**
     * Get the first name
     * It will get the first name of the customer who is going to the update
     *
     * @return string | null
     */
    public function getFirstname();

    /**
     * Get the last name
     * It will set the last name of the customer who is going to the update
     *
     * @return string | null
     */
    public function getLastname();

    /**
     * Get the gener
     * It will set the gender of the customer who is going to the update
     *
     * @return int | null
     */
    public function getGender();

    /**
     * Get the email
     * It will get the email of the customer who is going to the update
     *
     * @return string | null
     */
    public function getEmail();

    /**
     * Get the DOB
     * It will get the DOB of the customer who is going to the update
     *
     * @return string | null
     */
    public function getDob();

    /**
     * Get the created at
     * It will get the created at of the customer who is going to the update
     *
     * @return string | null
     */
    public function getCreatedAt();

    /**
     * Get the mobile number
     * It will get the mobile number of the customer who is going to the update
     *
     * @return string | null
     */
    public function getMobileNumber();

    /**
     * Get the email subscription status
     * It will get the email subscription status of the customer who
     * is going to the update
     *
     * @return int | null
     */
    public function getEmailSubscriptionStatus();

    /**
     * Get the sms subscription status
     * It will get the sms subscription status of the customer
     * who is going to the update
     *
     * @return int | null
     */
    public function getSmsSubscriptionStatus();

    /**
     * Get the dm subscription status
     * It will get the dm subscription status of the customer
     * who is going to the update
     *
     * @return int | null
     */
    public function getDmSubscriptionStatus();

    /**
     * Get the dm region
     * It will get the dm region of the customer who is going to the update
     *
     * @return string | null
     */
    public function getDmRegion();

    /**
     * Get the dm district
     * It will get the dm district of the customer who is going to the update
     *
     * @return string | null
     */
    public function getDmDistrict();

    /**
     * Get the dm detailed address
     * It will get the dm detailed address of the customer who is going to the update
     *
     * @return string | null
     */
    public function getDmDetailedAddress();

    /**
     * Get the dm zip code
     * It will get the dm zip code of the customer who is going to the update
     *
     * @return int | null
     */
    public function getDmZipcode();

    /**
     * Get the favorite store
     * It will get the favorite store of the customer who is going to the update
     *
     * @return string | null
     */
    public function getFavoriteStore();

    /**
     * Get the refferer code
     * It will get the refferer code of the customer who is going to the update
     *
     * @return string | null
     */
    public function getReffererCode();

    /**
     * Get the locale of customer
     * It will get the locale of the customer who is going to the update
     *
     * @return string | null
     */
    public function getLocale();

    /**
     * Set the customer id
     * It will set the customer id who is going to update
     *
     * @param int $id Customer id
     *
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Set the partner id
     * It will set the partner id who is going to update
     *
     * @param string $partnerId partner id
     *
     * @return $this
     */
    public function setPartnerId($partnerId);

    /**
     * Set the customer group code
     * It will set the customer group code who is going to update
     *
     * @param string $groupCode group code
     *
     * @return $this
     */
    public function setCustomerGroupCode($groupCode);

    /**
     * Set the customer first name
     * It will set the customer first name who is going to update
     *
     * @param string $firstName first name
     *
     * @return $this
     */
    public function setFirstname($firstName);

    /**
     * Set the customer last name
     * It will set the customer last name who is going to update
     *
     * @param string $lastname last name
     *
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Set the customer gender
     * It will set the customer gender who is going to update
     *
     * @param string $gender gender
     *
     * @return $this
     */
    public function setGender($gender);

    /**
     * Set the customer email
     * It will set the customer email id who is going to update
     *
     * @param string $email email
     *
     * @return $this
     */
    public function setEmail($email);

    /**
     * Set the customer dob
     * It will set the customer dob who is going to update
     *
     * @param string $dob date of birth
     *
     * @return $this
     */
    public function setDob($dob);

    /**
     * Set the customer created at
     * It will set the customer created at who is going to update
     *
     * @param string $createdAt created at value to save
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set the mobile number
     * It will set the customer customer mobile number who is going to update
     *
     * @param string $mobileNumber mobile number to save
     *
     * @return $this
     */
    public function setMobileNumber($mobileNumber);

    /**
     * Set the customer email subscription status
     * It will set the customer email subscription status who is going to update
     *
     * @param string $status status
     *
     * @return $this
     */
    public function setEmailSubscriptionStatus($status);

    /**
     * Set the customer sms subscription status
     * It will set the customer sms subscription status who is going to update
     *
     * @param string $status status
     *
     * @return $this
     */
    public function setSmsSubscriptionStatus($status);

    /**
     * Set the customer direct mail subscription status
     * It will set the customer direct mail subscription status
     * who is going to update
     *
     * @param string $status status
     *
     * @return $this
     */
    public function setDmSubscriptionStatus($status);

    /**
     * Set the customer region
     * It will set the customer region for direct mail who is going to update
     *
     * @param string $region region of customer
     *
     * @return $this
     */
    public function setDmRegion($region);

    /**
     * Set the customer district
     * It will set the customer district for direct mail who is going to update
     *
     * @param string $district District
     *
     * @return $this
     */
    public function setDmDistrict($district);

    /**
     * Set the customer detailed address
     * It will set the customer detailed address for direct mail
     * who is going to update
     *
     * @param string $detailedAddress detailed address
     *
     * @return $this
     */
    public function setDmDetailedAddress($detailedAddress);

    /**
     * Set the customer zip code
     * It will set the customer zip code for direct mail who is going to update
     *
     * @param string $zipcode zip code
     *
     * @return $this
     */
    public function setDmZipcode($zipcode);

    /**
     * Set the customer favorite store
     * It will set the customer favorite store who is going to update
     *
     * @param string $favoriteStore store from where customer is going to register
     *
     * @return $this
     */
    public function setFavoriteStore($favoriteStore);

    /**
     * Set the customer referrer code
     * It will set the customer referrer code who is going to update
     *
     * @param string $reffererCode referrer code of store representative
     *
     * @return $this
     */
    public function setReffererCode($reffererCode);

    /**
     * Set the locale of customer
     * It will set the locale of the customer who is going to the update
     *
     * @param string $locale locale
     *
     * @return string | null
     */
    public function setLocale($locale);

}
