# Amore Gcrm Data Export

`Website:` Amore  
`Author:` Adeel Ahmad, Raheel Shaukat  
`DB Table Name:` eguana_gcrm_data_export_setting

## Description: 
This module will allow the user to schedule the export of Customers, Customer Addresses, Products, Orders, Order Items, Quotes, Quote Items Data. And also when customer will signup or update his information that will be added or updated in external postgres DB 
as well.

## Requirements:

    - User should be able to login to admin panel

## Key features:

      1. User can schedule custom export at any time
      2. Admin can run custom export manually
      3. User can upload export files on local/FTP Server

## Module Installation
```
Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_GcrmDataExport
2.  php bin/magento setup:upgrade
3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management
```

## System Configuration

1. Navigate to **Stores ⇾ Configuration ⇾ Amore Extensions ⇾ GCRM Integration**.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/4a7a6642b8f3537f1a284fdc9840a523.png)

### General Configuration

![general-configurations](https://nimbus-screenshots.s3.amazonaws.com/s/3f5b044c275efba114461e66f122e6d1.png)

1) **Enable Logging**

    We can click on enable logging for enabling/disabling logs

2) **Enable Extension**

    To enable or disable this extension this field is used.

### Heroku Database Configurations

![heroku-configurations](https://nimbus-screenshots.s3.amazonaws.com/s/bbf4f82b3fbe5f620d3f6776d90d9654.png)

We will add our information related to external (Heroku) DB here to configure it with our module

1) **Host:** Host name or IP Address
2) **Database Name:** External (Heroku) database name
3) **User:** Username for database
4) **Port:** Port on which running
5) **Password:** Password for database

### Banner Configuration

![banner-onfiguration](https://nimbus-screenshots.s3.amazonaws.com/s/5c198ea784f145a6986970f0a4cf0a9f.png)

By this configuration we can show hide banner for customer on frontend side.

1) **Enable GCRM Banner:** To enable/disable banner functionality on frontend.

### Scheduled Imports/Exports Configurations

![scheduled-onfiguration](https://nimbus-screenshots.s3.amazonaws.com/s/f435548773e9850b72988167f534184a.png)

Using this configuration we can limit the Sales Order Items entity data. By entering order limits here only that no of order's items will be exported to the file.

1) **Orders Limit for Items Export:** If field is empty then all items will be exported to file else on basis of this limit order's items will be exported only.


### Schedule Export Settings

![export-settings](https://nimbus-screenshots.s3.amazonaws.com/s/ee1c11cd995ebb4282c03b9e0334a419.png)

The schedule related to entities Order, Order Items, Quote, Quote Items will load only data which is ahead to the date which is saved in this table till current date time.

**Note: If to load all data from start till to date you only need to set updated_at values to past years e.g set order field data 2000-01-01 00:00:00**

## Schedule Import/Export Configurations

1. Navigate to **System ⇾ Data Transfer ⇾ Scheduled Imports/Exports**.

![schedule](https://nimbus-screenshots.s3.amazonaws.com/s/3ca80a5332dce8c87ae9dfa220392a94.png)

### Explore the Grid

![schedule-grid](https://nimbus-screenshots.s3.amazonaws.com/s/5cf8b04856cfe2bdfac293f22925a1fd.png)

1. To manage the existing schedule select **Edit** from **Action** column.
2. To run the schedule manually select **Run** from **Action** column. It will run the schedule and export the file.
3. To add the new export schedule click "Add Scheduled Export". It will open form. Please fill the required fields and click Save button present on the top right.

![schedule-form](https://nimbus-screenshots.s3.amazonaws.com/s/472969b12144a6af8f20a7e4382ca8f8.png).
             
#### Export Settings

1. **Name:** Add the schedule name
2. **Description:** Add the description related to schedule.
3. **Entity Type:** Select the entity type from the dropdown. Can be Cusomer, Product, Order etc.
4. **Start Time:** Set the schedule time.
5. **Frequency:** Select the frequency e.g Daily, Weekly, Month.
6. **Status:** You can enable or disable the schedule by this field.
7. **File Format:** Select the export file format.
                      
#### Export File Information

**Server Type:** Seletc server type where the file will be exported. 
   1. If "Local" server is selected then "File Directory" field will be shown in which add path to the directory where file will be exported.   
   2. If "Remote FTP" or "Remote SFTP" then following fields will be shwon:
   3. **Remote File Prefix:** Add prefix if you want to add with the exported file.
   4. **File Directory:** Add path to the directory where file will be exported.
   5. **FTP Host[:Port]:** Add host with port.
   6. **User Name:** Remote host user name.
   7. **Password:** Add remote host password.
   8. **File Mode:** Select the file mode.
   9. **Passive Mode:** Passive Mode yes or no.
                                             
#### Export Failed Emails

If the schedule is failed then email will be sent to the relevant person whose email is given in the below configuratioins:

1. **Failed Email Receiver:** Select the receiver type to whom the email is sent.
2. **Failed Email Sender:** Select the email sender. (By selecting any value from this field the email of that user is used which is set in the general configurations of Magento Panel).
3. **Failed Email Template:** Select the email template which will be used to sent.
4. **Send Failed Email Copy To:** Email address of receiver.
5. **Send Failed Email Copy Method:** Select copy method.
