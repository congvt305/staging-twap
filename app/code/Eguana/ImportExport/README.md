Eguana_ImportExport
============================

`Author` : Muhammad Umer Farooq, Raheel Shaukat

####Short Description

Fixed Customer Export Issue, Added Order Items Print Option in Order's Grid Mass Action.

####Explanation

**1)** Customer Data can't be exported when there exists any customer with gender (female)
This issue is fixed by added a preference "Eguana\ImportExport\Plugin\Ui\Component\DataProvider\Document" on class "Magento\Customer\Ui\Component\DataProvider\Document".

**2)** Added "Print Order Items" option in order's grid mass action select field so that admin can generate report of selected order with items in csv format.

![order-items-report](https://nimbus-screenshots.s3.amazonaws.com/s/eb30aa81f77c5431545787374f804dbe.png)

============================

`Author` : Bilal Younas

####Short Description

Add print Order mass action in order grid.

####Explanation

**1)** When you click on Print Order mass action a csv file will download with all grid detail and also add promotioms which are apply on order 

![order-items-report](https://nimbus-screenshots.s3.amazonaws.com/s/d0171c4e1e74bbefa9b3959c8b7a5d5e.png)
