<!doctype html>
<html>
<head>
A	<meta charset="UTF-8">
	<meta name="robots" content="noindex">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<meta http-equiv="refresh" content="5;url=<?= base_url('/') ?>">

	<title>Redirecting...</title>

	<style type="text/css">
		<?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'debug.css')) ?>
	</style>
</head>
<body>
<div class="container text-center">
	<h1 class="headline">Whoops!</h1>
	<p class="lead">We seem to have hit a snag. Please try again later...</p>
	<h5>You will be redirected to the home page in 5 seconds.</h5>
	<button type="button" class="btn btn-warning" onclick="history.back();">
		Go Back
	</button>
</div>
</body>
</html>
