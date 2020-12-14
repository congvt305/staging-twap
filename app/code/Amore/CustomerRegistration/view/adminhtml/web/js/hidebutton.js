/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 11/12/20
 * Time: 11:44 AM
 */
require([
    'jquery'
], function ($) {
    $(window).on('load', function () {
        $("#customerregistraion_customergroups_customer_group_mapping button").remove();
        $("#customerregistraion_customergroups_customer_group_mapping action-delete").remove();
        $("#customerregistraion_customergroups_customer_group_mapping thead tr th:nth-child(3)").remove();
        $("#customerregistraion_customergroups_customer_group_mapping tbody tr td:nth-child(3)").remove();
    });
});
