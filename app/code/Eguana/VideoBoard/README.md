# Eguana Video Board

`Website` : Main Website URL 
`Author` : Arslan  
`DB Table Name` : eguana_video_board  
`Listing Page`  : Video Board listing page will be "{Main Website URL}/videoboard" once module is activated'

####Description:

Eguna Video Board allows you to manage the (How To) Videos.

####Key features:

- Admin can add Add Video's URL, Title and Description.
- Admin can design the Description content using page builder.
- Admin can set the Video's sort order from Ascending and Descending order options.
- All the Video's thumbnail added by admin can view on the frontend listing page.
- After click one thumbnail from list will open detail page of that video and loads video URL.

#Module Installation  
```
1.  php bin/magento Module:enable Eguana_VideoBoard  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```

#Module Configuration
  
**Add New Block**

Add a new block and add banner image in the block (This block is used to show Banner at the top of video's listing page).

Add new block guide is following.

Navigate to **CONTENT ⇾ Blocks**

 ![menu](https://nimbus-screenshots.s3.amazonaws.com/s/e98c10ae1a659c74ca178a2abc5fd000.png)

Click on Add New Block

 ![block](https://nimbus-screenshots.s3.amazonaws.com/s/449f8f284a4446778235de032d622d09.png)

Add the Block information

![add-block](https://nimbus-screenshots.s3.amazonaws.com/s/1f5c4851b3d2b7a8e4192c05ea542757.png)

#####(1) Enable Block
 
 This is block main enable/disable button. This will decide either block is enable or disabled.
 
#####(2) Block Title

 This is block main title. Add block name or title.
 
#####(3) Identifier

 This is block identifier. Identifier should be unique. This unique identifier is added in the Configuration. (Explained in the General Configuration Section) 

#####(4) Store View

 This is block store view. This will decide where block will show in multiple stores. All store view selection will show block on all stores. select one or two unique stores dependes upon requirement.
 
![add-block](https://nimbus-screenshots.s3.amazonaws.com/s/d5d6c3e75dfe0fc6930464ddc9df5eaf.png)
![add-image](https://nimbus-screenshots.s3.amazonaws.com/s/93fd67194752d6dd9af56f9ad4ffa5fd.png)
 
#####(5) Description

 This is block description. In this section drag and drop an image element from left panel and upload a banner image.

![save](https://nimbus-screenshots.s3.amazonaws.com/s/2317ca465a604cb904db52ecca4f7334.png)

 Now Click on save button to save the block.
#
 **General Configurations**
  
Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION ⇾ Video Board in the left panel.

![video-board](https://nimbus-screenshots.s3.amazonaws.com/s/f882cf47a52afc8e9cbafaa212023946.png)

Add configuration Values in the following fields and click the save button.

![video-config](https://nimbus-screenshots.s3.amazonaws.com/s/5bd8df9650237d396fc2230eba912ac6.png)

#####(1) Video Board top banner Id

 Add block's identifier in the input field name Video Board top banner Id.

#####(2) Video sort type	

 Select one option for Video sort type from Ascending and Descending order options.

#####(3) Now Click on Save Config button to save the config.

#  Manage Video Board
Go to CONTENT > Video Board and click on Manage Video Board

![manage-board](https://nimbus-screenshots.s3.amazonaws.com/s/ff43b2464c1cbf522a5b55d956851629.png)

It will open a Manage Video Board Grid. In the grid all records will be shown which added by admin.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/d5b63505f59231d5b642d5645e8b2cab.png)

 **Explore The Grid**
 
## 1) Add New Video

![add-new-video](https://nimbus-screenshots.s3.amazonaws.com/s/a42661a48783a3e6b88ac4c16ae7406e.png)

It will open a form to Add New Video to add a new record

![add-nw-form](https://nimbus-screenshots.s3.amazonaws.com/s/89696961c23c237fc3ecd864998de449.png)

#####(1) Enable Video

This is Video main enable/disable button. This will decide either Video is enable or disabled.

#####(2) Video Title

This is Video main title. Add Video name or title here.
 
#####(3) Video URL
 
This is Video URL. Copy Title from https://www.youtube.com and paste the URL here.
 
 ![upload-image](https://nimbus-screenshots.s3.amazonaws.com/s/98097ea678a7996bd5a1e09a61e95efd.png)

#####(4) Thumbnail Image

This field is used to add the thumbnail image which will show on listing page. Click on Upload and select an image which will show in the listing page.

#####(5) Store View
    
This is Video store view. This will decide where Video information will show in multiple stores or on one store.
    
    - If you have only one store, choose Default Store View.
    - If you want show this video on multi store,  
    press ctl button and click the stores you want select.
    
![description](https://nimbus-screenshots.s3.amazonaws.com/s/73634bb887fcc7d4f40ec54bb7bd334d.png)

#####(6) Description
    
This is Video description. In this section drag and drop multiple elements from left panel like images, videos, text, heading, blocks or dynamic blocks etc and create an designed content for description.

![save](https://nimbus-screenshots.s3.amazonaws.com/s/2d6ae60a0cb96bfd5e324deca02b4c7c.png)

At the end click on save button to save the video information. and Back button used to go back on Video Board Manage Grid and it will not save the video information.

## 2) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/12a1e0fba99eb9f92ec405a2d89eac31.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/36ce1a284c94a4bb0742f85deef91b5e.png)

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. or two modified dates. Also you can filter data according to the store scope and video status.
Add the parameters and click Apply Filters Button

## 4) Delete and Edit 

For Video delete and edit, go in the last column of Grid **Action** click the **select** Arrow then show two options edit and delete. Where video can be edit or delete.

 ![delete](https://i.ibb.co/xfNWCVs/Edit-and-delete.png)

#Video Board Frontend Examples

The link with to view fontend page is {Main Website URL}/videoboard
  
   **Listing Page**
  
Video banner image block and All the thumbnail images added by admin will show on the listing page.

![banner](https://nimbus-screenshots.s3.amazonaws.com/s/91c2bb540511066b89858cad3d974f7d.png)

(1) This is banner block which we added in content blocks and then add its identifier in the store configurations.

![thumbnails](https://nimbus-screenshots.s3.amazonaws.com/s/039fa016b4fa67d20c0c0687fed8e2a5.png)

(2) This is the list of all the thumbnails from the video records which were added by admin.

![more-button](https://nimbus-screenshots.s3.amazonaws.com/s/2efa4cc70210248121d883927490740d.png)

(3) This button is used to load more 6 thumbnails.

 **Detail Page**

When click on any thumbnail it redirects to the detail page and shows the video of URL that added by admin.
  
![detail-page](https://nimbus-screenshots.s3.amazonaws.com/s/411c454ad37ecc7b43362463f6592a63.png)

This section of the detail page shows Video Title, Created Date and Load Video by URL.

![content](https://nimbus-screenshots.s3.amazonaws.com/s/c24188aca807db6d318a6f7d46ced71c.png)

This section shows the description area which admin build using page builder in add new video form.

And Back button is used to redirect to back to the listing page.
