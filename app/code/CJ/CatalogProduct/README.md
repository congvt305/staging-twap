CJ_CatalogProduct v1.0

Author : Phongnh

Explanation :

Product Order - Inquiries about products with high sales volume

Controller best seller: base_url/catalogproduct/index/bestseller

Test with postman: url: "base_url/catalogproduct/index/bestseller"

Params required:  store_id  (EX: store_id = 6)

Method: GET.

Ranking Status: Rank Stable = 0, Rank Up = 1, Rank Down = 2

Admin Config:  

        Stores -> Configuration -> Catalog -> Catalog -> Best Seller Products

        Stores -> Configuration -> Catalog -> Catalog -> Update Rank Cron

Allow Stores: This value will be used when run cron update ranking

Sample Response:

        {"data":[{"page_size":5,"name":"Lip Sleeping Mask [Berry]","entity_id":"3008","pirce":"75.000000","finalprice":75,"symbol":"RM","review_rate":4,"review_total":"1","ranking":"1","ranking_status":"0"},{"page_size":5,"name":"Water Sleeping Mask EX Set","entity_id":"2990","pirce":"180.000000","finalprice":180,"symbol":"RM","review_rate":0,"review_total":0,"ranking":"3","ranking_status":"0"},{"page_size":5,"name":"MY Laneige ","entity_id":"2999","pirce":"0.010000","finalprice":0.01,"symbol":"RM","review_rate":0,"review_total":0,"ranking":"4","ranking_status":"0"},{"page_size":5,"name":"Radian-C Cream","entity_id":"2984","pirce":"220.000000","finalprice":220,"symbol":"RM","review_rate":0,"review_total":0,"ranking":"6","ranking_status":"0"},{"page_size":5,"name":"Radian-C 4-pc Trial Sample","entity_id":"3062","pirce":"0.000000","finalprice":0,"symbol":"RM","review_rate":0,"review_total":0,"ranking":"9","ranking_status":"0"}]}
