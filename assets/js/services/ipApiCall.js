import $ from "jquery";

export function ajaxCallIpAPI(callback, $country, $department, $town) {
    $.ajax({
        url: `https://127.0.0.1:8000/api/get-ip`,
        async: false,
        method: 'GET',
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