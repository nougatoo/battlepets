<html>
	
<head>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-116514017-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'UA-116514017-1');
	</script>
	
	<title>Battle Pet Deals</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="jquery-3.3.1.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="viewAllPets.js"></script>
	<link rel="stylesheet" href="myCustomStyle.css">
</head>

<body style="overflow-y: scroll;">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#" style="font-size: 18px;">Battle Pets</a>
		</div>
		<ul class="nav navbar-nav" >
			<li><a href="http://battletpets-testing.us-east-2.elasticbeanstalk.com"  style="font-size: 18px;">Home</a></li>
			<li  class="active"><a href="viewAllPets.php" style="font-size: 18px;">All Pets</a></li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li><a href="#" style="font-size: 18px;">About me</a></li>
		</ul>
	</div>
</nav>


<div class="container-fluid">
	<div class="row" id="row1">
		<div class="row">
			<div class="col-sm-4">
			</div>
			<div class="col-sm-4">
				<div id="loadingBar">
					<center><h4>Loading</h4></center>
					<div class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
					</div>
				</div>
				<input class="form-control" id="dataFilter" type="text" placeholder="Filter..." style="display:none;">
				<div id="allPetsTable">					
				</div>
			</div> <!-- End of middle column -->
			<div class="col-sm-4">
			</div>
		</div>
	</div> <!-- End of Row -->
</div> <!-- End of Top Container -->

</body>
</html>
