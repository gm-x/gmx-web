$.ajaxPrefilter(function (options) {
    $('#spinner').removeClass('uk-hidden');
    if (options.processData && options.contentType === 'application/x-www-form-urlencoded; charset=UTF-8') {
        options.data = (options.data ? options.data + '&' : '') + $.param(window.CSRF_TOKEN);
    }
});

$(document).ajaxComplete(function(event, xhr) {
    $('#spinner').addClass('uk-hidden ');
    xhr.then(function (data) {
        if (data.csrf) {
            window.CSRF_TOKEN = data.csrf;
        }
    })
});

function alertSuccess() {
    UIkit.notification({
        message: window.__GMX_DATA__.MESSAGES.saved,
        status: 'success',
        pos: 'top-center',
        timeout: 5000
    });
}

function alertFail() {
    UIkit.notification({
        message: window.__GMX_DATA__.MESSAGES.exception,
        status: 'success',
        pos: 'top-center',
        timeout: 5000
    });
}

$(document).ready(function () {
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

    var langSelect = $('.lang-select');
    langSelect.on('click', 'a', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var lang = $(this).data('lang');
        $.post(langSelect.data('href'), {lang: lang})
            .always(function() {
                location.reload()
            });
    });

    $('.datepicker').pickadate({
	    lang: 'ru',
	    format: 'dd.mm.yyyy',
	    formatSubmit: 'yyyy-mm-dd',
	    //min: new Date(),
	    closeOnSelect: true,
        editable: true,
        hiddenName: true,
        selectYears: true,
        selectMonths: true
    });

    UIkit.util.on('.forms-expired-element > li', 'show', function() {
        var self = $(this);
        $(self.data('input')).val(self.data('value'));
        console.log(this, $(self.data('input')), self.data('value'));
    });
});
