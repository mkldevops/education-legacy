document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.querySelector('.modal #save-student');
    if (saveButton) {
        saveButton.addEventListener('click', function (event) {
            event.preventDefault();

            const form = this.closest('form');
            const formData = new FormData(form);
            const modalContainer = form.closest('[x-data]');

            // Supprimer les anciens messages d'erreur
            const existingErrorContainer = form.querySelector('.form-error-container');
            if (existingErrorContainer) {
                existingErrorContainer.remove();
            }

            fetch(Routing.generate('app_api_student_create'), {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                if (response.ok) {
                    return response.json().then(data => {
                        // Fermer la modal via Alpine.js
                        if (modalContainer && modalContainer.__x) {
                            modalContainer.__x.$data.showModal = false;
                        }

                        // Recharger la page après un court délai pour laisser la modal se fermer
                        setTimeout(() => {
                            document.location.reload(true);
                        }, 300);
                    });
                } else {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Une erreur est survenue');
                    }).catch(() => {
                        return response.text().then(text => {
                            throw new Error(text || 'Une erreur est survenue');
                        });
                    });
                }
            })
            .catch(function(error) {
                // Créer le conteneur d'erreur
                const errorContainer = document.createElement('div');
                errorContainer.className = 'form-error-container mb-4 bg-red-50 border border-red-200 rounded-md p-4';
                errorContainer.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Erreur de validation
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                ${error.message}
                            </div>
                        </div>
                    </div>
                `;

                // Insérer l'erreur avant les boutons (dans .px-6.py-4.border-t)
                const buttonContainer = form.querySelector('.px-6.py-4.border-t');
                if (buttonContainer) {
                    buttonContainer.insertAdjacentElement('beforebegin', errorContainer);
                    // Scroll vers l'erreur
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        });
    }
});
