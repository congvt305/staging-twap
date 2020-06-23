Amore_Customer v2.0.0

Website: Amore

Author: Abbas Ali Butt

DB Table Name :
 
Explanation: This module will customize the registration process according to the Amore requirements

# Customer

Description:

Register module will be mainly used to customize the user registration process, call API of POS to get the offline
 registered user details

Requirements:

    - Check whether customer already exist in POS or not
    - If exist in POS then fetch the infromation from POS and load into the form for registration in Magento
    - If not exist in POS then load the form without any existing infromation. But load the phone number from step 1 and
      in step 2 customer is not allowed to change the phone number
    - When existing customer registration completed also update in the POS with the new infromation.  
    - When new customer registration completed also create a new customer in the POS
    - While registration also check whether phone number already exist or not. If already exist in Magento then show 
      CS information page.
    - If customer login from social media then load social media information in registration page
    - Create customer sequence number


Key features:

      1. As a store representative I want to scan a QR code so that I can register the customer.
      2. As an old user I want to add my POS information so that my old information will be fetched to Magento from POS
       system
      3. As a new customer I want to register without POS information so that I can register as a new customer
      4. As a customer I want to provide my newsletter direct mail address so that Amore can send me news letters on
       that address
      5.Show the SMS verification relateld fields to the customer
      6 Show the timer to expire the code
      7. As an admin I want to see from which store user has been registered so that I can list users register from
       specific store

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_CustomerRegistration

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Curstomer Registration** under Amore tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Curstomer Registration** under Amore tab in the left panel.

 
 ![Configuration](https://i.ibb.co/xJs59Wk/Selection-046.png)
 
 All the configurations are website level not store level.
 
1. Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Enable Extension** : To 
enable or disable this extension. If it will enable then while register customer will see the two step registration 
1. Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Terms CMS Block Id** : It will 
take the CMS block id which will show in the top of first step form during customer registration
1. Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Expiration Time in Minutes** : It is 
a expiration time of the code which will send to the customer in step 1 for mobile verification
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Minimum Mobile Number Digits** : Minimum number of 
digits allowed in the mobile number
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Minimum Mobile Number Digits** : Minimum number of 
digits allowed in the mobile number
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Membership Error CMS Page** : Here admin will add the 
CMS page URL key. In case of customer with same mobile number exist then system will redirect to this page
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> General Configuration -> **Duplicate Membership CMS Page** : Here admin will add the 
CMS page URL key. In case of customer with same name and mobile number exist then system will redirect to this page
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Base URL** : Base URL of the POS system.
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Member Information** : URI from where system can get the memerbship
information of POS members 
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Member Join** : URI from where system can send the memerbship
information of Magento customers when they will join the system
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Organization Code** : It is a code to recognize the site for sales organization. Such as
TW10 for Laneige Taiwan, TW20 for Sulwhasso Taiwan, SG10 for Laneige Singapore, SG20 for Sulwhasso Singapore and similarly ID10,ID20 for Indonesia
1.  Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Office Code** : It is a code to recognize the site for sales office. Such as 
TW10 for Laneige Taiwan, TW20 for Sulwhasso Taiwan, SG10 for Laneige Singapore, SG20 for Sulwhasso Singapore and similarly ID10,ID20 for Indonesia
 
# Customer Integraion Number
 
Format : CountryBrandChannelSequence Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Office Code** 

- CoutnryBrandh : It will be get from the configuration Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Office Code** 
- Channel: If url have QR code or POS return data in case of getInfo API then it's value will be 1 else 2
- Sequence: Module will create two tables for eachwebsite. For example customer_pos_sequence, customer_online_sequence with different stating number based on the requirement. Module will get 
the next number whenever customer will register

# API

- URL :  {Base URL}/rest/all/V1/pos-customers/
- Purpose : 
- Parameters
    - "cstmIntgSeq": Customer integration number but it can not update
    - "firstName": Customer First Name
    - "lastName": Customer Last Name
    - "birthDay": Customer brith day. format will be yyyymmdd
    - "mobileNo": Customer mobile number
    - "email": Customer email id
    - "sex": Customer gender
    - "emailYN": Subscribe or unsubscribe to newsletter
    - "smsYN": Subscribe or unsubscribe to sms
    - "callYN": Subscribe or unsubscribe to call
    - "dmYN": Subscribe or unsubscribe to direct mail
    - "homeCity": Home city for direct mail
    - "homeState": Home state for direct mail
    - "homeAddr1": Home address for direct mail
    - "homeZip": Home zip for direct mail
    - "statusCD": Customer status
    - "salOrgCd": Sales organization code but will not update
    - "salOffCd": Sales office code but will nto update
    
If you do not want to change any attribute value then set it ''

# QRCode

- URL = {BASE URL}/customer/account/create/referrer_code/{Value}/favorite_store/{Value}

When customer account will be create using above url it will save the favorite store and refferer code during registration