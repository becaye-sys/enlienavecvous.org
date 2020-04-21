import $ from "jquery";
import axios from "axios";
import {API_URL, customHeaders} from "../config";
import {getLocalisation, localisationCallback} from "./localisation";

export async function getPublicIp(callback, $ipDiv, $selectCountry, $selectDepartment, $selectTown) {
    return await axios
        .get(`${API_URL}get-ip`, customHeaders)
        .then(function (response) {
            callback(response, $ipDiv, $selectCountry, $selectDepartment, $selectTown)
        })
        .catch(function (error) {
            console.log('error: ',error);
        });
}

export function publicIpCallback(response, $ipDiv, $selectCountry, $selectDepartment, $selectTown) {
    const parsedResponse = JSON.parse(response?.data?.content);
    const ip = parsedResponse.ip;
    console.log('ip:',ip);
    if (ip !== undefined) {
        $ipDiv[0].dataset.ipAddr = ip;
        getLocalisation(localisationCallback, ip, $selectCountry, $selectDepartment, $selectTown)
        return ip;
    }
}

function ajaxCall(callback, $ipDiv) {
    $.ajax({
        url: `${API_URL}get-ip`,
        async: true,
        method: 'GET',
        headers: customHeaders,
        success: async function (result) {
            await callback(result, $ipDiv);
        },
        error: async function (error) {
            await console.log('get public ip error:',error);
        }
    })
}