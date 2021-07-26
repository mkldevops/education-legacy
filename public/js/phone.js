$(function () {
    $('.phone-value, .phone-name').hover(function () {
        $('.phone-action', this).toggleClass('hidden');
    });

    $('.add-phone').click(function (e) {
        e.preventDefault();
        var dl = $(this).parents('dl');

        if ($(dl).find('.new-phone-input').length < 1) {
            $(dl).append(
                $('<dd/>', {class: 'phone-value'})
                    .append(
                        $('<input>', {type: 'text', class: 'form-control phone-input new-phone-input'})
                            .blur(function () {
                                $(this).updatePhone();
                            }).on("keydown", function (event) {
                            if (event.which === 13) {
                                $(this).updatePhone();
                            }
                        })
                    )
            );
        } else {
            console.log($(dl));
        }

        console.log(dl);
    });

    $('.edit-phone').click(function (e) {
        e.preventDefault();
        var input = $(this).parents('.phone-value').find('.phone-input');
        var item = $(this).parents('.phone-value').find('.phone');
        var text = $(this).parents('.phone-value').find('.phone-text');

        $(item).toggleClass('hidden');
        $(input).toggleClass('hidden')
            .val($(text).text())
            .blur(function () {
                $(this).updatePhone();
            }).on("keydown", function (event) {
            if (event.which === 13) {
                $(this).updatePhone();
            }
        });
    });

    $.fn.updatePhone = function () {
        var item = $(this).parents('.phone-value').find('.phone');
        var person = $(this).parents('dl').data('person');
        var key = $(this).parents('dd').data('key');
        var value = $(this).val();

        $.ajax({
            url: Routing.generate('app_api_person_update_phone', {id: person}),
            type: "PUT",
            data: {"key": key, "value": value}
        }).done(function (msg) {
            if (msg.success) {
                $(this).toggleClass('hidden');
                $(item).toggleClass('hidden');
            }
        }).fail(function (a, b, c) {
            console.log(a, b, c);
            return false;
        });
    };

    $('.delete-phone').click(function (e) {
        e.preventDefault();
        var dd = $(this).parents('dd');
        var dl = $(this).parents('dl');

        $.ajax({
            url: Routing.generate('app_api_person_delete_phone', {id: $(dl).data('person')}),
            type: "DELETE",
            data: {"key": $(dd).data('key')}
        }).done(function (msg) {
            console.log(msg);
            $(dd).hide('slow')
                .remove();
        }).fail(function (a, b, c) {
            console.log(a, b, c);
            return false;
        });
    });

    $('#phone-modal-sm').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var person = button.data('person');// Extract info from data-* attributes
        var modal = $(this);

        $.ajax({
            url: Routing.generate('app_api_person_get_phones', {id: person}),
            type: "GET"
        }).done(function (msg) {
            console.log(msg);
            modal.find('.modal-content').html(msg.phones);
        }).fail(function (a, b, c) {
            console.log(a, b, c);
            return false;
        });
    })
});