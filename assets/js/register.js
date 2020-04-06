import $ from 'jquery';

$(document).ready(function () {
    let $departmentSelect = $(".js-therapist-form-department");
    let $townTarget = $(".js-town-target");

    $departmentSelect.on('change', function (e) {
        $.ajax({
            url: $departmentSelect[0].dataset.townUrl,
            data: {
                code: $departmentSelect.val()
            },
            success: function (html) {
                if (!html) {
                    $townTarget.find('select').remove();
                    $townTarget.addClass('d-none');
                    return;
                }

                $townTarget
                    .html(html)
                    .removeClass('d-none');
            }
        });
    });
});