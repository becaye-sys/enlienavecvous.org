
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

$(document).ready( function () {
    const $selectCountry = $("#therapist_register_country");
    const $selectDepartment = $("#therapist_register_department");
    const $selectTown = $("#therapist_register_town");

    $selectCountry.on('change', function async (e) {
        console.log($selectDepartment.find('option')[0].value);
        ajaxCallback(
            $selectCountry[0].dataset.getDepartmentsUrl,
            {country: e.currentTarget.value},
            function ($data) {
                const option = doWithHtml($data, $selectDepartment);
                console.log('option:',option);
                $selectDepartment[0].dataset.defaultValue = option.text.slice(0,4);

                ajaxCallback(
                    $selectDepartment[0].dataset.getTownsUrl,
                    {department: $selectDepartment[0].dataset.defaultValue},
                    function ($data) {
                        doWithHtml($data, $selectTown);
                    }
                );
            }
        );
        console.log('depart select result:',$selectDepartment.find('option'));
        if ($selectDepartment[0].dataset.defaultValue !== '') {
            console.log('default value available');
        }
        if ($selectDepartment.find('option').length > 0) {
            console.log('departments reloaded');
        }
    });

    $selectDepartment.on('change', function ({ currentTarget }) {
        $selectTown.find('option')
            .remove()
            .end();
        ajaxCall(
            $selectDepartment[0].dataset.getTownsUrl,
            'POST',
            {department: currentTarget.value},
            $selectTown
        );
    });

});

async function ajaxCall($url, $method, $data = {}, $targetSelect) {
    return $.ajax({
        url: $url,
        async: true,
        method: $method,
        data: $data,
        success: function (result) {
            result.forEach((item, key) => {
                let option = document.createElement("option");
                option.text = item.code + " " + item.name;
                option.value = item.id;
                $targetSelect[0].add(option);
            });
            console.log('select: ', $targetSelect.find('option'));
        }
    });
}

function doWithHtml($data, $targetSelect) {
    $targetSelect.find('option')
        .remove()
        .end();
    $data.forEach((item, key) => {
        let option = document.createElement("option");
        option.text = item.code + " " + item.name;
        option.value = item.code;
        $targetSelect[0].add(option);
    });
    return $targetSelect.find('option')[0];
}

async function ajaxCallback($url, $data, callback) {
    $.ajax({
        url: $url,
        async: true,
        method: 'POST',
        data: $data,
        success: function (result) {
            callback(result);
        }
    });
}