
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import 'gijgo/js/gijgo.min';

//$('.booking_date_picker').datepicker({
//    uiLibrary: 'bootstrap4'
//});
$('.booking_start_picker').timepicker({
    uiLibrary: 'bootstrap4'
});
$('.booking_end_picker').timepicker({
    uiLibrary: 'bootstrap4'
});