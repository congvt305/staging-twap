#Adyen Payment

`Website` : Main Website URL  
`Author` : Raheel Shaukat

##Description

This module gives you access to all the features of the Adyen payments platform in one integration. Accept all major global and local payment methods, store your shoppers' payment details for later payments, and use risk management system. The plugin keeps the two platforms synchronized, so you can view transaction summaries in Magento, and switch to Adyen for more detailed reporting.

##Key features

- **Credit cards** Accept credit card payments on your website and mobile application, without redirecting to an external website. Sensitive cardholder data is collected securely using our Custom Card Component, which reduces your PCI DSS-compliance requirements.
  
- **Dynamic 3D Secure 2 authentication** including AVS checks.
  - When a payment request includes the browserInfo object, Adyen will determine whether the payment is routed through 3D Secure authentication. There are two actions available Always and Prefer not.
  - **Always** Use 3D Secure whenever possible.
  - **Prefer not** Do not apply 3D Secure authentication, unless the issuing bank requires it to complete the authorisation.
  - For more details please refer https://docs.adyen.com/risk-management/dynamic-3d-secure.
  - **AVS checks** Address Verification System (AVS) is a security feature that verifies the billing address of the cardholder. It does this by comparing the billing address the shopper enters with the address on file with the card issuer. AVS validates the street address and ZIP/postal code of the shopper. (This risk rule is based on the issuer and may not be available for every transaction.)

- **Local payment methods** Accept local payment methods including **iDEAL**, **Sofort**, **Klarna**, **giropay**, **Google Pay**, and many others.

- **In-store payments** Accept in-store payments from point-of-sale (POS) devices.

- **Tokenization** Offer returning shoppers a faster checkout experience by saving their card details, or implement subscription payments.

- **Revenue Protect** Use risk management system to identify and block fraudsters, while reducing friction for legitimate shoppers. You can either fully automate the risk management process, or add manual review for certain payments.
- **Order management** Manage orders and view transaction summaries in Magento, while switching to Adyen for more detailed reporting and conversion analytics – the two platforms are synchronized.

**For more details please refer** https://docs.adyen.com/plugins/magento-2

## Set up Adyen test Customer Area

- To setup Adyen test Customer Area please first signup throuh this link https://www.adyen.com/signup 

![signup](https://nimbus-screenshots.s3.amazonaws.com/s/c897f5c0f9601bfc7b2acdb3211937c8.png)

- Fill the form and submit it. After submitting you will receive a confirmation email from Adyen in which merchant account name and username is mentioned. For password there will be a link to set password. Click the link.

![set-pass](https://nimbus-screenshots.s3.amazonaws.com/s/cec87597adc501c553b802f8f3daa1d6.png)

- After setting password login to your account. There will be a page shown to select payments type. Select one of it.

![payment-type](https://nimbus-screenshots.s3.amazonaws.com/s/5bb405a78d926c15d1d03a42897aafeb.png)

- On basis of these type 2 or 3 type of merchant accounts will be shown in Adyen panel. If **"Ecommerce & point of sale"** type is selected then there will be 2 more accounts under the main account. Like if we have merchant account with name **"EGSolutions"** then 2 more sub accounts will be crteated automatically with names **"EGSolutionsECOM"** and **"EGSolutionsPOS"**. So make sure to use relevant account name with its relevant settings in your business projects. We will use Ecommerce account in magento panel.

![merchant-accounts](https://nimbus-screenshots.s3.amazonaws.com/s/801f10acf8cd7ee7d60b21ea2f07aaec.png)

## Generate an API Key

1) Go to **Account ­ ⇾ API credentials**, and select the credential for your integration, for example ws@Company.

![generate-api-key-1](https://nimbus-screenshots.s3.amazonaws.com/s/e134a51ff3fd8b985271096ec31260cd.png)

![generate-api-key-2](https://nimbus-screenshots.s3.amazonaws.com/s/bb6212554139dc1a703077ad31046c22.png)

2) Under **Authentication** tab, click **Generate New API Key** button.

![generate-api-key-3](https://nimbus-screenshots.s3.amazonaws.com/s/e02f7a54df0941314dfda1267b42a085.png)

3) Copy and securely store the **API Key** in your system — you won't be able to restore it later. If your **API Key** is lost or compromised, you need to generate a new one.

4) Click **Save Generated API Key** button to save the generated **Api Key**.

5) Select Save at the bottom of the page.

## Set up notifications

Adyen uses notifications, our webhook service, to inform your Magento platform of payment status changes. For more information, refer to Notifications.

To receive notifications:

1) Go to **Account ­ ⇾ Server Communication**.

![notofications-1](https://nimbus-screenshots.s3.amazonaws.com/s/a0a4715502e3023e4477f5c50ea2920c.png)

2) Next to **Standard Notification**, select **Add** button.

![notofications-2](https://nimbus-screenshots.s3.amazonaws.com/s/dc6bc9f255d84e0425fbe6660066b496.png)

3) In the **URL** box, enter your website URL followed by /adyen/process/json.

4) From the **SSL Version** list, select **TLSv.1.2**.

