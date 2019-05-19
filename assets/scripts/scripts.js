$.ajaxPrefilter(function (options) {
	$('#spinner').removeClass('uk-hidden');
	if (options.processData && options.contentType === 'application/x-www-form-urlencoded; charset=UTF-8') {
		options.data = (options.data ? options.data + '&' : '') + $.param(window.__GMX_DATA__.CSRF_TOKEN);
	}
});

$(document).ajaxComplete(function(event, xhr) {
	$('#spinner').addClass('uk-hidden ');
	xhr.then(function (data) {
		if (data.csrf) {
			window.__GMX_DATA__.CSRF_TOKEN = data.csrf;
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
				ok: window.__GMX_DATA__.MESSAGES.delete,
				cancel: window.__GMX_DATA__.MESSAGES.cancel
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
});