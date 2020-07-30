Eguana_StoreSms v2.0

Website : Main Website 
Author : Mobeen Sarwar
Explanation :
# StoreSms

Description:

StoreSms module is used for the user account creation validation by verification code and used for the user to update the user order status notification.

Key features:

- Send verification code on customer create an account and validate this code for registration
- Notification user Order status like pending, processing, hold etc
- Admin can enable or disable the module
- Admin can select country for the store sms
- Admin can select customer name format to reverse with no empty space eg. lastNameFirstName
- Admin can enable or disable Verification code on registration
- Admin can enable or disable order notification for a specific order status
- Admin can edit the SMS template according to the requirement



Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_StoreSms --clear-static-content

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Requirement:

**Create Sms Templates**

Navigate to **Stores­ ⇾**  **Marketing** and **Email Templates** under **Communications**.

Click on **Add New Template** button


 
Custom templates created while installing module under Eguana_StoreSms

User can select a template

Click on **Load Template** button


![](https://nimbusweb.me/box/attachment/3632791/ofk3r9vig6w6oci21tdv/mYyn8UdT1Z9i5GwL/screenshot-dev.magentoshop.net-2019.12.11-19_27_59.png)

Enter **Template Name**

Enter **Template Subject**

Click on ** **** Save Template** button
![](https://nimbusweb.me/box/attachment/3632765/oklih0h736ivg4x9epye/mPRgkDBxvFPPfdf0/screenshot-dev.magentoshop.net-2019.12.11-19_22_40.png)


Navigate to **Stores ⇾ Configuration** and **StoreSms** under **Eguana Extensions** tab.

**Configuration**

1. Navigate to Stores ⇾ Configuration and click on **StoreSms** under **Eguana Extensions** tab in the left panel.

**i)**  **General Configuration**

![](https://i.ibb.co/LZP1SmL/image.png)
- **●●**** Enable Extension**

This will decide either enable/disable StoreSms Module.

- **●●**** Unique Mobile Number Validation	**

This will verify that new registration number is not already register.

- **●●**** Select Country  **

Select the country for your store.

- **●●**** Reverse name format with no empty space  **

If you set this to yes, the customer name format will be like LastNameFirstName

- **●●**** Username**

This is the API username which is used for sending SMS.

- **●●**** API URL**

This is API URL  Used to send SMS.

- **●●**** Password**

This is the password used for sign in for API.

**ii) Message templates**

![](https://nimbusweb.me/box/attachment/3632799/35asgloqe56mp9aumak0/9DeVlXy8YqU7Kooc/screenshot-dev.magentoshop.net-2019.12.11-19_29_52.png)

**Enable SMS based registration verification option**

If this option is enabled it will enable code verification on the registration page

After enabling this field four fields will add on customer register account page

i)   phone 

ii)  Get verification code

iii) Enter 4 digit verification code

iv)  check

![](https://i.ibb.co/nmX16vn/94cd8ddd-4886-42c7-b04a-04796573dafe.png)

**Register SMS Template**

Select Sms template to send SMS if not select then default template will be used.

**Send SMS after status changed to Pending Payment&quot;**

If this field is enabled then order notification SMS is sent to customer when order status to change pending

**Template**

Select template for SMS order notification
![](https://nimbusweb.me/box/attachment/3632813/bprbtpvcbncdd2b5apra/zbJ2eBwWEwyhknCN/screenshot-dev.magentoshop.net-2019.12.11-19_32_00.png)

Same as above for the every order status user can enable/disable and set the template

iii) **Test Message**


**Test Numbers**

Enter the mobile number with country code

**Test Message**

Enter test SMS for testing module working

Click on send button message will be sent to your mobile number
below screen shoot attached
![](https://nimbusweb.me/box/attachment/3632816/49n881yeg7cu9rofqorl/SJP18YDPNAKSeK3h/screenshot-dev.magentoshop.net-2019.12.11-19_32_45.png)

**Test cases** **(**code verification**)**

To verify Verification code Sending On create account

  Go to create account page

  - Enter mobile number without leading zero

  -Click on &quot; Get verificaiton code &quot; to send Code

After Receive code enter code in &quot;Enter 4 digit verification code&quot; field

For verify code click check button 

If code is the same as sent to the number then it will show message your verification code has verified and Verify Registration button becomes enabled.
If code is the not the same as sent to the number then it will show message Verification code is wrong.

After this click Verify registration button that will take you to the next step.


**Test Cases** (Order status notification)

Place an order from storefront

Go to Admin

Store -> Sales -> Orders open Order you want to change status

change status and notification will be sent to the mobile number you have entered in shipping address during creating Order



**Test Cases** (For Test Sms)

Go to, Admin

Store -> Configuration ->EGUANA Extensions -> Eguana StoreSms

Test Message

Enter telephone number with international code

Enter text message you want to test

Click on Send button;

**Note:** Test sms can be sent to any country. (Do not depend upon selected country in the configuration).

Test cases:

**-** The message will be sent.

- if you click without entering a number it shows error message Please Test Enter Phone Number;

- if you click without entering text message it shows error message Please Enter Test text message;

- if you click without enable module it will show error message Please enable extension;

- if you click without entering API credentials it will show error message Please enter valid API credentials;
