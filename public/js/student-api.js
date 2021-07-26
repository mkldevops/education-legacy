$(function() {
    $('.modal #save-student').click(function (event) {
        event.preventDefault()

        $.post(Routing.generate( 'app_api_student_create'), $( this ).parents('form').serialize())
            .done(function (data) {
                document.location.reload(true)
            }).fail(function (data) {
                alert(data)
            })

    })
});
