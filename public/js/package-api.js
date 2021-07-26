$(function() {
    $('#modalPackageStudentPeriod').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget) // Button that triggered the modal
        let student = button.data('student') // Extract info from data-* attributes
        console.log(student)
        $('#app_package_student_period_student').val(student);
    })

    $('.modal #save-package-student').click(function (event) {
        event.preventDefault()

        $.post(Routing.generate( 'app_api_package_student_period_create'), $( this ).parents('form').serialize())
            .done(function (data) {
                document.location.reload(true)
            }).fail(function (data) {
            console.log(data)
                alert(data)
            })

    })
});
