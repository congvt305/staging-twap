# Eguana Magazine

`Website` : Main Website 
`Author` : Muhammad Yasir
`DB Table Name` : eguana_magazine

##Ultimate Guide for Magento 2 Eguana Magazine

##Description

This module will be mainly used to Manage Banner, video and image magazine of current month and previous months. Show banner magazines on the top of the Page then show image magazines, video magazines and previous month magzines.



##Module Installation

Download the extension and Unzip the files in a temporary directory

Upload it to your Magento installation root directory

Execute the following commands respectively:

1.  php bin/magento module:enable Eguana_Magazine

2.  php bin/magento setup:upgrade

3.  php bin/magento setup:di:compile

Refresh the Cache under System ⇾ Cache Management




## Manage Magazine
Go to **CONTENT** -> **Manage Magazine** -> **Manage Magazine**
 ![menu](https://i.ibb.co/9NFNkfm/r1.png)

You will see all magazines that you entered in this grid.

 ![Empty Grid](https://i.ibb.co/9wWRWLM/Empty-grid.png)

First of All we add new Magazine

## Add New Magazine
1.  Click **Add New Magazine**.

![Empty Grid](https://i.ibb.co/GH9TNSf/Add-New.png)

2.  When click Add new Magazine button then form will be appear.

![Empty form](https://i.ibb.co/NnpGzVx/Empty-form.png)

3. Select the date by date picker in which month you want to show magazine. Date picker is required for every magazine.
If selected date is of the current month then it will show on the Page. Otherwise it will be in the monthly collection that show in the slider.
![Show date](https://i.ibb.co/YkKXym1/show-date.png)
![Show date](https://i.ibb.co/PMTzkCW/show-date-1.png.png)

4. Click the toggle button for enable/disable magazine. Make sure magazine is enabel otherwise it will not show on front view.

5. Write down the title of Magazine.
![Show date](https://i.ibb.co/D9WxBwt/Enable-and-Magazine-title.png)

6. Enter sort order. All magazines are display on front view on the basis of sort order. If magazine type is banner and sort order is 1, then banner show in 
the slider otherwise not show in the slider on the front view.

![Sort order](https://i.ibb.co/7R55M2W/Sort-order.png)

7- Upload Magazine image. 

8- Write down the alt for image. (If magazine image is not available then show this instead of image.)

9- Enter short description about related magazines.

![image](https://i.ibb.co/BsCmk3c/Upload.png)

10- Select the type of magazine use this drop down menu. By default type is select of main banner. If magazine type is different click on arrow sign and select another type of magazine.

![type](https://i.ibb.co/1zVTrX0/type.png)

11- Select Store view to show magazine.
            - If you have only one store, choose Default Store View.
            - If you want show this magazine on multi store,
            press ctl button and click the stores you want select.

![sort view](https://i.ibb.co/jDCjNLk/Sort-view.png)

In our case we have two store view. We select one store view or select both store view. Which store view is selected image show only this store view.

Write Content using PageBuilder. (This content will be used for detail page.)

![Content](https://i.ibb.co/Jpw8fy3/Content.png)

Now we add new Magazine of banner type as a sample and fill all fields.

![Form fill](https://i.ibb.co/DVHjGzD/Form-fill.png)

12- Click **Save** button to save Magazine at the right top of the window. Now click on **Back** and see the magazine data in the grid.

In the same way enter other magazine and select another type like video and image. Then you click **Back** Button. Now your all magazines data show in the grid.

 ![Grid](https://i.ibb.co/mH9wmX1/Grid-with-magazine.png)

In the above grid different types of Magazine with different dates are available.

#Grid Review

Many magazines are available in the grid, magazines can be filtered as requirement.

1- Click on **Filters** button, show different options for filter the magazines. Magazines can be filtered by ID, Title, Type, Created Date, Updated date, Sort Order, Short Desciption, Status, store view and alt.

##Column

2- Grid columns can be hide/show by click on column arrow .

 ![Options](https://i.ibb.co/sFxpYh3/opitons.png)

##Delete and Edit 

For Magazine delete and edit, go in the last column of Grid **Action** click the **select** Arrow then show two options edit and delete. Where magazine can be edit or delete.

 ![Delete](https://i.ibb.co/xfNWCVs/Edit-and-delete.png)

For Magazines delete, select the magazine, click on Mass Action and then delete. 

##Front View

##Banner Magazine

First of all show banner magazine on the top of the front which **show date** adjust accoding to current month.

 ![Banner](https://i.ibb.co/tzt0jnG/Banner.png)
 
if we add more banner then these banner show in the below of first banner according to sort order.
 
 ![Two banner](https://i.ibb.co/ZLhJMsp/two-banner.png)
 
Remember that sort order of banner 1 is "1" and sort order of banner 2 is "2". In the same way we entered more banner.

##Image Magazine
Now we discuss image magazine. Image magazine also arranged according to the sort order.

In each row only **three** image magazines are show and other magazines will be automatically move to the next row.

But on the Main front Page only current month image magazines show.

 ![image](https://i.ibb.co/WfVkGn0/Add-image-magazine.png)

After banner magazines, **Four** image magazines are arranged with title and short description of related magazines. 

##Video Magazines

Like other magazines video magazines are also arranged according to the sort order. On front view only show thumbnail of video. Only  One thumbnail show in one row. 

When click on thumbnail then open new page where detail of this magazine available. This content write using page builder.


 ![Video](https://i.ibb.co/ctKNGqs/Video-Magazines.png)

##Other Magazines
In the end of this page current and previous Magazines collection in the slider. In the slider only show banner thumbnail of each month which sort order is one. 

If no **Banner Magazines thumbnail** of current or previous month Magazines then no thumbnail is show in the slider. 

If no magazine in any month then this month skip in this slider.

 ![slider](https://i.ibb.co/MGgqpmP/Slider.png)

##Monthly Magazines Collection

View Collection of any month click the slider thumbnail and view all collection of this month.

When click magazine in the slider then this window will open.

 ![slider](https://i.ibb.co/44VqgWj/click-on-slider.png)


#Detail Page
For detail of any magazine, click on the thumbnail of this magazine and view the detail of related magazine.

And detail page is show in this format. In this Page Detail Description added and also add images.

In the last of this page Products will be added by using page builder.

![slider](https://i.ibb.co/dmZtSDn/f75d1212-d401-45cc-b7f4-467551c35a7b.png)


