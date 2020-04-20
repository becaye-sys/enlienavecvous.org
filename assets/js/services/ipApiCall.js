import $ from "jquery";
import {API_URL, customHeaders} from "../config";

export function ajaxCallIpAPI(callback, $country, $department, $town) {
    $.ajax({
        url: `${API_URL}get-ip`,
        async: false,
        method: 'GET',
        headers: customHeaders,
        success: function (result) {
            callback(result, $country, $department, $town);
        }
    })
}

export function ipCallback(result, $country, $department, $town) {
    console.log(result?.content);
    let content = JSON.parse(result?.content);
    console.log('info:',content);
    $country[0].value = content.country;
    $department[0].value = content.zip.split(0,1) + " " + content.regionName;
    $town[0].value = content.zip + " " + content.city;
}