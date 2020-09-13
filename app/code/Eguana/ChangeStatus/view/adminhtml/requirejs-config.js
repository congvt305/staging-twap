/**
 * User: Brian
 * Date: 2020-07-03
 */
let config = {
    map: {
        '*': {
            "rma_cron":'Eguana_ChangeStatus/js/runCron',
            "order_status_cron":"Eguana_ChangeStatus/js/runOrderStatusCron"
        }
    },
    deps: ["jquery"]
};
