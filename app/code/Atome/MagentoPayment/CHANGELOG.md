2.6.5 (released on 12 May 2022)
========
* optimize text & UI

2.6.4 (released on 28 April 2022)
========
* fix creating 'complete' orders with cancelled items

2.6.3 (released on 22 April 2022)
========
* fix may cause database dead lock when callback
* change default value of order_created_when setting to before_paying
* add checkout agreements block

2.6.2 (released on 15 Mar 2022)
========
* fix cannot get correct scope values when refunding

2.6.1 (released on 08 Mar 2022)
========
* fix some errors

2.6.0 (released on 17 Feb 2022)
========
* new feature: support TaiWan
* new feature: add setting for whether clear cart when customer gives up paying or pay failed
* new feature: set order to cancelled when the payment expired

2.5.6 (released on 25 Nov 2021)
========
* fix error when refunding as the order has been refunded via atome merchant center

2.5.5 (released on 22 Nov 2021)
========
* fix that multiple useless invoices may be generated in some cases

2.5.4 (released on 19 Nov 2021)
========
* fix errors when handling orders without shipping address

2.5.3 (released on 11 Nov 2021)
========
* add promotion icon for new user on checkout page for TH.

2.5.2 (released on 4 Nov 2021)
========
* fix issue: callback error when customer clicked the "Back to merchant" button on the Atome payment gateway before he paid for this.

2.5.1
========
* add setting for whether to delete order without paying when redirecting from Atome gateway

2.5.0
========
* add max_spend setting

2.4.12
========
* fix error 'Registry key "isSecureArea" already exists'

2.4.11
========
* fix the QTY not restored when payment failed

2.4.10
========
* fix sending email when creating order first

2.4.9
========
* fix guest checkout may cause "Invalid customer address id " error

2.4.8
========
* fix missing billing address

2.4.7
* optimize for dynamic total on Aheadworks_OneStepCheckout

2.4.5
========
* prevent creating order when order's grand total is not equal payment amount

2.4.4
========
* prevent creating repetitive orders

2.4.3
========
* add fix action for fixing quote data
* optimize validate payment status when checkout

2.4.2
========
* support Philippines

2.4.0
========
* support creating order before payment
