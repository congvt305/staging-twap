Eguana_MobileLogin v1.0.0

Website: Amore

Author: Muhammad Umer Farooq

DB Table Name :
 
Explanation: This module will customize the login process according to the Amore requirements

# Mobile Login

Description:

Register module will be mainly used to customize the user login process

Requirements:

    - User should be able to login via mobile number or email
    - Mobile number length should be between 10 and 11
    - User should be able to login on customer login page via mobile number
    - User should be able to login on social login page via mobile number

Key features:

      1. Admin can enable disable whole module
      2. User can login by using mobile number or email

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_Login

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Mobile Login** under Eguana tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Mobile Login** under Eguana tab in the left panel.

    In General Setting User can Enable or Disable the module

![](https://i.ibb.co/zf75R5m/mobileconfig.png)

**Frontend**   

Go to customer login page and login using mobile no or email

![](https://i.ibb.co/NZFnMB1/frontend.png)

Go to social login page and login using mobile no or email

![](https://i.ibb.co/3c64NJq/mobieloginsocial.png)
