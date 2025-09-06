document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('modalPackageStudentPeriod');

    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            const student = button.getAttribute('data-student'); // Extract info from data-* attributes
            console.log(student);
            const studentInput = document.getElementById('app_package_student_period_student');
            if (studentInput) {
                studentInput.value = student;
            }
        });
    }

    const saveButton = document.querySelector('.modal #save-package-student');
    if (saveButton) {
        saveButton.addEventListener('click', function (event) {
            event.preventDefault();

            const form = this.closest('form');
            const formData = new FormData(form);

            fetch(Routing.generate('app_api_package_student_period_create'), {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                if (response.ok) {
                    document.location.reload(true);
                } else {
                    return response.text().then(text => {
                        throw new Error(text);
                    });
                }
            })
            .catch(function(error) {
                console.log(error);
                alert(error.message);
            });
        });
    }
});
