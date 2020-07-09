# Eguana Event Manager

`Website` : Main Website URL 
`Author` : Arslan  
`DB Table Name` : eguana_event_manager  
`Listing Page`  : Events listing page will be "{Main Website URL}/events" once module is activated'

####Description:

Eguna Event Manager allows you to manage the date, content and more information about the Events.

####Key features:

- Admin can Enable or Disable Events, To either show an event on front end or not to show.
- Admin can add Event Title, Event Thumbnail, Event Start and End Date and Description.
- Admin can design the Description content using page builder.
- Admin can select store at which Admin wants to show Event Information.
- All the Event's Title, Start and End Date and thumbnail images added by admin can view on the frontend listing page.
- On the listing page Two tabs will show, which diffrentiate Current Events and Previous Events, By clicking the tab Events will display according to there End Date.
- After click one Title or thumbnail from list will open detail page of that Event and shows Event Title, Start and End Dates, and the content which designed by Admin in Admin Panel using Page Builder.

#Module Installation  
```
1.  php bin/magento Module:enable Eguana_EventManager  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```
 **General Configurations**
  
Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION ⇾ Event Manager in the left panel.

![event-manager-config](https://nimbus-screenshots.s3.amazonaws.com/s/02190d42d61f04e9136d80f0bd82d591.png)

Add configuration Values in the following fields and click the save button.

![config-fields](https://nimbus-screenshots.s3.amazonaws.com/s/c443b89ea13a51a2ecf3a84941d4c0ee.png)

#####(1) No. of events to load

 Add a number in this field. This number will decide how many events will load when click on load more button on listing page.

#####(2) Event sort type	

 Select one option for Event sort type from Ascending and Descending order options.

#####(3) Now Click on Save Config button to save the configuration.

#  Manage Event Manager
Go to CONTENT > Manage Store Contents and click on Events

![manage-evebr](https://nimbus-screenshots.s3.amazonaws.com/s/200b77804311dbce9cacc6cae53d7eee.png)

It will open a Manage Events Grid. In the grid all records will be shown which admin add by admin panel.

Example image of an admin grid is below.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/f9ed0ef25bc2e995027034d4451b8fd9.png)

 **Explore The Grid**
 
## 1) Add New Event

Click on Add New Event Button.

![add-new-event](https://nimbus-screenshots.s3.amazonaws.com/s/5ec41a8734a9ba80424f2af1e9903a59.png)

It will open a form to Add New Event.

Below is the example image of the add new form.

![add-nw-form](https://nimbus-screenshots.s3.amazonaws.com/s/96fb3b07e1e37aa88d738dc7613ea6da.png)

Explore the every field of add new form in details.

![three-fields](https://nimbus-screenshots.s3.amazonaws.com/s/05453e4526d8089e1a4589813bdd3dee.png)

#####(1) Enable Event

This is Event main enable/disable button. This will decide either Event is enable to show on front end or if disabled it will not show on the front end.

#####(2) Event Title

This is Event main title. Add Event name or title here.
 
#####(3) Thumbnail Image
 
This field is used to add the thumbnail image which will show on listing page. Click on Upload and select an image which will show in the listing page.
 
![dates](https://nimbus-screenshots.s3.amazonaws.com/s/5f4fbfcf8340d2d5b9236bb2d42ab181.png)

#####(4) Start Date

Click on the calender and it will open a pop-up called date picker as shown in the image below.

![calender](https://nimbus-screenshots.s3.amazonaws.com/s/058ebced5c4d00e126f6c5cc0773c583.png)

and from that date picker select the event start date.

#####(5) End Date

Click on the calender and it will open a pop-up called date picker as shown in the image below.

![calender](https://nimbus-screenshots.s3.amazonaws.com/s/058ebced5c4d00e126f6c5cc0773c583.png)

and from that date picker select the event end date.

#####(6) Store View

![store-view](https://nimbus-screenshots.s3.amazonaws.com/s/6b92902afb24cabffdc206675b738b19.png)
    
This is Event store view. This will decide where Event information will shown, on multiple stores or on one store.
    
    - If you have only one store, choose Default Store View.
    - If you want show this Event on multi store,  
    press ctl button and click the stores you want select.
    
#####(7) Description

![description](https://nimbus-screenshots.s3.amazonaws.com/s/0166a62bbfbb06b74092e01e77b596bb.png)

This is Event description. In this section drag and drop multiple elements from left panel like images, Events, text, heading, blocks or dynamic blocks etc and create an designed content for description.

##### Diffrent Save Buttons

![save](https://nimbus-screenshots.s3.amazonaws.com/s/a7d0a88ad0ae9ba64aabc261dfb2d2ff.png)

At the end click on save button to save the Event information.

There are three different buttons to save the event information

#####(8) Save

Click Save button to save event information.

#####(9) Save & Duplicate

Click Save & Duplicate option to save event information and create a copy of current event we can change some information and add another event.

#####(9) Save & Close

Click Save & Close option to save event information and then close the add new form and it will redirect to admin grid page. 

#####(9) Back

and Back button is used to go back on Event Manager Manage Grid and it will not save the Event information.

## 2) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/b7b9468e3ca4cb09a5f05461877dda94.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/6d1e781f2466e3c4a93ab1714d70cd52.png)

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. or two modified dates. Also you can filter data according to the store scope and Event status.
Add the parameters and click Apply Filters Button

## Action Column

For Event delete and edit, go in the last column of Grid **Action** click the **select** Arrow then show two options edit and delete. Where Event can be edit or delete.

![action](https://nimbus-screenshots.s3.amazonaws.com/s/baf9b57544aa78b2646032a1cb8022c2.png)

## 4) Edit 

Edit Action is used to edit the the existing record by clicking edit it opens the form with current data where we can add changes and update the data.

## 5) Delete

Delete Action is used to delete the selected record.

#Events Frontend Examples

The link to view fontend page is {Main Website URL}/events
  
   **Listing Page**
 
(1) This is the list of all the thumbnails from the Event records which were added by admin. also shows the Title, Start and End Date.

![listing-page](https://nimbus-screenshots.s3.amazonaws.com/s/8bbedc1aedbb3bd9f78b7ff250090eba.png)

(2) There are two tabs on the listing page which shows 2 differet events.
    
    - Current Events
    - Previous Events

![events](https://nimbus-screenshots.s3.amazonaws.com/s/7c7314dcfae0f49f5b967e37e670670e.png)
    
   **Current Events**
    
Here only those events will show which date period is already started and until now did not end
    
   **Previous Events**
    
Here only those events will show which date period has been passed.

(3) Load More Button
 
![more-button](https://nimbus-screenshots.s3.amazonaws.com/s/2efa4cc70210248121d883927490740d.png)

(3) This button is used to load more thumbnails. And the number of thumbails to load will be added in general configuration section.

 **Detail Page**

When click on any thumbnail it redirects to the detail page and shows the Event's information
 
 ![detail-page](https://nimbus-screenshots.s3.amazonaws.com/s/5c2b5318041815b6603ba47beebf337b.png)
 
The upper section of the detail page shows Event Title, Start Date and End Date.

The middle section of the detail page shows description area which admin build using page builder in add new event form.

And Back button is used to redirect back to the listing page.
