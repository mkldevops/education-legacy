// Use jQuery events because Bootstrap 3/4 trigger jQuery events, not native ones
$(function () {
    // When the modal is about to be shown
    $(document).on('show.bs.modal', '#modalPackageStudentPeriod', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        if (!button) return;
        var student = button.getAttribute('data-student');
        console.log(student);
        var studentInput = document.getElementById('app_package_student_period_student');
        if (studentInput) {
            studentInput.value = student;
        }
    });

    // Handle save button click inside the modal
    $(document).on('click', '#save-package-student', function (event) {
        event.preventDefault();

        var form = this.closest('form');
        if (!form) return;

        // Collect fields from the Symfony form
        var packageField = document.getElementById('app_package_student_period_package');
        var studentField = document.getElementById('app_package_student_period_student');
        var discountField = document.getElementById('app_package_student_period_discount');
        var commentField = document.getElementById('app_package_student_period_comment');

        // Parse discount robustly (handle comma decimal, strip symbols)
        var rawDiscount = discountField ? String(discountField.value || '') : '';
        var normalizedDiscount = rawDiscount.replace(/[^0-9,.-]/g, '');
        if (normalizedDiscount.indexOf(',') !== -1 && normalizedDiscount.indexOf('.') === -1) {
            normalizedDiscount = normalizedDiscount.replace(',', '.');
        } else if (normalizedDiscount.indexOf(',') !== -1 && normalizedDiscount.indexOf('.') !== -1) {
            // Keep last separator as decimal, remove others
            var lastComma = normalizedDiscount.lastIndexOf(',');
            var lastDot = normalizedDiscount.lastIndexOf('.');
            var decimalPos = Math.max(lastComma, lastDot);
            normalizedDiscount = normalizedDiscount
                .replace(/[.,]/g, function (m, offset) { return offset === decimalPos ? '.' : ''; });
        }
        var discount = parseFloat(normalizedDiscount);
        if (isNaN(discount)) discount = 0.0;

        var packageId = packageField ? parseInt(packageField.value, 10) : NaN;
        var studentId = studentField ? parseInt(studentField.value, 10) : NaN;
        if (!Number.isFinite(packageId) || packageId <= 0) {
            alert('Package is required');
            return;
        }
        if (!Number.isFinite(studentId) || studentId <= 0) {
            alert('Student is required');
            return;
        }

        var payload = {
            packageId: packageId,
            studentId: studentId,
            discount: discount,
            // API expects string, not null
            comment: commentField ? String(commentField.value || '') : ''
        };

        fetch(Routing.generate('app_api_package_student_period_create'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                if (response.ok) {
                    document.location.reload(true);
                } else {
                    return response.text().then(function (text) {
                        throw new Error(text);
                    });
                }
            })
            .catch(function (error) {
                console.log(error);
                alert(error.message);
            });
    });
});