5) Select the **Active** check box.

6) Set **Method** to **JSON**.

![notofications-3](https://nimbus-screenshots.s3.amazonaws.com/s/3298b812f433189fccf11d5542750688.png)

7) In the **Authentication** section, in the **User Name** and **Password** boxes, enter a username and password for basic authentication.

8) In the **Additional Settings** section, next to **HMAC Key (HEX Encoded)**, select Generate new HMAC key. Store the generated key in your password management tool.

![notofications-4](https://nimbus-screenshots.s3.amazonaws.com/s/b0dcb8900d424914cd6e6965f3f70fe6.png)

9) Click **Save Configuration** button on buttom of page.

## Configure risk settings (For test user)

1) Go to **Risk ­ ⇾ Risk Settings**.

![risk-setting-1](https://nimbus-screenshots.s3.amazonaws.com/s/d2c072798943d1a6ed089abc4c645af8.png)

2) Turn off Adyen's risk system.

3) Disable **Perform Transaction Risk Analysis (TRA) for TRA exemption**

4) Click **Save Configuration** Link to save settings.

![risk-setting-2](https://nimbus-screenshots.s3.amazonaws.com/s/b5debf2bd622bcde108745e4c3eb9479.png)

To configure risk setting on Live account please refer this link for further details https://ca-test.adyen.com/ca/ca/risk/settings.shtml

## Test accounts and credintials

### Eguana

**Acount** Eguana  
**Username** admin  
**Password** Eguana@123  
**Type** Ecommerce  
**API Key** AQEhhmfuXNWTK0Qc+iSVlXE5qeV0s1vY3GiOLTOHxfDYk8GfEMFdWw2+5HzctViMSCJMYAc=-vav8B3q2wQcDZ/u0KMLr3GmIsoYGSEgWN+TOItmt94A=-Nn975A{$_tUc~<bP  
**Notification User Name** vnlaneige
**Notification Password** vnlaneige  
**HMAC Key**  5F2E22ECDE622B9B3139882739A33B262E6EAB4D0D71B1D33568B3FCC0363EBA

### EGSolutions

**Acount** EGSolutions  
**Username** admin  
**Password** Eguana@123  
**Type** Ecommerce & POS  
**API Key** AQEmhmfuXNWTK0Qc+iSVtVc3q/GPQYRDHhfMLXpZ/80NJ7WEmuJakTAQwV1bDb7kfNy1WIxIIkxgBw==-kHGAGhBB7Z+imVEGLKxSj0iCAvQF8aokO/QyKIoQOgw=-L^]kB?Hcc]Sf.7k<  
**Notification User Name** vnlaneige
**Notification Password** vnlaneige  
**HMAC Key**  B2616AC3DBF3602766EDA45BA58086552872B5DF7A97E3FA3F3BEA5EF5941B5A

## Module Installation
```
1.  bin/magento Module:enable Adyen_Payment  
2.  composer require adyen/php-api-library
2.  bin/magento setup:upgrade  
3.  bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```

## Run Cron

1) To process notifications, you need to have cron running on server.

2) The cron job generation time interval for the adyen_payment group is set to 1 minute.

