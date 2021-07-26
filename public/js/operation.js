$(function () {
    $('[data-toggle="popover"]').on('shown.bs.popover', function () {
        var myPopover = $(this);
        $('.datepicker', '.popover-content').datepicker({dateFormat: 'dd/mm/yy'});

        $('.form-planed-operation').submit(function () {
            $('[type=submit]', this).attr('disabled', true);

            let result = $(this).validationOperation($(this).serializeObject());

            $(myPopover).popover('hide');

            return result;
        });
    });

    $('.check', '.validate-operation').click(function () {
        return $(this).parent().validationOperation({});
    });

});

$.fn.validationOperation = function (dataOperation) {
    let id = $(this).data('id');
    let spanValidate = $('#validate-operation-' + id);
    let iconValidate = $('.fa', spanValidate);
    $(iconValidate).toggleClass('fa-square-o fa-spinner fa-spin');

    $.ajax({
        type: "POST",
        data: dataOperation,
        url: Routing.generate('app_operation_validate', {id: id})
    }).done(function (response) {
        if (response.success) {
            $(spanValidate)
                .toggleClass('text-' + response.data.validate.type)
                .html('')
                .append(
                    $('<span />', {
                        class: 'far fa-check-square',
                        'data-toggle': "tooltip",
                        'data-placement': "top",
                        'data-html': "true",
                        title: response.message
                    }).tooltip()
                )
                .append(
                    $('<span />', {
                        class: 'text hidden-xs',
                        html: strip_tags(' ' + response.message)
                    })
                );

            $(spanValidate).parents('panel').addClass('panel-' + response.data.validate.type);
        } else {
            $(iconValidate)
                .toggleClass('fa-spinner fa-spin fa-squaresquare-o')
                .after($('<span />', {class: 'text-error'})
                    .html(response.message));
        }
    }).fail(function () {
        $(iconValidate).toggleClass('fa-spinner fa-spin fa-exclamation-triangle text-danger');
    });

    return false;
};
