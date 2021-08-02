Amore_GcrmDataExport v1.0.0

Website: Amore

Author: Adeel Ahmad

DB Table Name : eguana_gcrm_data_export_setting

Explanation: This module will allow the user to schedule the export of Orders, Order Items, Quotes, Quote Items Data
and also when customer will signup or update his information that will be added or updated in external postgres DB 
as well

# GcrmDataExport

Description:

This module will be used by users to schedule their exports in Admin panel and send customer Data after saving
and updating in external postgres DB

Requirements:

    - User should be able to login to admin panel

Key features:

      1. User can schedule custom export at any time
      2. Admin can run custom export manually
      3. User can upload export files on local/FTP Server

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_GcrmDataExport

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management


**Configuration**

1. Navigate to **System ⇾ Scheduled Imports/Exports**.

![image](https://i.ibb.co/HYQGNpn/enable.png)


click on **Add Scheduled Export** on right side.

![Configuration](https://i.ibb.co/hYLjrtw/social-login.png)

**General Configuration**

1. Navigate to **Stores ⇾ Configuration ⇾ Amore Extensions ⇾ GCRM Integration**.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/4a7a6642b8f3537f1a284fdc9840a523.png)

* Enable Logging

We can click on enable logging for enabling/disabling logs

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/a193d1bf1161bcf6386f1292d24c13af.png)

**Heroku Database Configurations**

We will add our information related to external DB here to configure it with our module

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/bbf4f82b3fbe5f620d3f6776d90d9654.png)
