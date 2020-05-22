<?php
/**
 * Created by PhpStorm.
 * User: abbas
 * Date: 2020-05-21
 * Time: 오후 5:09
 */

namespace Amore\CustomerRegistration\Api\Data;


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
     * get the customer id
     * It will get the customer id of the customer who is going to the update
     * @return int | null
     */
    public function getCustomerId();

    /**
     * get the partner Id
     * It will get the partner Id  of the customer who is going to the update
     * @return string | null
     */
    public function getPartnerId();

    /**
     * get the customer gorup code
     * It will get the customer gorup code of the customer who is going to the update
     * @return string | null
     */
    public function getCustomerGroupCode();

    /**
     * get the first name
     * It will get the first name of the customer who is going to the update
     * @return string | null
     */
    public function getFirstname();

    /**
     * get the last name
     * It will set the last name of the customer who is going to the update
     * @return string | null
     */
    public function getLastname();

    /**
     * get the gener
     * It will set the gender of the customer who is going to the update
     * @return int | null
     */
    public function getGender();

    /**
     * get the email
     * It will get the email of the customer who is going to the update
     * @return string | null
     */
    public function getEmail();

    /**
     * get the DOB
     * It will get the DOB of the customer who is going to the update
     * @return string | null
     */
    public function getDob();

    /**
     * get the created at
     * It will get the created at of the customer who is going to the update
     * @return string | null
     */
    public function getCreatedAt();

    /**
     * get the mobile number
     * It will get the mobile number of the customer who is going to the update
     * @return string | null
     */
    public function getMobileNumber();

    /**
     * get the email subscription status
     * It will get the email subscription status of the customer who is going to the update
     * @return int | null
     */
    public function getEmailSubscriptionStatus();

    /**
     * get the sms subscription status
     * It will get the sms subscription status of the customer who is going to the update
     * @return int | null
     */
    public function getSmsSubscriptionStatus();

    /**
     * get the dm subscription status
     * It will get the dm subscription status of the customer who is going to the update
     * @return int | null
     */
    public function getDmSubscriptionStatus();

    /**
     * get the dm region
     * It will get the dm region of the customer who is going to the update
     * @return string | null
     */
    public function getDmRegion();

    /**
     * get the dm district
     * It will get the dm district of the customer who is going to the update
     * @return string | null
     */
    public function getDmDistrict();

    /**
     * get the dm detailed address
     * It will get the dm detailed address of the customer who is going to the update
     * @return string | null
     */
    public function getDmDetailedAddress();

    /**
     * get the dm zip code
     * It will get the dm zip code of the customer who is going to the update
     * @return int | null
     */
    public function getDmZipcode();

    /**
     * get the favorite store
     * It will get the favorite store of the customer who is going to the update
     * @return string | null
     */
    public function getFavoriteStore();

    /**
     * get the refferer code
     * It will get the refferer code of the customer who is going to the update
     * @return string | null
     */
    public function getReffererCode();

    /**
     * Set the customer id
     * It will set the customer id who is going to update
     * @param $id
     * @return $this
     */
    public function setCustomerId($id);

    /**
     * Set the partner id
     * It will set the partner id who is going to update
     * @param $partnerId
     * @return $this
     */
    public function setPartnerId($partnerId);

    /**
     * Set the customer group code
     * It will set the customer group code who is going to update
     * @param $groupCode
     * @return $this
     */
    public function setCustomerGroupCode($groupCode);

    /**
     * Set the customer first name
     * It will set the customer first name who is going to update
     * @param $groupCode
     * @return $this
     */
    public function setFirstname($groupCode);

    /**
     * Set the customer last name
     * It will set the customer last name who is going to update
     * @param $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * Set the customer gender
     * It will set the customer gender who is going to update
     * @param $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Set the customer email
     * It will set the customer email id who is going to update
     * @param $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Set the customer dob
     * It will set the customer dob who is going to update
     * @param $dob
     * @return $this
     */
    public function setDob($dob);

    /**
     * Set the customer created at
     * It will set the customer created at who is going to update
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Set the mobile number
     * It will set the customer customer mobile number who is going to update
     * @param $mobileNumber
     * @return $this
     */
    public function setMobileNumber($mobileNumber);

    /**
     * Set the customer email subscription status
     * It will set the customer email subscription status who is going to update
     * @param $status
     * @return $this
     */
    public function setEmailSubscriptionStatus($status);

    /**
     * Set the customer sms subscription status
     * It will set the customer sms subscription status who is going to update
     * @param $status
     * @return $this
     */
    public function setSmsSubscriptionStatus($status);

    /**
     * Set the customer direct mail subscription status
     * It will set the customer direct mail subscription status who is going to update
     * @param $status
     * @return $this
     */
    public function setDmSubscriptionStatus($status);

    /**
     * Set the customer regition
     * It will set the customer region for direct mail who is going to update
     * @param $region
     * @return $this
     */
    public function setDmRegion($region);

    /**
     * Set the customer district
     * It will set the customer district for direct mail who is going to update
     * @param $district
     * @return $this
     */
    public function setDmDistrict($district);

    /**
     * Set the customer detailed address
     * It will set the customer detailed address for direct mail who is going to update
     * @param $detailedAddress
     * @return $this
     */
    public function setDmDetailedAddress($detailedAddress);

    /**
     * Set the customer zip code
     * It will set the customer zip code for direct mail who is going to update
     * @param $zipcode
     * @return $this
     */
    public function setDmZipcode($zipcode);

    /**
     * Set the customer favorite store
     * It will set the customer favorite store who is going to update
     * @param $favoriteStore
     * @return $this
     */
    public function setFavoriteStore($favoriteStore);

    /**
     * Set the customer refferer code
     * It will set the customer refferer code who is going to update
     * @param $reffererCode
     * @return $this
     */
    public function setReffererCode($reffererCode);

}
