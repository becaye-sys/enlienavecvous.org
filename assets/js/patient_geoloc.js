import $ from 'jquery';
import {getPublicIp, publicIpCallback} from "./services/getUserPublicIP";
let GeoDb = require('wft-geodb-js-client');
import axios from "axios";

let patientClient = GeoDb.ApiClient.instance;

let api = new GeoDb.GeoApi();

const GEO_API_URL = `https://wft-geo-db.p.mashape.com`;

const citySearchUrl = `http://www.citysearch-api.com/$country/city?login=onestlapourvous&apikey=so4c0d00de65b6aae5842f3e6f4a32040c0f5f7058&dp=$code`

$(document).ready( async function () {
    const $selectCountry = $("#patient_register_country");
    const $selectDepartment = $("#patient_register_scalarDepartment");
    const $selectTown = $("#patient_register_scalarTown");
    const $ipDiv = $("#ip");

    $selectCountry.on('change', function (e) {
        const regions = axios
            .get(`${GEO_API_URL}/v1/geo/countries/{countryId}/regions`)
            .then(function (response) {
                console.log(response);
            });
    });

    // get public ip with ajax -> async
    await getPublicIp(publicIpCallback, $ipDiv, $selectCountry, $selectDepartment, $selectTown);
    //await getLocalisation(localisationCallback($ipDiv, $selectCountry, $selectDepartment, $selectTown))
    // set public ip to dataset in html
    // get stored ip and call localisation apis
});