Eguana_EcommerceStatus v1.0.0

Website: Amore

Author: Muhammad Yasir 
Explanation: This module will Enable/Disable ecommerce functionalities process according to the Amore requirements

# E-Commerce Enable/Disable

Description:

-Register module will be mainly used to Enable/Disable ecommerce functionalities
-Hide mini cart when ecommerce switcher is disable
-Redirect cart page to home page when ecommerce switcher is disable
-Redirect checkout page to home page when ecommerce switcher is disable
-Hide add ot cart buttons


Requirements:

Key features:

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_EcommerceStatus

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** Eguana Extension **E-commerce Status


**Configuration**

1. Navigate to **Stores ⇾ Configuration** Eguana Extension  **E-commerce Status**


![](https://i.ibb.co/Bs2pmMk/Screenshot-2.png)

**IMPORTANT NOTE:**

Please run indexer command bin/magento indexer:reindex after enable or disable this module so that changes apply to
the products that are in content blocks, widgets and other pages etc.
