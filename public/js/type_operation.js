$(function () {
    console.log(Routing.generate('app_api_operation_update', { "id" : 1233 }));
    $('#modalTypeOperation').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        let operationId = button.parents('tr').data('id');
        $(this).data('operation', operationId);
    });

    $('#modal_type_operation', '#modalTypeOperation').change(function () {
        let modal = $(this).parents('#modalTypeOperation');
        let id = $(modal).data('operation');
        let operation = $('#operation-'+ id);
        let dataOperation = {
            "typeOperation": {
                "class": "App\\Entity\\TypeOperation",
                "id": $(this).val()
            }
        };

        console.log(Routing.generate('app_api_operation_update', { "id" : id }));

        $.ajax({
            type: "POST",
            data: JSON.stringify(dataOperation),
            contentType: "application/json",
            dataType: "json",
            url: Routing.generate('app_api_operation_update', { "id" : id })
        }).done(function (response) {
            if (!response.success) {
                alert(response.success)
            }

            let label = '';
            if(response.data.typeOperation.id > 0) {
                label = response.data.amount > 0 ? 'label-success' : 'label-danger';
            }

            console.log( $('#modal_type_operation'));
            $('#modal_type_operation option[value="0"]').prop('selected', true);


            $('.type-operation', operation)
                .removeClass('label-danger label-success')
                .addClass(label)
                .text(response.data.typeOperation.name);
            $(modal).modal('hide');

        }).fail(function (response) {
            console.log(response);
            alert('An error occurred on process');
        });
    });
});
