Amore_Customer v2.0.0

Website: Amore

Author: Abbas Ali Butt

DB Table Name : Added customer attributes

    - mobile_number: To save the customer mobile number
    - favorite_store: To save favorite store value when custmer will register using QR Code
    - referrer_code: To save refferer code value when custmer will register using QR Code
    - dm_subscription_status: To identify whether customer is subscribed for DM or not
    - terms_and_services_policy: Whether customer has been agreed with the terms and services policy or not
    - 
: To save customer integration number. Auto generated number like Order increment id. Further details you can read in next sections
- sales_organization_code: To save Website based sale organization code.
- sales_office_code: To save Website based sale organization code.
- call_subscription_status: To save call marketing subsctiption. Currently we are not taking it while register. Only when POS want to update
- status_code: To save customer status. In case of POS api customer update call.
- imported_from_pos: To identify whether customer information was brought from POS or not
- partner_id: To save Website based partner id.

Explanation: This module will customize the registration process according to the Amore requirements

# Customer

Description:

Register module will be mainly used to customize the user registration process, call API of POS to get the offline
registered user details

Requirements:

    - Check whether customer already exist in POS or not
        - Online Customer : Those customer who does not exist in POS 
        - Offline Customer : Those customer who exist in POS, while going to step 2 get information from POS and add in step 2 form
    - If exist in POS then fetch the infromation from POS and load into the form for registration in Magento
    - If not exist in POS then load the form without any existing infromation. But load the phone number from step 1 and
      in step 2 customer is not allowed to change first name, last name and phone number
    - When existing customer registration completed also update in the POS with the new infromation.  
    - When new customer registration completed also create a new customer in the POS
    - While registration also check whether phone number already exist or not. If already exist in Magento then show 
      CS information page.
    - If customer login from social media then load social media information in registration page
    - Create customer sequence number for online customers in case of offline get from POS
    - Once Date of Birth (DOB) do not allow customer to change DOB
    - On second step show the POS alert message. It means if customer information is fetched from the POS then show message else do not show.


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


