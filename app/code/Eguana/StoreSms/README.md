Eguana_StoreSms v2.0

Website : Main Website 
Author : Shahroz Masih
Explanation :
# StoreSms

Description:

StoreSms module is used for the user account creation validation by verification code and used for the user to update the user order status notification.

Key features:

- Send verification code on customer create an account and validate this code for registration
- Notification user Order status like pending, processing, hold etc
- Admin can enable or disable the module
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

Create customer Address attribute with code phone_country_code;

Create Attribute

I have attached video link for creating attribute

https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d

For creating Address attribute

Stores -> Attributes -> Customer Address

![](https://s.nimbusweb.me/attachment/3632722/3ir0f97dg0tringg4gzy/wl4B4CKkhz6Rih3D/screenshot-dev.magentoshop.net-2019.12.11-19_14_13.png)

Click on ;Add New Attribute; to add a new attribute

![](https://s.nimbusweb.me/attachment/3632737/uo20x3endn3op87cqw7y/ZfTfITl1Po7kWlOl/screenshot-dev.magentoshop.net-2019.12.11-19_18_54.png)

 
![](https://s.nimbusweb.me/attachment/3632744/m2u3iqqmxdipxrb3v9fi/4lI445qycWVTDGQU/screenshot-dev.magentoshop.net-2019.12.11-19_20_02.png)
 
Add label for the attribute as you wish but attribute code must be phone_country_code;.

Add Attribute Code   phone_country_code

Attribute type  Select dropdown

Attribute Require set yes



Set Storefront Properties

set Show on Storefront  YES

Select Forms to Use In

select both options

Customer Address Registration

Customer Account Address



**Manage Label / Options**

![](https://s.nimbusweb.me/attachment/3632751/vatmu81u6ie86tzlxpeg/Tvh7e29DFHdFXi4b/screenshot-dev.magentoshop.net-2019.12.11-19_21_00.png)

**Default Store View**

Enter label for attribute

**Manage Options (Values of Your Attribute)**

Add values according to your country code.

Click on **Save Attribute** button to save attribute.

I have attached video link for creating attribute

[https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d](https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d)


**Create Sms Templates**

Navigate to **Stores­ ⇾**  **Marketing** and **Email Templates** under **Communications**.

Click on **Add New Template** button


 
Custom templates created while installing module under Eguana_StoreSms

User can select a template

Click on **Load Template** button


![](https://s.nimbusweb.me/attachment/3632791/ofk3r9vig6w6oci21tdv/mYyn8UdT1Z9i5GwL/screenshot-dev.magentoshop.net-2019.12.11-19_27_59.png)

Enter **Template Name**

Enter **Template Subject**

Click on ** **** Save Template** button
![](https://s.nimbusweb.me/attachment/3632765/oklih0h736ivg4x9epye/mPRgkDBxvFPPfdf0/screenshot-dev.magentoshop.net-2019.12.11-19_22_40.png)


Navigate to **Stores ⇾ Configuration** and **StoreSms** under **Eguana Extensions** tab.

**Configuration**

1. Navigate to Stores ⇾ Configuration and click on **StoreSms** under **Eguana Extensions** tab in the left panel.

**i)**  **General Configuration**

![](https://s.nimbusweb.me/attachment/3632796/zf71ck44gb8b731efvop/MO3R28TPnFgEHJzn/screenshot-dev.magentoshop.net-2019.12.11-19_29_05.png)
- **●●**** Enable Extension**

This will decide either enable/disable StoreSms Module.

- **●●**** Username**

This is the API username which is used for sending SMS.

- **●●**** API URL**

This is API URL  Used to send SMS.

- **●●**** Password**

This is the password used for sign in for API.

**ii) Message templates**

![](https://s.nimbusweb.me/attachment/3632799/35asgloqe56mp9aumak0/9DeVlXy8YqU7Kooc/screenshot-dev.magentoshop.net-2019.12.11-19_29_52.png)

**Enable SMS based registration verification option**

If this option is enabled it will enable code verification on the registration page

After enabling this field three fields will add on customer register account page

i)   International telephone area code

ii)  Mobile number

iii) SMS Code

![](https://s.nimbusweb.me/attachment/3632808/63k9saopbz3ifat0ospx/pDr7TbciqddXYEmf/screenshot-dev.magentoshop.net-2019.12.11-19_30_54.png)
**Register SMS Template**

Select Sms template to send SMS if not select then default template will be used.

**Send SMS after status changed to Pending Payment&quot;**

If this field is enabled then order notification SMS is sent to customer when order status to change pending

**Template**

Select template for SMS order notification
![](https://s.nimbusweb.me/attachment/3632813/bprbtpvcbncdd2b5apra/zbJ2eBwWEwyhknCN/screenshot-dev.magentoshop.net-2019.12.11-19_32_00.png)

Same as above for the every order status user can enable/disable and set the template

iii) **Test Message**


**Test Numbers**

Enter the mobile number with country code

**Test Message**

Enter test SMS for testing module working

Click on send button message will be sent to your mobile number
below screen shoot attached
![](https://s.nimbusweb.me/attachment/3632816/49n881yeg7cu9rofqorl/SJP18YDPNAKSeK3h/screenshot-dev.magentoshop.net-2019.12.11-19_32_45.png)

**Test cases** **(**code verification**)**

To verify Verification code Sending On create account

  Go to create account page

  - Enter International country code

  - Enter mobile number without leading zero

  -Click on &quot;Get Verification Code&quot; to send Code



After Receive code enter code in &quot;SMS Code&quot; field

For verify code click Verify Code button 
If code is the same as sent to the number then it will show message your verification code has verified.
If code is the same as sent to the number then it will show message your please enter valid code.

After this click create account button 

If code is the same as sent to the number then it will create an account.

If code is different then code sent it will through error &quot;Please add correct verification code&quot;.

If the phone number is not the same as number receive verification code it will through error &quot;Please add correct verification code&quot;.

**Test Cases** (Order status notification)

Make sure you have created attribute as per requirement;phone_country_code;

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

Test cases:

**-** The message will be sent.

- if you click without entering a number it shows error message Please Test Enter Phone Number;

- if you click without entering text message it shows error message Please Enter Test text message;

- if you click without enable module it will show error message Please enable extension;

- if you click without entering API credentials it will show error message Please enter valid API credentials;
Eguana_StoreSms v2.0

Website : Main Website 
Author : Shahroz Masih
Explanation :
# StoreSms

Description:

StoreSms module is used for the user account creation validation by verification code and used for the user to update the user order status notification.

Key features:

- Send verification code on customer create an account and validate this code for registration
- Notification user Order status like pending, processing, hold etc
- Admin can enable or disable the module
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

Create customer Address attribute with code phone_country_code;

Create Attribute

I have attached video link for creating attribute

https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d

For creating Address attribute

Stores -> Attributes -> Customer Address

![](https://s.nimbusweb.me/attachment/3632722/3ir0f97dg0tringg4gzy/wl4B4CKkhz6Rih3D/screenshot-dev.magentoshop.net-2019.12.11-19_14_13.png)

Click on ;Add New Attribute; to add a new attribute

![](https://s.nimbusweb.me/attachment/3632737/uo20x3endn3op87cqw7y/ZfTfITl1Po7kWlOl/screenshot-dev.magentoshop.net-2019.12.11-19_18_54.png)

 
![](https://s.nimbusweb.me/attachment/3632744/m2u3iqqmxdipxrb3v9fi/4lI445qycWVTDGQU/screenshot-dev.magentoshop.net-2019.12.11-19_20_02.png)
 
Add label for the attribute as you wish but attribute code must be phone_country_code;.

Add Attribute Code   phone_country_code

Attribute type  Select dropdown

Attribute Require set yes



Set Storefront Properties

set Show on Storefront  YES

Select Forms to Use In

select both options

Customer Address Registration

Customer Account Address



**Manage Label / Options**

![](https://s.nimbusweb.me/attachment/3632751/vatmu81u6ie86tzlxpeg/Tvh7e29DFHdFXi4b/screenshot-dev.magentoshop.net-2019.12.11-19_21_00.png)

**Default Store View**

Enter label for attribute

**Manage Options (Values of Your Attribute)**

Add values according to your country code.

Click on **Save Attribute** button to save attribute.

I have attached video link for creating attribute

[https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d](https://stage.loom.com/share/c2ae4cb55904413eba97243fa5422a6d)


**Create Sms Templates**

Navigate to **Stores­ ⇾**  **Marketing** and **Email Templates** under **Communications**.

Click on **Add New Template** button


 
Custom templates created while installing module under Eguana_StoreSms

User can select a template

Click on **Load Template** button


![](https://s.nimbusweb.me/attachment/3632791/ofk3r9vig6w6oci21tdv/mYyn8UdT1Z9i5GwL/screenshot-dev.magentoshop.net-2019.12.11-19_27_59.png)

Enter **Template Name**

Enter **Template Subject**

Click on ** **** Save Template** button
![](https://s.nimbusweb.me/attachment/3632765/oklih0h736ivg4x9epye/mPRgkDBxvFPPfdf0/screenshot-dev.magentoshop.net-2019.12.11-19_22_40.png)


Navigate to **Stores ⇾ Configuration** and **StoreSms** under **Eguana Extensions** tab.

**Configuration**

1. Navigate to Stores ⇾ Configuration and click on **StoreSms** under **Eguana Extensions** tab in the left panel.

**i)**  **General Configuration**

![](https://s.nimbusweb.me/attachment/3632796/zf71ck44gb8b731efvop/MO3R28TPnFgEHJzn/screenshot-dev.magentoshop.net-2019.12.11-19_29_05.png)
- **●●**** Enable Extension**

This will decide either enable/disable StoreSms Module.

- **●●**** Username**

This is the API username which is used for sending SMS.

- **●●**** API URL**

This is API URL  Used to send SMS.

- **●●**** Password**

This is the password used for sign in for API.

**ii) Message templates**

![](https://s.nimbusweb.me/attachment/3632799/35asgloqe56mp9aumak0/9DeVlXy8YqU7Kooc/screenshot-dev.magentoshop.net-2019.12.11-19_29_52.png)

**Enable SMS based registration verification option**

If this option is enabled it will enable code verification on the registration page

After enabling this field three fields will add on customer register account page

i)   International telephone area code

ii)  Mobile number

iii) SMS Code

![](https://s.nimbusweb.me/attachment/3632808/63k9saopbz3ifat0ospx/pDr7TbciqddXYEmf/screenshot-dev.magentoshop.net-2019.12.11-19_30_54.png)
**Register SMS Template**

Select Sms template to send SMS if not select then default template will be used.

**Send SMS after status changed to Pending Payment&quot;**

If this field is enabled then order notification SMS is sent to customer when order status to change pending

**Template**

Select template for SMS order notification
![](https://s.nimbusweb.me/attachment/3632813/bprbtpvcbncdd2b5apra/zbJ2eBwWEwyhknCN/screenshot-dev.magentoshop.net-2019.12.11-19_32_00.png)

Same as above for the every order status user can enable/disable and set the template

iii) **Test Message**


**Test Numbers**

Enter the mobile number with country code

**Test Message**

Enter test SMS for testing module working

Click on send button message will be sent to your mobile number
below screen shoot attached
![](https://s.nimbusweb.me/attachment/3632816/49n881yeg7cu9rofqorl/SJP18YDPNAKSeK3h/screenshot-dev.magentoshop.net-2019.12.11-19_32_45.png)

**Test cases** **(**code verification**)**

To verify Verification code Sending On create account

  Go to create account page

  - Enter International country code

  - Enter mobile number without leading zero

  -Click on &quot;Get Verification Code&quot; to send Code



After Receive code enter code in &quot;SMS Code&quot; field

For verify code click Verify Code button 
If code is the same as sent to the number then it will show message your verification code has verified.
If code is the same as sent to the number then it will show message your please enter valid code.

After this click create account button 

If code is the same as sent to the number then it will create an account.

If code is different then code sent it will through error &quot;Please add correct verification code&quot;.

If the phone number is not the same as number receive verification code it will through error &quot;Please add correct verification code&quot;.

**Test Cases** (Order status notification)

Make sure you have created attribute as per requirement;phone_country_code;

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

Test cases:

**-** The message will be sent.

- if you click without entering a number it shows error message Please Test Enter Phone Number;

- if you click without entering text message it shows error message Please Enter Test text message;

- if you click without enable module it will show error message Please enable extension;

- if you click without entering API credentials it will show error message Please enter valid API credentials;

