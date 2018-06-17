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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="index.js"></script>
	<link rel="stylesheet" href="myCustomStyle.css">
</head>


<body style="overflow-y: scroll;background-color:#e6e6e68a" data-spy="scroll" data-target="#realmSpy" data-offset="20">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#" style="font-size: 27px;color: #a9e7ff;letter-spacing: 1px;">Cross-Realm Pets &#9876;</a>
		</div>
		<ul class="nav navbar-nav" >
			<li class="active"><a href="#"  style="font-size: 16px;letter-spacing: 0.5px;">Home</a></li>
			<li><a href="viewAllPets.php" style="font-size: 16px;color: #a9e7ff;letter-spacing: 0.5px;">All Pets</a></li>
			<li><a href="findNewRealm.php" style="font-size: 16px;color: #a9e7ff;letter-spacing: 0.5px;">Find a New Realm</a></li>
		</ul>	
		<ul class="nav navbar-nav navbar-right">
		  <li><a href="#" data-toggle="modal" data-target="#contactModal"><span class="glyphicon glyphicon-envelope" style="padding-right:5px"></span>Contact</a></li>
		  <li><a href="#" data-toggle="modal" data-target="#rptBugModal"><span class="glyphicon glyphicon-pencil" style="padding-right:5px"></span>Report a Bug</a></li>
		  <li><a href="#" data-toggle="modal" data-target="#faqModal"><span class="glyphicon glyphicon-info-sign" style="padding-right:5px"></span>FAQ</a></li>
		</ul>
	</div>
</nav>

