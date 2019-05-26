$(document).ready(function () {
	UIkit.util.on('#activateUser', 'click', function (e) {
		e.preventDefault();
		e.target.blur();
		$.post($(this).attr('href'))
			.done(function(data) {
				if (data.success) {
					location.reload();
				} else {
					alertFail();
				}
			})
			.fail(function() {
				alertFail();
			});
	});
});