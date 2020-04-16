
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import {ajaxCallIpAPI, ipCallback} from "./services/ipApiCall";

$(document).ready( function () {
    const $selectCountry = $("#patient_register_country");
    const $selectDepartment = $("#patient_register_scalarDepartment");
    const $selectTown = $("#patient_register_scalarTown");

    ajaxCallIpAPI(ipCallback, $selectCountry, $selectDepartment, $selectTown);
});