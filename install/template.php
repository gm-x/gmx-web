<?php
/**
 * @var string $baseUrl
 */
?>
<html>
<head>
    <title>GameX Install</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="<?= $baseUrl; ?>/assets/css/bootstrap.min.css">
    <script src="<?= $baseUrl; ?>/assets/js/jquery-3.3.1.min.js"></script>
    <script src="<?= $baseUrl; ?>/assets/js/bootstrap.min.js"></script>
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
                            <input type="text" class="form-control" id="formDatabasePrefix" value="gamex_">
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
            <button type="submit" class="btn btn-primary float-right">Install</button>
        </form>
    </div>
</div>
<script>
	function result(nextCall) {
		return function (data) {
			if (data.success) {
				alert('Success');
				nextCall();
			} else {
				alert('Error: ' + data.message);
			}
		}
	}
	function installComposer() {
		$.post('<?= $baseUrl; ?>/install/?step=composer', result(installConfig));
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
		$.post('<?= $baseUrl; ?>/install/?step=config', data, result(installMigrations));
	}

	function installMigrations() {
		$.post('<?= $baseUrl; ?>/install/?step=migrations', result(installAdmin));
	}

	function installAdmin() {
		var data = {
			login: $('#formAdminLogin').val(),
			email: $('#formAdminEmail').val(),
			pass: $('#formAdminPass').val()
		};
		$.post('<?= $baseUrl; ?>/install/?step=admin', data, result(installTasks));
	}

	function installTasks() {
		$.post('<?= $baseUrl; ?>/install/?step=tasks', result(finish));
	}

	function finish() {
		alert('Finished');
	}

	$('#installForm').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		installComposer();
	});
</script>
</body>
</html>
