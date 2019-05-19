$(document).on('moved', '.uk-sortable', function(e) {
    var indexes = [];
    $('.uk-sortable').find('ul').each(function(i) {
        indexes.push($(this).data('id'));
    });
    $.post(window.__GMX_DATA__.GROUPS_PRIORITY_URL, {priority: indexes})
        .done(alertSuccess)
        .fail(alertFail);
});
