import {API_URL, customHeaders} from "../config";
import axios from "axios";

export async function getLocalisation(callback, ipAddr, $selectCountry, $selectDepartment, $selectTown) {
    console.log('ajax call ip api:',ipAddr);
    return await axios
        .get(`${API_URL}get-localisation?ip=${ipAddr}`, customHeaders)
        .then(function (response) {
            callback(response, $selectCountry, $selectDepartment, $selectTown)
        })
        .catch(function (error) {
            console.log('error: ',error);
        });
}

export function localisationCallback(result, $country, $department, $town) {
    const content = JSON.parse(result?.data?.content);
    console.log('content:',content);
    $country[0].value = content.country;
    $department[0].value = content.zip.split(0,1) + " " + content.regionName;
    $town[0].value = content.zip + " " + content.city;
}