Amore_GcrmBanner v1.0.0

Website: Amore

Author: Sonia Park

DB Table Change : 
 - magento_banner :
   - is_remote (smallint) added
   - remote_code (varchar, 255) added
 - salesrule_coupon : 
   - customer_id (int)

Explanation: This module allows the user create a dynamic block for a specific GCRM segment and allows a customer generate a coupon code of a cart price rule which is linked with the dynamic block. This module has a dependency of Amore_GcrmSegment module. Creating dynamic block and cart price rule and linking them together follow Magento default behavior. 

# GcrmBanner

Description:

This module allows a user create a dynamic block for a specific GCRM segment and displays a drawer style popup in the frontend when the customer logged in, allowing the customer to generate a coupon code of a cart price rule which is linked with the dynamic block.

Requirements:

    - User should be able to login to admin panel.
    - User has a GCRM segmnt ID which wish to configure.
    - User has configured a segment whose GCRM segmnt ID is same as dynamic block's.
    - User has configured a cart price rule to link the dynamic block and configured a condition to target same customer segment.
    - Target Customer should logged in to see the dynamic block(banner).

Key features:

      1. User can setup a dynamec block which can target a specific GCRM customer segment.
      2. Target segment customer can see a dynamic block which linked with a cart price rule.
      3. Target segment customer can generate a coupon code for a specific cart price rule by clicking a "generate coupon" button to use it in the checkout.

Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Amore_GcrmBanner

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management
Need to reindex manually or wait for next reindexing schedule. (It follows Magento Native Reindexing process.)


**General Configuration**

1. Navigate to **Stores ⇾ Configuration ⇾ Amore Extensions ⇾ GCRM Integration**.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/4a7a6642b8f3537f1a284fdc9840a523.png)

* Enable the Extension

Select "Yes" on "Enable GCRM Banner" for enabling/disabling the module

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/d51b0917daccb2dcc51074b6d5ba7291.png)

**Remote GCRM Banner Configurations**

1. Prerequisite : See Requirements section above.

2. Navigate to **Content ⇾ Dynamic Blocks ⇾ Add Dynamic Block**.

3. Select preconfigured customer segment whose GCRM Segment ID is same as the dynamic block's.

4. Select "Is GCRM Banner" to "Yes".

5. Enter GCRM Customer Segment ID in the "GCRM Segment ID" field. (Precondition : User knows the GCRM Segment ID to configure.)

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/e3179d5a22c089c6b79ea7daebd72577.png)

6. Edit content with the Page Builder, create a simple image banner.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/986082db9328d25be88d71eb88c4b994.png)

7. Click "Related Promotions" tab and add a preconfigured cart price rule in the "Related Cart Price Rule" whose target customer segment is for the same GCRM Segment ID. (Precondition : User has configured a cart price rule to link the dynamic block which is configured a condition to target the same customer segment.)

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/3c6899da0fec5fe9b43395f697bfca12.png)

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/3ad7f9c1d0462e8aea3773be0831040e.png)



**Generating Dynaic Coupon Code**

1. A customer logged in the frontend.

2. The customer belongs to a preconfigured customer segment which is mapped to GCRM customer segment.

3. Admin User has configured a dynamic blocks and a cart price rule which is targeting the segment.

4. A drawer style banner is displayed.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/5af9ceb059d552c7ee3d8d083aa288cd.png)

5. When customer open the drawer, customer will see the banner with "generate coupone" button.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/eb28e219f467aaa5f21cfe02f834ea1c.png)

6. When customer clicks the "generate coupone" button customer will see the coupon code which has generated dynamically that can be used for the related cart price rule.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/99ea0541777825296b72fc71fb62512c.png)

6. Admin user also can confirmed the coupon code generated in "Manage Coupon Code" section of the related cart promotion edit page in the backend. Coupon code generating rule is GCRM_{Rule Id}_XXX_XXX_XXX.

![Configuration](https://nimbus-screenshots.s3.amazonaws.com/s/1c9e7ad495767eecdbefbb60a7f6b7bd.png)

7. All the feature that are not mentioned here follows Magento Default behavior.






