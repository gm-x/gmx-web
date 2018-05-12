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
<script
	src="https://code.jquery.com/jquery-3.3.1.js"
	integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
	crossorigin="anonymous"></script>
<script>
	$('#installComposer').on('click', function (e) {
		$.post('<?= $baseUrl; ?>/install/?step=1', function (data) {
			console.log(data)
		});
	})
</script>
</body>
</html>
