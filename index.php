<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">

	<title>TheKadeshi test page</title>

	<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

</head>

<body>

<div class="container">
	<div class="header clearfix">
		<nav>
			<ul class="nav nav-pills pull-right">
				<li role="presentation" class="active"><a href="http://thekadeshi.com" target="_blank">Home</a></li>
			</ul>
		</nav>
		<h3 class="text-muted">TheKadeshi</h3>
	</div>

	<div class="jumbotron">
		<h1>Test page</h1>
		<p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
	</div>

	<div class="row">
		<form action="/" method="post" enctype="multipart/form-data">
			<div class="form-group">
				<label for="exampleInputEmail1">Email address</label>
				<input type="email" name="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
			</div>
			<div class="form-group">
				<label for="exampleInputPassword1">Password</label>
				<input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
			</div>
			<div class="form-group">
				<label for="exampleInputFile">File input</label>
				<input type="file" name="exampleInputFile" id="exampleInputFile">
				<p class="help-block">Some file. May be php</p>
			</div>
			<button type="submit" class="btn btn-default">Submit</button>
		</form>
	</div>

	<div class="row">
		<div class="col-xs-3">
			<p class="lead">GET:</p>
			<? print_r($_GET); ?>
		</div>
		<div class="col-xs-3">
			<p class="lead">POST:</p>
			<? print_r($_POST); ?>
		</div>
		<div class="col-xs-3">
			<p class="lead">REQUEST:</p>
			<? print_r($_REQUEST); ?>
		</div>
		<div class="col-xs-3">
			<p class="lead">FILES:</p>
			<? print_r($_FILES); ?>
		</div>
	</div>


</div> <!-- /container -->

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

</body>
</html>
