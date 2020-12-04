# Eguana News Board

`Website` : Main Website URL 
`Author` : bilalyounas  
`DB Table Name` : eguana_news  
`Listing Page`  : news listing page will be "{Main Website URL}/news" once module is activated'

####Description:

Eguna News Board allows you to manage the date, content and more information about the news.

####Key features:

- Admin can Enable or Disable News, To either show news on front end or not to show.
- Admin can add News Title, News Thumbnail, News Date  News category and Description.
- Admin can design the Description content using page builder.
- Admin can select store at which Admin wants to show News Information.
- All the news Title, Date and thumbnail images added by admin can view on the frontend listing page.
- After click on Title or thumbnail of news list detail page will open of that news and shows news Title, Dates, and the content which designed by Admin in Admin Panel using Page Builder.
- Slider is also show at the end of detail page which show thumbnail of of all news

#Module Installation  
```
1.  php bin/magento Module:enable Eguana_NewsBoard  
2.  php bin/magento setup:upgrade  
3.  php bin/magento setup:di:compile

Refresh the Cache under System­ ⇾ Cache Management
```
 **General Configurations**
  
Navigate to **Stores­ ⇾ Configuration**

![store-config](https://nimbus-screenshots.s3.amazonaws.com/s/b7d0f7098eb8912cea0507737a970139.png)

Navigate to EGUANA EXTENSION ⇾ Event Manager in the left panel.

![event-manager-config](https://nimbus-screenshots.s3.amazonaws.com/s/ab4179d50d573ef0a9995ef099f8fb01.png)

Add configuration Values in the following fields and click the save button.

![config-fields](https://nimbus-screenshots.s3.amazonaws.com/s/6404c9595de1a7c2b896dfd68b35e84e.png)

#####(1) Enable or Disable module

Admin can enable and disable module from Enable Feature.

#####(2) Create Category.

Admin can create and delete categories of news from categories field admin can create categories on store base.

#####(3) No. of news to load

 Add a number in this field. This number will decide how many news will show on one page.

#####(4) news sort type	

 Select one option for news sort type from Ascending and Descending order options.

#####(3) Now Click on Save Config button to save the configuration.

#  Manage News Board
Go to CONTENT > Manage Store Contents and click on News

![manage-evebr](https://nimbus-screenshots.s3.amazonaws.com/s/5bd62b603ee2e97369bbfad30944629a.png)

It will open a Manage News Grid. In the grid all records will be shown which admin add by admin panel.

Example image of an admin grid is below.

![grid](https://nimbus-screenshots.s3.amazonaws.com/s/12ff22b9afa1b3502745a36787ba2139.png)

 **Explore The Grid**
 
## 1) Add New News

Click on Add New News Button.

![add-new-event](https://nimbus-screenshots.s3.amazonaws.com/s/615556c3283097a2278525e5b7aaa859.png)

It will open a form to Add New News.

Below is the example image of the add new form.

![add-nw-form](https://nimbus-screenshots.s3.amazonaws.com/s/a717f2a6cb59c64b7534797e5fd6ef98.png)
![add-nw-form](https://nimbus-screenshots.s3.amazonaws.com/s/9b58c105742fd7f2d269e7d1936c0cca.png)

Explore the every field of add new form in details.

![three-fields](https://nimbus-screenshots.s3.amazonaws.com/s/6582ed0c16a53f16c411e43d415c6593.png)

#####(1) Enable News

This is News main enable/disable button. This will decide either news is enable to show on front end or if disabled it will not show on the front end.

#####(2) News Title

This is News main title. Add News name or title here.
 
#####(3) Thumbnail Image
 
This field is used to add the thumbnail image which will show on listing page. Click on Upload and select an image which will show in the listing page.
 
![dates](https://nimbus-screenshots.s3.amazonaws.com/s/6be372cdf30b2318d7b57b766035194b.png)

#####(4) Date

Click on the calender and it will open a pop-up called date picker as shown in the image below.

![calender](https://nimbus-screenshots.s3.amazonaws.com/s/058ebced5c4d00e126f6c5cc0773c583.png)

and from that date picker select the event start date.


#####(5) Store View

![store-view](https://nimbus-screenshots.s3.amazonaws.com/s/6b92902afb24cabffdc206675b738b19.png)
    
This is news store view. This will decide where news information will shown, on multiple stores or on one store.
    
    - If you have only one store, choose Store View.
    - If you want show this news on multi store,  
    press ctl button and click the stores you want select.
    
#####(6) Category

![store-view](https://nimbus-screenshots.s3.amazonaws.com/s/06c4aeb9aa3654107624b2a48e02b591.png)

Click on to select the category and select categories on the base of selected stores.
    
#####(7) Description

![description](https://nimbus-screenshots.s3.amazonaws.com/s/0166a62bbfbb06b74092e01e77b596bb.png)

This is news description. In this section drag and drop multiple elements from left panel like images, text, heading, blocks or dynamic blocks etc and create an designed content for description.

#####(8) SEO

![description](https://nimbus-screenshots.s3.amazonaws.com/s/60a7bfafadb5430e984abba87386292c.png)

#####(1) URL Key

Add URL identifer or leave it black it will create the identifer according to the News Title

#####(2) Meta Title

Add Meta Title

#####(3) Meta Keywords

Add Meta Keywords

#####(4) Meta Description

Add Meta Description

##### Diffrent Save Buttons

![save](https://nimbus-screenshots.s3.amazonaws.com/s/a7d0a88ad0ae9ba64aabc261dfb2d2ff.png)

At the end click on save button to save the news information.

That new news does not show on frontend listing page without flush the full page cahce.
so after creating the new news you need to flush full page cache. 

There are three different buttons to save the news information

#####(8) Save

Click Save button to save news information.

#####(9) Save & Duplicate

Click Save & Duplicate option to save news information and create a copy of current news we can change some information and add another news.

#####(9) Save & Close

Click Save & Close option to save news information and then close the add new form and it will redirect to admin grid page. 

#####(9) Back

and Back button is used to go back on News Board Manage Grid and it will not save the News information.

## 2) Search by keyword

![search](https://nimbus-screenshots.s3.amazonaws.com/s/fefe056b2884049dee0af46ea8e87693.png)

Search by keyword is used to search specific keyword word available list of record. Just write the keyword in input field and press the enter button. 

## 3) Filters

![filters](https://nimbus-screenshots.s3.amazonaws.com/s/ab3b561a26d8b97265bf4868222dda6a.png)

Filter option is used to search data but in this you can select range of different options. as you can add ID from 5 to 10, it will show data between 5 and 10 IDs. Similarly you can filter data between two created at dates. or two modified dates. Also you can filter data according to the store scope and News status.
Add the parameters and click Apply Filters Button

## Action Column

For news delete, edit and view, go in the last column of Grid **Action** click the **select** Arrow then show two options edit and delete. Where news can be edit or delete or view.

![action](https://nimbus-screenshots.s3.amazonaws.com/s/5ef1cccbace6d90c4fcc9926141b0a73.png)

## 1) Edit 

Edit Action is used to edit the the existing record by clicking edit it opens the form with current data where we can add changes and update the data.

## 2) Delete

Delete Action is used to delete the selected record.

#Events Frontend Examples

The link to view fontend page is {Main Website URL}/news
A submenu is also show in first top menu of wedsite

![action](https://i.ibb.co/257zMG7/Screenshot-from-2020-11-17-15-34-21.png)

  
   **Listing Page**
 
(1) This is the list of all the thumbnails from the news records which were added by admin. also shows the Title, category, Date.

![listing-page](https://nimbus-screenshots.s3.amazonaws.com/s/0536a48814478268adeb1ffdd484b397.png)

(3) Pagination
 
![more-button](https://nimbus-screenshots.s3.amazonaws.com/s/e9176605d1784d4d2f9407f3a606c297.png)


 **Detail Page**

When click on any thumbnail it redirects to the detail page and shows the news information
 
 ![detail-page](https://nimbus-screenshots.s3.amazonaws.com/s/ce6293dcfa6d954aa313bc2d3111f001.png)
 
The upper section of the detail page shows news thumbnail, category, Title , Date.

The middle section of the detail page shows description area which admin build using page builder in add new event form.

And At the end of detail page slider show the thumbnail of all news when click on any image it will redirect its news detail page.

News in slider and listing page show according to news date 

# Test Cases
- That module features are shown at frontend only that time when its is enable from configuration.
- Listing of news are shown on front according to the configuration sortorder
- When we create a new news from admin side when we select a store then categories of only that stores are show in category field
- For Example If we select one store and select two categories against that store then it throw an error and that case also in category
- If URL key is already exist in URl Rewrite table than it show error url key already exist

