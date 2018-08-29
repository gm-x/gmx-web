<?php
/**
 * @var string $baseUrl
 */
?>
<html>
<head>
    <title>GameX Install</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script src="../assets/js/jquery-3.3.1.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center">
        <form class="col-8" id="installForm">
            <div class="form-row">
                <div class="col">
                    <fieldset>
                        <legend>Database:</legend>
                        <div class="form-group">
                            <label>Host:</label>
                            <input type="text" class="form-control" id="formDatabaseHost" value="127.0.0.1">
                        </div>
                        <div class="form-group">
                            <label>Port:</label>
                            <input type="text" class="form-control" id="formDatabasePort" value="3306">
                        </div>
                        <div class="form-group">
                            <label>User:</label>
                            <input type="text" class="form-control" id="formDatabaseUser" value="root">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" class="form-control" id="formDatabasePass" value="">
                        </div>
                        <div class="form-group">
                            <label>Database:</label>
                            <input type="text" class="form-control" id="formDatabaseName" value="test">
                        </div>
                        <div class="form-group">
                            <label>Prefix:</label>
                            <input type="text" class="form-control" id="formDatabasePrefix" value="gmx_">
                        </div>
                    </fieldset>
                </div>
                <div class="col">
                    <fieldset>
                        <legend>Admin:</legend>
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" class="form-control" id="formAdminLogin" value="admin">
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" class="form-control" id="formAdminEmail" value="admin@example.com">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" class="form-control" id="formAdminPass" value="">
                        </div>
                    </fieldset>
                </div>
            </div>
            <button type="submit" class="btn btn-primary float-right" id="formSubmitButton">Install</button>
        </form>
    </div>
    <div class="row justify-content-center  align-items-center">
        <ul class="list-group col-8 w-100" id="status"></ul>
    </div>
</div>
<script>
    var statusList = $('#status');
	function result(step, nextCall) {
		var el= $('<li/>');
		el.addClass('list-group-item').text('Install ' + step + ': installing ...');
		statusList.append(el);
		return function (data) {
			if (data.success) {
				el.text('Install ' + step + ': installed');
				nextCall();
			} else {
				el.text('Install ' + step + ': error ' + data.message);
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
