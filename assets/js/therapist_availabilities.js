import './../public/css/jquery-ui.min.css';
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
const $ = require('jquery');
require('../public/js/jquery-ui.min.js');


$(document).ready( function () {
    const $filterForm = $("#table_filter_form");
    const $dateFilter = $("#date_filter");
    const $locationFilter = $("#location_filter");
    $("#appointment_bookingDate").datepicker();
    $dateFilter.on('change', function (e) {
        $filterForm.submit();
    });
    $locationFilter.on('change', function (e) {
        $filterForm.submit();
    });

    function ajaxRequest() {
        $dateFilter.on('change', function (e) {
            console.log(e.currentTarget);
            console.log($filterForm);
            $.ajax({
                url: $filterForm[0].action,
                method: $filterForm[0].method,
                data: {
                    date: e.currentTarget.value
                },
                success: function (result) {
                    console.log('success: ',result);
                }
            });
        });
    }
});