![Configuration](https://i.ibb.co/5X0zQ9C/Customer-Registration-Configuration.png)

**Frontend Registration Step 1**

![Step 1](https://i.ibb.co/S00yt7K/Customer-Registration-Step-1.png)

**Frontend Registration Step 2**

![Step 2](https://i.ibb.co/58z1GK7/Customer-Registration-Step-2.png)

**Frontend Registration Step 2 Direct Mail (DM) Subscription**

![DM Subscription](https://i.ibb.co/vdrsprW/Customer-Registration-Step-2-DM-Subscription.png)


All the configurations are website level not store level.

**Stores -> Configuration -> Amore Extensions -> Customer Registration**

**General Configuration**

1. Enable Extension: To enable or disable this extension. If it will enable then while register customer will see the two step registration. As you can see in the
   above images frontend registration Step 1 & Step 2. Else you will see the default Magento registration.
1. Terms CMS Block Id: It will take the CMS block id which will show in the top of first step form during customer registration. **For Reference please see the image Frontend Registration Step 1-1 part**
1. POS Alert CMS Block Id: It will take the CMS block id which will show in the top of second step If Magento get information from POS.
1. Expiration Time in Minutes: It is a expiration time of the code which will send to the customer in step 1 for mobile verification **For Reference please see the image Frontend Registration Step 1-5 part**
1. Minimum Mobile Number Digits:Minimum number of digits allowed in the mobile number. **For Reference please see the image Frontend Registration Step 1-2 part**
1. Maximum Mobile Number Digits: Maximum number of digits allowed in the mobile number. **For Reference please see the image Frontend Registration Step 1-2 part**
1. Membership Error CMS Page: Here admin will add the CMS page URL key. In case of customer with same mobile number exist then system will redirect to this page. If there is no CMS page in configuration then show a message.
1. Duplicate Membership CMS Page: Here admin will add the CMS page URL key. In case of customer with same name and mobile number exist then system will redirect to this page. If there is no CMS page in configuration then show a message.
1. Terms and Services Policy CMS Block: Here user will add the Terms and services policy content related CMS block id. And it will show in popup when user will click on read more link.
1. SMS Verification Enable: Here user admin can enable disable SMS verification. In case sms verification is disabled then during SMS verification 1234 will be the verification code.


**POS System**

1. Base URL: Base URL of the POS system.
1. Member Information: URI from where system can get the memerbship information of POS members
1. Member Join: URI from where system can send the memerbship information of Magento customers when they will join or update in the system
1. Sales Organization Code: It is a code to recognize the site for sales organization. Such as TW10 for Laneige Taiwan, TW20 for Sulwhasso Taiwan, SG10 for Laneige Singapore, SG20 for Sulwhasso Singapore and similarly ID10,ID20 for Indonesia.
   It will assign to all customer who register to that particulat site. You can see this information after customer registration from admin in his account information.
1. Sales Office Code: It is a code to recognize the site for sales office. Such as TW10 for Laneige Taiwan, TW20 for Sulwhasso Taiwan, SG10 for Laneige Singapore, SG20 for Sulwhasso Singapore and similarly ID10,ID20 for Indonesia
   It will assign to all customer who register to that particulat site. You can see this information after customer registration from admin in his account information.
1. Partner Id: It is a partner id which will be assigned to the customer, while resgiter from this particular website
   It will assign to all customer who register to that particulat site. You can see this information after customer registration from admin in his account information.
1. SSL Verification: For the parameters to call POS API
1. Debug: If yes then it will log api calls and store the exceptions. In the file var/log/pos.log

**Name Validation**

1. Add not allowed characters: Those characthers which are not allowed in first name and last name during register or edit account.

# Customer Integraion Number

![Integration Number](https://i.ibb.co/NrNzcmR/Customer-Integration-Number.png)

Format : CountryBrandChannelSequence

Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Office Code**

- CoutnryBrand : It will be get from the configuration Stores -> Configuration -> Amore Extensions -> Customer Registration -> POS System -> **Sales Office Code**
- Channel: If url have QR code or POS return data in case of getInfo API then it's value will be 1 else 2
- Sequence: Module will create one table for each website. For example customer_online_sequence with different stating number one million one. Module will get the next number whenever customer will register

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
    - "sex": Customer gender, F for female and M for male
    - "emailYN": Subscribe or unsubscribe to newsletter, Y for subscirbe and N for unsubscribe
    - "callYN": Subscribe or unsubscribe to call, Y for subscirbe and N for unsubscribe
    - "dmYN": Subscribe or unsubscribe to direct mail, Y for subscirbe and N for unsubscribe
    - "homeCity": Home city for direct mail. home City code
    - "homeState": Home state for direct mail, home state code
    - "homeAddr1": Home address for direct mail
    - "homeZip": Home zip for direct mail
    - "statusCD": Customer status
    - "salOrgCd": Sales organization code but will not update
    - "salOffCd": Sales office code but will nto update

If you do not want to change any attribute value then set it ''

Response

- When there is no website exist agains the sales office code (salOffCd)

{
"code": "0001",
"message": "No website exist against sales office code salOffCd",
"data": {
"status_code": "0001",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When customer integration sequence (cstmIntgSeq) parameter is empty

{
"code": "0002",
"message": "Customer Sequence Number can not be empty cstmIntgSeq",
"data": {
"status_code": "0002",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When there is no customer exist against the customer integration sequence (cstmIntgSeq)

{
"code": "0003",
"message": "No customer exist against this integration sequence",
"data": {
"status_code": "0003",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When there are more than one customer in against the customer integration sequence (cstmIntgSeq) in a specific website

{
"code": "0004",
"message": "There are more than one customer exist against this sequence Id cstmIntgSeq in website customerWebsiteId",
"data": {
"status_code": "0004",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When mobile number (mobile) assigned to any other customer in the same website

{
"code": "0005",
"message": "mobileNo Mobile number is assigned to other customer in website customerWebsiteId",
"data": {
"status_code": "0005",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}
- When mobile number (mobile) contain other than number and hypens

{
"code": "0006",
"message": "mobileNo Mobile number can contain only number and hypens",
"data": {
"status_code": "0006",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When there is no city name against the homeCity code

{
"code": "0007",
"message": "There is not city name against the homeCity code",
"data": {
"status_code": "0007",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- It homeState value is set but homeCity is empty

{
"code": "0008",
"message": "If you want to set the state then city is required for state code ",
"data": {
"status_code": "0008",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- When in the system there is no name exist against homeState code

{
"code": "0009",
"message": "There is no state name against the homeState code",
"data": {
"status_code": "0009",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- There can be Magento core validation messages as well

For example

{
"code": "0",
"message": "Invalid date",
"data": {
"status_code": "0",
"status_message": "NO",
"cstm_intg_seq": "cstmIntgSeq"
}
}

- On successfull update

  {
  "code": "0000",
  "message": "SUCCESS",
  "data": {
  "status_code": "200",
  "status_message": "OK",
  "cstm_intg_seq": "cstmIntgSeq"
  }
  }

# QRCode

- URL = {BASE URL}/customer/account/create/referrer_code/{Value}/favorite_store/{Value}

When customer account will be create using above url it will save the favorite store and refferer code during registration

# Test Cases

- We can check POS call result in SALES -> Eguana BizConnect -> Operation Log.
    - eguana.pos.get.info call POS to get information using firstname, last name and mobile number from POS if customer exist
    - eguana.pos.sync.info call POS to send the updated information to the POS system.
- If customer have default billing address then send address infromation in the POS API whether customer is subscribe to DM or not
- Whenever customer create, edit or delete from anywhere (Admin, frontend, API) send updated infromation to the POS
- Online customer create without any address and DM subscription
- On create a default billing address send customer information with address to the POS
- Do not call POS API if customer edit any address other than the default billing address such as default shipping, billing and shipping addresses
- Online customer during register add address but nott subscirbe to DM then create default address and also send to the POS
- Using bar code registeration if get email errors then paramters in URL should remain same.
- Update customer using API
- If add exist mobile number or integration number then get related error message
