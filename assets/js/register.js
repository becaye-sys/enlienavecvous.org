
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
import $ from 'jquery';

$(document).ready( function () {
    const $selectCountry = $("#therapist_register_country");
    const $selectDepartment = $("#therapist_register_department");
    const $selectTown = $("#therapist_register_town");

    $selectCountry.on('change', function (e) {
        $selectDepartment.css('display','none');
        console.log('country: ',e.currentTarget.value);
        const $defaultOptions = $selectDepartment[0].options;
        const $defaultOptionsLength = $selectDepartment[0].options.length;
        $selectDepartment.find('option')
            .remove()
            .end();

        $.ajax({
            url: $selectCountry[0].dataset.departmentUrl,
            method: 'POST',
            data: {
                country: e.currentTarget.value
            },
            success: function (result) {
                console.log('success: ',result);
                console.log('departments select: ',$selectDepartment);
                result.forEach((item, key) => {
                    //console.log(item,key);
                    let option = document.createElement("option");
                    option.text = item.code + " " + item.name;
                    option.value = item.id;
                    $selectDepartment[0].add(option);
                    console.log('value: ',item.code + " " + item.name);
                });
                $selectDepartment.css('display','inline-block');
                console.log('departments select: ',$selectDepartment);
            }
        });
    });

    $selectDepartment.on('change', function (e) {
        $selectTown.find('option')
            .remove()
            .end();
        console.log('department: ',e.currentTarget.value);
        $.ajax({
            url: $selectDepartment[0].dataset.townUrl,
            method: 'POST',
            data: {
                department: e.currentTarget.value
            },
            success: function (result) {
                console.log('success: ',result);
                console.log('town select: ',$selectTown);
                result.forEach((item, key) => {
                    console.log(item,key);
                    let option = document.createElement("option");
                    option.text = item.code + " " + item.name;
                    option.value = item.id;
                    $selectTown[0].add(option);
                    //console.log('value: ',item.code + " " + item.name);
                });
                $selectTown.css('display','inline-block');
                console.log('town select: ',$selectTown);
            }
        });
    });
});