document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.querySelector('.modal #save-student');
    if (saveButton) {
        saveButton.addEventListener('click', function (event) {
            event.preventDefault();

            const form = this.closest('form');
            const formData = new FormData(form);

            fetch(Routing.generate('app_api_student_create'), {
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
                alert(error.message);
            });
        });
    }
});
