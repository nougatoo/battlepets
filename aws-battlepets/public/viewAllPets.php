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
	<script src="publicCommon.js"></script>
	<link rel="stylesheet" href="myCustomStyle.css">
</head>

<body style="overflow-y: scroll; overflow-x: hidden; background-color: #e6e6e68a;">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#" style="font-size: 27px;color: #a9e7ff;letter-spacing: 1px;">Cross-Realm Pets &#9876;</a>
		</div>
		<ul class="nav navbar-nav" >
			<li><a href="http://battletpets-testing.us-east-2.elasticbeanstalk.com"  style="font-size: 16px;color: #a9e7ff;letter-spacing: 0.5px;">Home</a></li>
			<li  class="active"><a href="viewAllPets.php" style="font-size: 16px;letter-spacing: 0.5px;">All Pets</a></li>
			<li><a href="findNewRealm.php" style="font-size: 16px;color: #a9e7ff;letter-spacing: 0.5px;">Find a New Realm</a></li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li class="dropdown">
				<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<span class="glyphicon glyphicon-globe" style="padding-right:5px"></span>
				<span id="currentRegion">Region:</span>
				<span class="caret"></span></a>
				<ul class="dropdown-menu">
					<li><a href="#" style="text-align: center;border-bottom: 1px solid #a09e9e;" onclick="switchRegion(this)">US</a></li>
					<li><a href="#" style="text-align: center;" onclick="switchRegion(this)">EU</a></li>
				</ul>
			</li>		
			<li><a href="#" data-toggle="modal" data-target="#contactModal"><span class="glyphicon glyphicon-envelope" style="padding-right:5px"></span>Contact</a></li>
			<li><a href="#" data-toggle="modal" data-target="#rptBugModal"><span class="glyphicon glyphicon-pencil" style="padding-right:5px"></span>Report a Bug</a></li>
			<li><a href="#" data-toggle="modal" data-target="#faqModal"><span class="glyphicon glyphicon-info-sign" style="padding-right:5px"></span>FAQ</a></li>
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
			  <h5><span><b><b></span></h5>
	</center>
</footer>


<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h4 class="modal-title">Contact</h4>
		</div>
		<div class="modal-body">
		  <p>Please contact me at crossrealmpets@gmail.com</p>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div> 
	</div>
</div>

<!-- Report a Bug Modal -->
<div class="modal fade" id="rptBugModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h4 class="modal-title">Report a bug</h4>
		</div>
		<div class="modal-body">
		  <p>You can report bugs to crossrealmpets.bugs@gmail.com. Please include as much detail as possible, and screenshot when applicable.</p>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div> 
	</div>
</div>

<!-- FAQ Modal -->
<div class="modal fade" id="faqModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h4 class="modal-title">Frequently Asked Questions</h4>
		</div>
		<div class="modal-body">
		  <h4><b>Who should use this application?</b></h4>
		  <span style="font-weight:300;">This application is for...</span>
		  
		  <h4><b>Why don't the market values match TSM?</b></h4>
		  <span style="font-weight:300;">The market values don't match TMS because...</span>
		  
		  <h4><b>What are the default values?</b></h4>
		  <span style="font-weight:300;">The default mininum sell value is 75%. I'm planning on making this an option for users.</span>
		  
		  <h4><b>Is this application mobile friendly?</b></h4>
		  <span style="font-weight:300;">It's designed to be usable on mobile, but optimized for desktop</span>
		  
		  <h4><b>Why does "Last Updated" never change?</b></h4>
		  <span style="font-weight:300;">This is still under development. Currently the data is refreshed ever hour</span>
		  
		  <h4><b>What future changes do you have planned?</b></h4>
		  <span style="font-weight:300;">Here is what is on my TODO list: ...</span>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div>	  
	</div>
</div>
</body>
</html>
