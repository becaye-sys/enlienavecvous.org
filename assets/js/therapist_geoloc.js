
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import {ajaxCallIpAPI, ipCallback} from "./services/ipApiCall";

$(document).ready( function () {
    const $selectCountry = $("#therapist_register_country");
    const $selectDepartment = $("#therapist_register_scalarDepartment");
    const $selectTown = $("#therapist_register_scalarTown");

    ajaxCallIpAPI(ipCallback, $selectCountry, $selectDepartment, $selectTown);
});