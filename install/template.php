<?php
/**
 * @var string $baseUrl
 */
?>
<html>
<head>
	<title>GameX Install</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body>
<button id="installComposer">Install composer</button>
<hr>
<form id="installConfig">
	<fieldset>
		<legend>Database:</legend>
		<div>
			<label>Host:</label>
			<input type="text" name="db[host]" value="127.0.0.1">
		</div>
		<div>
			<label>Port:</label>
			<input type="text" name="db[port]" value="3306">
		</div>
		<div>
			<label>User:</label>
			<input type="text" name="db[user]" value="root">
		</div>
		<div>
			<label>Password:</label>
			<input type="text" name="db[pass]" value="">
		</div>
		<div>
			<label>Name:</label>
			<input type="text" name="db[name]" value="test">
		</div>
		<div>
			<label>Prefix:</label>
			<input type="text" name="db[prefix]" value="">
		</div>
	</fieldset>
    <input type="submit" value="Save">
</form>
<hr>
<button id="installMigrations">Run migrations</button>
<hr>
<form id="installUser">
    <div>
        <label>Email:</label>
        <input type="text" name="email" value="">
    </div>
    <div>
        <label>Password:</label>
        <input type="text" name="pass">
    </div>
    <input type="submit" value="Save">
</form>
<button id="installCronJobs">Install cron jobs</button>
<hr>
<script
	src="https://code.jquery.com/jquery-3.3.1.js"
	integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
	crossorigin="anonymous"></script>
<script>
	$('#installComposer').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$.post('<?= $baseUrl; ?>/install/?step=1', function (data) {
			console.log(data)
		});
	});
	$('#installConfig').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$.post('<?= $baseUrl; ?>/install/?step=2', $(this).serializeArray(), function (data) {
			console.log(data)
		});
	});
	$('#installMigrations').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$.post('<?= $baseUrl; ?>/install/?step=3', function (data) {
			console.log(data)
		});
	});
	$('#installUser').on('submit', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$.post('<?= $baseUrl; ?>/install/?step=4', $(this).serializeArray(), function (data) {
			console.log(data)
		});
	});
	$('#installCronJobs').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();
		$.post('<?= $baseUrl; ?>/install/?step=5', function (data) {
			console.log(data)
		});
	});
</script>
</body>
</html>
