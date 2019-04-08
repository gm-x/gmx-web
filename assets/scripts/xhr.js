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
        message: window.MESSAGES.saved,
        status: 'success',
        pos: 'top-center',
        timeout: 5000
    });
}

function alertFail() {
    UIkit.notification({
        message: window.MESSAGES.exception,
        status: 'success',
        pos: 'top-center',
        timeout: 5000
    });
}
