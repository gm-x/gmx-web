UIkit.util.on('form.delete-form', 'submit', function (e) {
    e.preventDefault();
    e.target.blur();
    var form = this;
    UIkit.modal.confirm(window.MESSAGES.are_you_sure, {
        labels: {
            ok: window.MESSAGES.delete,
            cancel: window.MESSAGES.cancel
        }
    }).then(function () {
        form.submit();
    });
});