## Configurations

Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to **SALES ⇾ Payment Methods** in the left panel.

![adyen-config](https://nimbus-screenshots.s3.amazonaws.com/s/d32a556c7d91c2ebd245a42f180d024d.png)

Navigate to **Adyen All-in-One Payment Solutions** tab and add configuration values in the following fields and click the Save Config button.

### Required Settings

![required-settings](https://nimbus-screenshots.s3.amazonaws.com/s/ba957922ff3768c7580f9fc028901caa.png)

#### 1) Merchant Account

Add ecommerce merchant account in this field.

#### 2) Test/Production Mode

Select Test mode/Production mode from this field.

#### 3) Notification User Name

Add notification username in this field which we created above in **"Set up notifications""** section.

#### 4) Notification Password

Also add notification password in this field.

#### 5) API key for Test

Add Api key which we generated in above "Generate an API Key" section.

#### 6) Capture Delay

Select option "immediate" from this field.

#### 7) Order status: order creation

Select option "Pending" from this field.

#### 8) Order status: payment authorisation

Select option "Processing" from this field.

#### 9) Order status: payment confirmed

Select option "Processing" from this field.

#### 10) Order status: order cancellation

Select option "Canceled" from this field.

#### 11) Enable debug logging

Select "Yes" if you want to enable logs foor adyen processes.

### Advanced: Security

![advanced-security](https://nimbus-screenshots.s3.amazonaws.com/s/039d6e0f90853220ea48459a24ff32cf.png)

#### 1) Check notification's HMAC signature

Select "Yes" from this field.

#### 2) HMAC key test

Add HMAC key in this filed which we generated in **"Set up notifications"** section's point 8.

### CreditCard API integration

![CreditCard API integration](https://nimbus-screenshots.s3.amazonaws.com/s/9f6abde35e09be4b78b8bc1540f898e0.png)

#### 1) Enabled

Select "Yes" from this field to enable Credit Card option.

#### 2) Credit Card Types

Select desired credit card types

### Note

These are basic configurations to setup Adyen payment method. For adding advanced settings please refer https://docs.adyen.com/plugins/magento-2/set-up-the-plugin-in-magento

## How to pay using Adyen

On checkout payment page **"Credit Card"** option and fill credit card no field, its expiration date, CVC/CVV value and name of caedholder. Then press **"Order"** button to place the order.

![place-order](https://nimbus-screenshots.s3.amazonaws.com/s/0f732cebab6dcdcd1982f612991b3c41.png)

### Test card numbers

##### American Express (Amex)
**Card number** 3700 0000 0000 002  
**Expiry Date** 03/30  
**Expiry Date** 7373

##### Mastercard
**Card number** 5136 3333 3333 3335  
**Expiry Date** 03/30  
**Expiry Date** 737  

- For more test card numbers with details please visit: https://docs.adyen.com/development-resources/test-cards/test-card-numbers

- To create own custom test cards please visit: https://docs.adyen.com/development-resources/test-cards/create-test-cards

## Manage Transactions on Adyen Panel

- To view and manage transactions on Adyen panel please first select the relevent account (either Ecommerce account or POS account) from left side of panel then go to *Transactions* tab and select **Payments**

![merchant-accounts](https://nimbus-screenshots.s3.amazonaws.com/s/801f10acf8cd7ee7d60b21ea2f07aaec.png)

![transactions-1](https://nimbus-screenshots.s3.amazonaws.com/s/7d4f2c62981fc6a73b099f1f9c1b0aea.png)

- All transactions will be list down on screen where you can filters and export these.

![transactions-2](https://nimbus-screenshots.s3.amazonaws.com/s/4eb6de9cdd133a1f72491c9b9efad00c.png)


## Transactions can failed due to some issues 

- If wrong merchant account is used (like using POS account in ecommerce transaction calls) then this issue will occurs "807 Invalid shopper interaction".

- If risk settings are enabled and client shipping address is not valid then transaction will be cancelled with refusal reason: "FRAUD-CANCELLED"

- The complete list of error codes and messages can be seen: https://docs.adyen.com/development-resources/error-codes#generic-error-codes
