Eguana_CustomRMA v1.0.0

Website : Main Website 
Author : Mobeen Sarwar
Explanation :
# CustomRMA

Description: Disable Partial Return Request from frontend and allows only full order return.

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_CustomRMA --clear-static-content

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management


Requirement:

**Configuration**

1. Navigate to Stores ⇾ Configuration and click on **CustomRMA** under **Eguana Extensions** tab in the left panel.

**i)**  **General Configuration**

- **●●**** Enable Extension**

This will decide either enable/disable StoreSms Module.

When module will be enabled the return order page will show only two fields 

i) Contact Email Address
ii) Return Order Reason

![](https://i.ibb.co/KWM3nj4/image.png)

Enter the reason and submit the form. The Complete order will be returned.

