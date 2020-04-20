
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import {getPublicIp, publicIpCallback} from "./services/getUserPublicIP";

$(document).ready( async function () {
    const $selectCountry = $("#therapist_register_country");
    const $selectDepartment = $("#therapist_register_scalarDepartment");
    const $selectTown = $("#therapist_register_scalarTown");
    const $ipDiv = $("#ip");

    await getPublicIp(publicIpCallback, $ipDiv, $selectCountry, $selectDepartment, $selectTown);
});