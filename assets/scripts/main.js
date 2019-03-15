$(document).ready(function () {
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
});
