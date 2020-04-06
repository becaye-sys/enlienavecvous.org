const $ = require('jquery');
require('@popperjs/core');
require('bootstrap');
require('bootstrap-select');

$(document).ready(function () {
    $.fn.selectpicker.Constructor.BootstrapVersion = '4';
    let $departmentSelect = $(".js-therapist-form-department");
    let $townTarget = $(".js-town-target");
    $('select').selectpicker({
        liveSearch: true
    });

    $("select#therapist_register_department").on('change', function (e) {
        console.log('department changed');
        console.log('depart event:',e.currentTarget.value);
        $.ajax({
            url: `https://localhost:8000/ajax/town-select/`,
            data: {
                code: e.currentTarget.value
            },
            success: function (html) {
                if (!html) {
                    $townTarget.find('select').remove();
                    $townTarget.addClass('d-none');
                    return;
                }

                $townTarget.html(html);
                //$townTarget.removeClass('d-none').addClass('selectpicker');
                const $townSelect = $("select#therapist_register_town");
                console.log($townSelect[0].classList);
                if ($townSelect[0].classList.contains('d-none')) {
                    $townSelect[0].classList.remove('d-none')
                }
                $townSelect[0].classList.add('selectpicker');
                $townSelect.selectpicker({
                    liveSearch: true
                });
                $townSelect.on('change', function (e) {
                    console.log('town changed:',e.currentTarget.value);
                    $.ajax({
                        url: `https://localhost:8000/ajax/town-select/`,
                        data: {
                            id: e.currentTarget.value
                        },
                        success: function (html) {
                            if (!html) {
                                return;
                            }
                            console.log('town validated:',html);
                        }
                    });
                })
            }
        });
    });
});