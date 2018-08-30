<?php
/**
 * @var string $baseUrl
 */
?>
<html>
<head>
    <title>GameX Install</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../assets/css/uikit.css" />
    <script src="../assets/js/uikit.js"></script>
</head>
<body>
<div class="uk-container uk-container-small uk-margin-medium">
    <form id="installForm">
        <div class="uk-child-width-1-2@m uk-grid" uk-grid="">
            <div class="uk-first-column">
                <div class="uk-card uk-card-default uk-margin uk-card-large">
                    <div class="uk-card-header">
                        <h3 class="uk-card-title">Database</h3>
                    </div>
                    <div class="uk-card-body">
                        <div class="form-group">
                            <label>Host:</label>
                            <input type="text" class="uk-input" id="formDatabaseHost" value="127.0.0.1">
                        </div>
                        <div class="form-group">
                            <label>Port:</label>
                            <input type="text" class="uk-input" id="formDatabasePort" value="3306">
                        </div>
                        <div class="form-group">
                            <label>User:</label>
                            <input type="text" class="uk-input" id="formDatabaseUser" value="root">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" class="uk-input" id="formDatabasePass" value="">
                        </div>
                        <div class="form-group">
                            <label>Database:</label>
                            <input type="text" class="uk-input" id="formDatabaseName" value="test">
                        </div>
                        <div class="form-group">
                            <label>Prefix:</label>
                            <input type="text" class="uk-input" id="formDatabasePrefix" value="gmx_">
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="uk-card uk-card-default uk-margin uk-card-large">
                    <div class="uk-card-header">
                        <h3 class="uk-card-title">Admin</h3>
                    </div>
                    <div class="uk-card-body">
                        <div>
                            <div class="form-group">
                                <label>Username:</label>
                                <input type="text" class="uk-input" id="formAdminLogin" value="admin">
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <input type="email" class="uk-input" id="formAdminEmail" value="admin@example.com">
                            </div>
                            <div class="form-group">
                                <label>Password:</label>
                                <input type="password" class="uk-input" id="formAdminPass" value="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="uk-flex uk-flex-center uk-flex-wrap uk-margin">
            <div class="uk-width-1-1@m uk-margin">
                <button type="submit" class="uk-button uk-button-secondary uk-button-large" id="formSubmitButton">Install</button>
            </div>
            <div class="uk-width-1-1@m uk-margin">
                <ul class="uk-list uk-width-1-1@m uk-list-divider" id="status"></ul>
            </div>
        </div>
    </form>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
    var statusList = $('#status');
	function result(step, nextCall) {
		var el= $('<li/>');
		el.addClass('list-group-item').text('Install ' + step + ': installing ...');
		statusList.append(el);
		return function (data) {
			if (data.status) {
				el.text('Install ' + step + ': installed');
				nextCall();
			} else {
				el.text('Install ' + step + ': error ' + data.message);
                el.addClass('uk-text-danger');
                $('#formSubmitButton').prop('disabled', false);
			}
		};
	}

	function fail(nextFunc) {
        return function () {
            nextFunc({
                success: false,
                message: 'Server error'
            });
            $('#formSubmitButton').prop('disabled', false);
        };
    }

    function installChecks() {
        var nextFunc = result('composer', installComposer);
        $.post('?step=checks')
            .done(nextFunc)
            .fail(fail(nextFunc));
    }

	function installComposer() {
	    var nextFunc = result('composer', installConfig);
		$.post('?step=composer')
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installConfig() {
		var data = {
			db: {
				host: $('#formDatabaseHost').val(),
				port: $('#formDatabasePort').val(),
				user: $('#formDatabaseUser').val(),
				pass: $('#formDatabasePass').val(),
				name: $('#formDatabaseName').val(),
				prefix: $('#formDatabasePrefix').val()
			}
		};
        var nextFunc = result('config', installMigrations);
		$.post('?step=config', data)
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installMigrations() {
        var nextFunc = result('migrations', installPermissions);
		$.post('?step=migrations')
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installPermissions() {
        var nextFunc = result('permissions', installAdmin);
		$.post('?step=permissions')
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installAdmin() {
		var data = {
			login: $('#formAdminLogin').val(),
			email: $('#formAdminEmail').val(),
			pass: $('#formAdminPass').val()
		};
        var nextFunc = result('administrator', installTasks);
		$.post('?step=admin', data)
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function installTasks() {
        var nextFunc = result('tasks', finish);
		$.post('?step=tasks')
            .done(nextFunc)
            .fail(fail(nextFunc));
	}

	function finish() {
        $('#formSubmitButton').prop('disabled', false);
        alert("Successfully installed");
        location.href = '../';
	}

	$('#installForm').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		statusList.empty();
		$('#formSubmitButton').prop('disabled', true);
        installChecks();
	});
</script>
</body>
</html>
