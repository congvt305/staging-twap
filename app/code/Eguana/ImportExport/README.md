Eguana_ImportExport
============================

`Author` : Muhammad Umer Farooq

####Short Description

Fixed Customer Export Issue, Added Order Items Print Option in Order's Grid Mass Action.

####Explanation

**1)** Customer Data can't be exported when there exists any customer with gender (female)
This issue is fixed by added a preference "Eguana\ImportExport\Plugin\Ui\Component\DataProvider\Document" on class "Magento\Customer\Ui\Component\DataProvider\Document".

============================

`Author` : Bilal Younas

####Short Description

Add print Order mass action in order grid.

####Explanation

**1)** When you click on Print Order mass action a csv file will download with all grid detail and also add promotioms which are apply on order

![order-items-report](https://nimbus-screenshots.s3.amazonaws.com/s/d0171c4e1e74bbefa9b3959c8b7a5d5e.png)

##Print Order Items

`Author` : Raheel Shaukat

### Description

Added "Print Order Items" option in order's grid mass action select field so that admin can generate report of selected order with items in csv format.

![order-items-report](https://nimbus-screenshots.s3.amazonaws.com/s/eb30aa81f77c5431545787374f804dbe.png)

### Mobile number columns (Print Order Items)

Order status column is included while exporting order items.

![mobile](https://nimbus-screenshots.s3.amazonaws.com/s/a347b596d0ebe1385e02ae1ccc332a1b.png)

### Mobile number columns (Print Orders)

Customer registration mobile number and shipping address mobile number columns are added in the print order item report.

![mobile](https://nimbus-screenshots.s3.amazonaws.com/s/33a0131abeb22e8678dec69da7d80c26.png)


#### Note

This column will be unavailable when BA Code feature is disabled from customer registration configuration.

![config](https://nimbus-screenshots.s3.amazonaws.com/s/eae4d2ed25eab601c331b19589260f60.png)
