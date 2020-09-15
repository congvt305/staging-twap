Eguana_ImportExport
============================

`Author` : Muhammad Umer Farooq

***Short Description***

Fixed Customer Export Issue

***Explanation*** 

Customer Data can't be exported when there exists any customer with gender (female)
This issue is fixed by added a preference Eguana\ImportExport\Plugin\Ui\Component\DataProvider\Document on class Magento\Customer\Ui\Component\DataProvider\Document