<div class="container-fluid" style="min-height:90%">
	<div class="row" id="row1" style="display:none">
		<div class="col-sm-12" id="row1col1">	
		</div>
	</div>
	<div class="row" id="row2" style="display:none">
		<div class="col-sm-2" id="row2col2">
			
		</div>
		<div class="col-sm-2" id="row2col3">
		</div>
	</div>
	<div class="row" id="row3" style="display:none">
		<div class="col-sm-3" id="row3col1" style="padding-right: 0px;">
			<div id="realmSpy" data-spy="affix" data-offset-top="50">
				<div id="realmSpyDynammic">
				
				</div>
				<div id="realmSpySatic">
					<div class="form-group has-feedback">
						<input class="form-control" id="dataFilter" type="text" placeholder="Filter Pets.." style="display:none;box-shadow: none; border: 1px solid #e0e0e0;">
						<i class="form-control-feedback glyphicon glyphicon-filter" style="color: grey;"></i>
					</div>
					<div class="panel panel-default" style="border: 1px solid #e0e0e0; margin-bottom: 0px;">
						<div class="panel-heading" style="border: none; background-color: white;">
							<h3 class="panel-title">
								<a class="realmCollapse" data-toggle="collapse" href="#legendCollapse"  style="color:black; color: #00000094; font-weight: bold;">Legend</a>
							</h3>
						</div>
						<div id="legendCollapse" class="panel-collapse collapse">
							<div id="legendDiv" class="panel-body">
								<table class="table">
									<tr>
										<td class="toggleTd" style="width: 25%">
											<h4><span class="label label-default legendColorLabel" style="background-color:#f7e0bc ;">>50,000g</span></h4>
										</td>
										<td class="toggleTd" style="width: 25%">
											<h4><span class="label label-default legendColorLabel" style="background-color:#ddcffb;">>15,000g</span></h4>
										</td>
										<td class="toggleTd" style="width: 25%">
											<h4><span class="label label-default legendColorLabel" style="background-color:#bdd5fb;">>6,000g</span></h4>
										</td>
										<td class="toggleTd" style="width: 25%">
											<h4><span class="label label-default legendColorLabel" style="background-color:#daf3d0;">>3,000g</span></h4>
										</td>
									</tr>
									<tr>
										<td style="width: 25%">
											<h4><span class="badge realmTableBadgeLegend"># Owned</span></h4>
										</td>
										<td style="width: 25%">
										</td>
										<td style="width: 25%">	
										</td>
										<td style="width: 25%">										
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="panel panel-default" style="border: 1px solid #e0e0e0; margin-bottom: 0px;">
						<div class="panel-heading" style="border: none; background-color: white;">
							<h3 class="panel-title">
								<a class="realmCollapse" data-toggle="collapse" href="#optionsCollapse"  style="color:black; color: #00000094; font-weight: bold;">Options</a>
							</h3>
						</div>
						<div id="optionsCollapse" class="panel-collapse collapse">
							<div id="optionsDiv" class="panel-body">
								<table class="table">
									<tr>
										<td class="toggleTd" style="width: 20%">
											<div class="form-group" style="margin-bottom:0px;">
												<select class="form-control" id="selectMaxBuy" style="font-size: 11px;padding-right: 2px;background-color: #eeeeee;border: none;box-shadow: none;">
													<?php
														for($i = 10; $i<100; $i+=5) {
															if($i == 55) // Default is 55
																echo ('<option value="'.($i/100).'" selected="true">'.$i.'%</option>');
															else
																echo ('<option value="'.($i/100).'">'.$i.'%</option>');
														}
													?>
											</select>
											</div>
										</td>	
										<td class="toggleTd" style="width: 20%">
											<!--
											<label class="switch">
												<input id="snipesSlider" type="checkbox" checked >
												<span class="slider round basicSlider"></span>
											</label>
											-->
										</td>			
										<td class="toggleTd" style="width: 20%">
											<!--
											<label class="switch">
												<input id="collectedSlider" type="checkbox" checked >
												<span class="slider round basicSlider"></span>
											</label>
											-->
										</td>							
										<td class="toggleTd" style="width: 20%">
										</td>				
										<td class="toggleTd" style="width: 20%">
										</td>
									</tr>
									<tr>
										<td>Max Buy %</td>	
										<td><!--Show Snipes --></td>			
										<td><!-- Show Amount Caged --></td>											
									</tr>					
								</table>
							</div>
						</div>
					</div>
					<div id="helpSection" style="float:right; color: #ababab;cursor: pointer;">
						<h2>
								<span class="glyphicon glyphicon-question-sign" data-toggle="modal" data-target="#myModal"/>
						</h2>
					</div>
					<div id="myModal" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Help</h4>
								</div>
								<div class="modal-body">
									<p>In the future, this will help you!</p>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
		<div id="dataSection" class="col-sm-6" id="row3col2">
			<div id="tableArea" class="tab-content">
				<center>
					<h1></h1>
					<br/>
				</center>
			</div>
		</div>
		<div class="col-sm-3" id="row3col3" style="padding-left: 0px;">
			<div id="charactersSpy" data-spy="affix" data-offset-top="50">
				<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
					<div class="panel-heading" style="border: none; background-color: white;">
						<h3 class="panel-title">
							<a class="realmCollapse" data-toggle="collapse" href="#charSelectb" style="color:black; color: #00000094; font-weight: bold;">Characters &amp; Realms</a>
						</h3>
					</div>
					<div id="charSelectb" class="panel-collapse collapse in">
						<form id="charSelectFormb" class="form-inline" style="padding-left:15px;padding-right:15px">
						</form>
						<div id="addExtraRealmb" style="text-align: right;    padding-right: 25px;padding-bottom:12px;">
							<button type="button" class="addExtraRealmButtonb" style="border:none;font-weight: bold;color: #6b6b6b;background-color: white;padding-right:0px;" onclick="addRealmClickb()">
								<span class="glyphicon glyphicon-plus" style="padding-right: 5px;"></span>
								Add Realm
							</button>
						</div>
					</div>
				</div>
				<button id="findDealsButtonb" type="button" class="btn btn-default btn-block" onclick="findDeals()"><h4 style="color: #6b6b6b;font-weight: bold;">Find Pets <span class="glyphicon glyphicon-search" style="color: #6b6b6b;"></span></h4></button>
				<br/>
				<div id="loadingBarb" style="display:none;">
					<div class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row" id="row4">
		<div class="col-sm-4" id="row4col1">
		</div>
		<div class="col-sm-4" id="row4col2" >
			<div id="charactersSpy" data-spy="affix" data-offset-top="50">
				<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
					<div class="panel-heading" style="border: none; background-color: white;">
						<h3 class="panel-title">
							<a class="realmCollapse" data-toggle="collapse" href="#charSelect" style="color:black; color: #00000094; font-weight: bold;">Characters &amp; Realms</a>
						</h3>
					</div>
					<div id="charSelect" class="panel-collapse collapse in">
						<form id="charSelectForm" class="form-inline" style="padding-left:15px;padding-right:15px">
							<div id="realmSelectDiv1" class="form-group charFormGroup" style="width:100%;">
								<input style="width:49%;" type="text" class="form-control charInput" id="character1"  placeholder="Character 1" onchange="activateDealsButton()">
								<select style="width:49%;" class="form-control realmInput" id="realm1">
									<option></option>
									<?php
										require_once('../scripts/util.php');
										
										$conn = dbConnect();
										
										
										$sql = "SELECT slug, name FROM realms";
										$result = $conn->query($sql);
										
										if($result) {
											while($row = $result->fetch()) {
												echo ('<option value="'.$row['slug'].'">'.$row['name'].'</option>');
											}	
										}
									?>
								</select>
							</div>
						</form>
						<div id="addExtraRealm" style="text-align: right;    padding-right: 25px;padding-bottom:12px;">
							<button type="button" class="addExtraRealmButton" style="border:none;font-weight: bold;color: #6b6b6b;background-color: white;padding-right:0px;" onclick="addRealmClick()">
								<span class="glyphicon glyphicon-plus" style="padding-right: 5px;"></span>
								Add Realm
							</button>
						</div>
					</div>
				</div>

				<button id="findDealsButton" type="button" class="btn btn-default btn-block" onclick="findDeals()"><h4 style="color: #6b6b6b;font-weight: bold;">Find Pets <span class="glyphicon glyphicon-search" style="color: #6b6b6b;"></span></h4></button>
				<br/>
				<div id="loadingBar" style="display:none;">
					<div class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
					</div>
				</div>
			</div>
		<div class="col-sm-4" id="row4col3" >
		</div>
	</div>
</div>
</div>
<footer class="footer" style="min-height:5%">
	<center>	
			  <h5><span><b>Report a Bug  &middot; Contact &middot; <span data-toggle="modal" data-target="#faqModal">FAQ</span><b></span></h5>
	</center>
</footer>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h4 class="modal-title">Modal Header</h4>
		</div>
		<div class="modal-body">
		  <p>Here is how to contact me</p>
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
		  <h4 class="modal-title">Modal Header</h4>
		</div>
		<div class="modal-body">
		  <p>Here is how to report a bug</p>
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
		  <h4>Who should use this application?</h4>
		  <span>This application is for...</span>
		  
		  <h4>Why don't the market values match TSM?</h4>
		  <span>The market values don't match TMS because...</span>
		  
		  <h4>Is this application mobile friendly?</h4>
		  <span>It's designed to be usable on mobile, but optimized for desktop</span>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div>
	  
	</div>
</div>

</body>
</html>
















