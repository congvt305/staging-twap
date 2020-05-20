Eguana_SocialLogin v2.0.0

Website: Amore

Author: Abbas Ali Butt

DB Table Name :
 
Explanation: This module will allow the customer to login/register using social media

# SocialLogin

Description:

Register module will be mainly used to allow the customer login/register using Facebook, Gmail and Line

Requirements:

    - User should be able to register using Facebook, Google, or Line
    - User should add the missining information which related social media API do not provide
    - Next time custoemr should be able to login without social media


Key features:

      1. Admin can enable or disable from Facebook, Google or Line Login options
      2. Admin can enable disable whole module
      3. Same social media user should not register again. 

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_SocialLogin

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management

Navigate to **Stores ⇾ Configuration** and the module **Social Login** under Eguana tab.


**Configuration**

1. Navigate to **Stores ⇾ Configuration** and click on **Social Login** under Eguana tab in the left panel.