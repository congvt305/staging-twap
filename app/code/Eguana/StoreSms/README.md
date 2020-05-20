Eguana_StoreSms v2.0.0

Website: Amore

Author: Abbas Ali Butt

DB Table Name :
 
Explanation: This module will send the SMS on different events in the website

# StoreSms

Description:

StoreSms module will be mainly used for any sort of SMS communication between website and customers.

Requirements:

- Module should have one interface for other modules to send the SMS against their actions.

Key features:

- Send verification code up to 4 digits on customer create an account and validate this code for registration
- Notify the user on Order status change like pending, processing, hold etc
- Notify the user on Return request rejected, authoriezed or approved
- Admin can enable or disable the module
- Admin can enable or disable Verification code on registration
- Admin can enable or disable order notification for a specific order status
- Admin can edit the SMS template according to SMS type from the admin panel

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_StoreSms

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Store SMS** under Eguana tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Store SMS** under Eguana tab in the left panel.