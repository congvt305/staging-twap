Amore_GcrmSegment v1.0.0

Website: Amore

Author: Sonia Park

DB Table Change : 
 - magento_customersegment_segment :
   - is_remote (smallint) added
   - remote_code (varchar, 255) added

Explanation: This module allows the user create a magento customer segment which is synchronized with the remote GCRM customer segment, reading customer segment data from the postgresql DB table.

# GcrmSegment

Description:

This module allows the user create a magento customer segment which is synchronized with the remote GCRM customer segment.

Requirements:

    - User should be able to login to admin panel

Key features:

      1. User can setup a customer segment which synchronized with GCRM customer segment
      2. User can configure GCRM customer segment code(or id) in magento backend.
      3. User can confirm GCRM customer segment users in the backend so that he can use the segment in the promotions and dynamic blocks.

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_GcrmSegment

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management


**General Configuration**

1. Navigate to **Stores ⇾ Configuration ⇾ Amore Extensions ⇾ GCRM Integration**.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/4a7a6642b8f3537f1a284fdc9840a523.png)

* Enable the Extension

Select "Yes" on "Enable Extension" for enabling/disabling the module

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/193314351e0783ae344b2edf922d4f4e.png)

**Remote GCRM Segment Configurations**

1. Navigate to **Customers ⇾ Segments ⇾ Add Segment -> General Properties**.

2. Select "Is Remote" to "Yes".

3. Enter GCRM Customer Segment ID in the "GCRM Segment ID" field. (Precondition : User knows the GCRM Segment ID to configure.)

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/a4265801d3b36c6fc93030e5540deab3.png)

4. Click "Save and Continue Edit" to save the segment or "Refresh Segment Data" button after saving the segment.
   
5. Click "Matched Customers" tab to confirm matched GCRM customers for the segment.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/5202f5c4058c61a44f1cbbe116bf79d6.png)

