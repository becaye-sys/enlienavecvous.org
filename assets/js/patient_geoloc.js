
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';
import {getPublicIp, publicIpCallback} from "./services/getUserPublicIP";

$(document).ready( async function () {
    const $selectCountry = $("#patient_register_country");
    const $selectDepartment = $("#patient_register_scalarDepartment");
    const $selectTown = $("#patient_register_scalarTown");
    const $ipDiv = $("#ip");

    // get public ip with ajax -> async
    await getPublicIp(publicIpCallback, $ipDiv, $selectCountry, $selectDepartment, $selectTown);
    //await getLocalisation(localisationCallback($ipDiv, $selectCountry, $selectDepartment, $selectTown))
    // set public ip to dataset in html
    // get stored ip and call localisation apis
});