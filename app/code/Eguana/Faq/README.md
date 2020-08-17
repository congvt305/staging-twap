# Eguana FAQ

`Website` : Main Website URL 
`Author` : Arslan  
`DB Table Name` : eguana_faq  
`Listing Page`  : FAQ listing page will be "{Main Website URL}/faq" once module is activated'

####Description:

Eguana FAQ allows you to manage Frequently Asked Questions in admin panel. And shows on user end (frontend listing page).

####Key features:
 
 - Admin can Enable or Disable FAQs, To either show an FAQ on front end or not to show.
 - Admin can add six different categories in store configuration. 
 - These categories are used as groups. Every group have their title or heading related questions.  
 - Admin can select store at which Admin wants to show FAQ Information.
 - All the Categories, FAQ's Title and Description added by admin can view on the frontend listing page.
 - By Clicking the FAQ's Title it shows the description (or Answer) of that title which were added by admin.
 - End user can search the FAQs. 
 
#Module Installation  

```
1.  php bin/magento Module:enable Eguana_Faq
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```

#General Configurations

First of all add FAQ categories in Admin Panel by Navigate to **Stores­ ⇾ Configuration**

![config](https://nimbus-screenshots.s3.amazonaws.com/s/9fdf0afa5d0a2a3be7728e848365f2ec.png)

Navigate to **EGUANA EXTENSION ⇾ FAQ** in the left panel.

![faq-config](https://nimbus-screenshots.s3.amazonaws.com/s/594b0fb891563b90f949e01beaa644d7.png)

It will open configuration window for FAQ as shown in the image.

![faq](https://i.ibb.co/ggmWDWn/image.png)

There are two groups.

    - General 
    - FAQ Category

(1) By clicking general tab module's Enable/Disable configuration will show.
    **Faq Sort type**
    User can sort the faq listings based on ascending and descending order.
        
(2) By clicking FAQ category six fields will show were categories name or title will be added.

(3) By default these fileds are disabled and unable to add or change any value. To be able to add or update values just uncheck the Use system value checkbox as shown the image.

![system-value](https://nimbus-screenshots.s3.amazonaws.com/s/3d7dac41e69bb0c1fc7d9ef47ae7a7ec.png)

(4) Now Click on Save Config button to save the configuration.

#  Manage FAQ

Navigate to **CONTENT ⇾ Manage Store Contents ⇾ FAQ** and click on FAQ

It will open a Manage FAQ Grid. In the grid all records will be shown which were added by admin.

![faq-grid](https://nimbus-screenshots.s3.amazonaws.com/s/8f688fdbe3b2ea02ae26827bcc5fcf30.png)

 **Explore The Grid**
 
 ## 1) Add New Faq
 
 ![add-new](https://nimbus-screenshots.s3.amazonaws.com/s/ee828093b5836b12caa0eef7929b1c60.png)
 
It will open a form of Add New Faq to add a new record. As shown in the image below

![add-new-form](https://nimbus-screenshots.s3.amazonaws.com/s/060861b9b40bb2599050e127fb0c217d.png)

 **Explore The Add New Form**
 
 ![first-part](https://nimbus-screenshots.s3.amazonaws.com/s/9bc62dbcd83139b9d27b3bd596853ed3.png)
 
#####(1) Enable Faq

This is FAQ main enable/disable button. This will decide either FAQ is enable to show on front end or if it is disabled it will not show on the front end.

#####(2) Store View

This is FAQ store view. This will decide where FAQ information will shown, on multiple stores or on one store.
    
    - If you have only one store, choose Default Store View.
    - If you want show this FAQ on multi store,  
    press ctl button and click the stores you want select.
    
![second-part](https://nimbus-screenshots.s3.amazonaws.com/s/abdca8b3c8920ff537fe2bb5b77e0f2e.png)

#####(3) Category

In this section those categories will listed which were added in general store configuration section.

Click on drop down and it will show all the categories which were added in configuration. see below image

![config-category](https://nimbus-screenshots.s3.amazonaws.com/s/541d9843b287689c3f9950c1b91daee9.png)

Every category will show on all store and if we want to show the category related information on both stores. We have add FAQ two times, One for first store and second time it will be for second store.

#####(4) Faq Title

This is FAQ main title. Add FAQ title or Question statement here.

#####(5) Description

![description](https://nimbus-screenshots.s3.amazonaws.com/s/e6dd39e378c4936a454b5d9a842c9666.png)
    
This is FAQ description. In this section drag and drop multiple elements from left panel like images, videos, text, heading, blocks or dynamic blocks etc and create an designed content for to explain the FAQ Answer or description.

#####(6) Save Faq

![save-button](https://nimbus-screenshots.s3.amazonaws.com/s/471ccaa87c69f02e7b8f1aabf8df59e2.png)

At the end click on save button to save the FAQ information. While the Back button is used to go back on Manage FAQ Grid and it will not save the FAQ information.

## 2) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/f15d331555c161e5dbaf9d66ff5047a3.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/e16f8e66540fede106061084d511d64e.png)

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. or two modified dates. Also you can filter data according to the store scope and FAQ status.
Add the parameters and click Apply Filters Button.

## 4) Delete and Edit 

For FAQ delete and edit, go in the last column of Grid **Action** click the **select** Arrow then show two options edit and delete. Where FAQ information can be edit or delete.

 ![delete](https://i.ibb.co/xfNWCVs/Edit-and-delete.png)
 
 #FAQ Frontend Examples
 
 The link to view fontend page is {Main Website URL}/faq
   
 **FAQ Page**
 
 Here is the example of the front view of FAQ page where all the questions list will show.
 
 ![front-view](https://nimbus-screenshots.s3.amazonaws.com/s/cd13808b1ff5bc8e291381ab270d76ba.png)
 
  **Explore The front page**
  
 ![search-faq](https://nimbus-screenshots.s3.amazonaws.com/s/276115f571a9bc7586580bcd33bd01a7.png)
 
 (1) This is search area where user can add any query or question he wants to search. Just add any query and click on search button.
 
 (2) Search button.
 
 **Showing Criteria**
 
 ![config-cat](https://nimbus-screenshots.s3.amazonaws.com/s/61d4bf1f1aaffd6b069d4caaf435e3cc.png)
 
 (3) This is the category which were added by admin in the general configuration section. And there must be atleast one category. That one category will be shown in two store view in admin panel.
     And on front end it will be displayed based on which store's category selected when adding FAQ from Admin panel. 
 
 ![answer](https://nimbus-screenshots.s3.amazonaws.com/s/fd56e206793fe5018bdf3365df4b75f8.png)
 
 (4) This is the FAQ title which were added by admin in add new faq section. By clicking on this title it will opens the description area in which asked question's answer will be available which were explained by admin.
 
 (5) Description area.
