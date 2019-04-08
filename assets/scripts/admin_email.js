$(document).ready(function () {
    UIkit.util.on('#testMail', 'click', function (e) {
        e.preventDefault();
        e.target.blur();
        UIkit.notification({
            message: 'Testing...',
            pos: 'bottom-right',
            timeout: 5000
        });
        $.post(window.EMAIL_TEST_URL, $('#mailConfigForm').serialize())
            .done(function (data) {
                if (data.success) {
                    UIkit.notification({
                        message: 'Success!',
                        status: 'success',
                        pos: 'bottom-right',
                        timeout: 8000
                    });
                } else {
                    UIkit.notification({
                        message: 'Fail: ' + data.message || 'Unknown',
                        status: 'danger',
                        pos: 'bottom-right',
                        timeout: 8000
                    });
                }
            })
            .catch(function () {
                UIkit.notification({
                    message: 'Fail: Server Error',
                    status: 'danger',
                    pos: 'bottom-right',
                    timeout: 8000
                });
            });
    });

    $('#email_pref_transport').on('change', function () {
        if ($(this).val() === 'smtp') {
            $('#email_pref_smtp').removeClass('d-none');
        } else {
            $('#email_pref_smtp').addClass('d-none');
        }
    });
});
