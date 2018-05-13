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

<body style="overflow-y: scroll; overflow-x: hidden; background-color: #e6e6e68a;">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#" style="font-size: 30px;color: #a9e7ff;letter-spacing: 1px;">Battle Pets &#9876;</a>
		</div>
		<ul class="nav navbar-nav" >
			<li><a href="http://battletpets-testing.us-east-2.elasticbeanstalk.com"  style="font-size: 16px;color: #a9e7ff;font-weight: bold;letter-spacing: 0.5px;">Home</a></li>
			<li  class="active"><a href="viewAllPets.php" style="font-size: 16px;font-weight: bold;letter-spacing: 0.5px;">All Pets</a></li>
			<li><a href="findNewRealm.php" style="font-size: 16px;color: #a9e7ff;font-weight: bold;letter-spacing: 0.5px;">Find a New Realm</a></li>
		</ul>
	</div>
</nav>


<div class="container-fluid" style="min-height:90%">
	<div class="row" id="row1">
		<div class="row">
			<div class="col-sm-4">
			</div>
			<div class="col-sm-4">
				<div id="loadingBar">
					<div class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
					</div>
				</div>
				<input class="form-control" id="dataFilter" type="text" placeholder="Filter..." style="display:none;box-shadow: none; border: 1px solid #e0e0e0;">
				<div class="panel panel-default realmPanel" style="margin-top:15px;">
					<div class="panel-heading realmPanelHeading">
						<h4 class="panel-title">	  
						  <a class="realmCollapse" data-toggle="collapse" href="#allPetsTable"><b>All Pets</b></a>
						</h4>
					</div>
					<div id="allPetsTable" class="panel-collapse collapse in realmPanelCollapse">		
					</div>
				</div>
			</div> <!-- End of middle column -->
			<div class="col-sm-4">
			</div>
		</div>
	</div> <!-- End of Row -->
</div> <!-- End of Top Container -->
<footer class="footer" style="min-height:5%">
	<center>	
			  <h5><span><b>Report a Bug  &middot; Contact &middot; FAQ<b></span></h5>
	</center>
</footer>
</body>
</html>
