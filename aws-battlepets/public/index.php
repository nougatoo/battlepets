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
	<script src="index.js"></script>
	<link rel="stylesheet" href="myCustomStyle.css">
</head>


<body style="overflow-y: scroll;">

<nav class="navbar navbar-inverse" data-offset-top="1" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">Battle Pets</a>
		</div>
		<ul class="nav navbar-nav">
			<li class="active"><a href="#">Home</a></li>
			<li><a href="viewAllPets.php">All Pets</a></li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li><a href="#">About me</a></li>
		</ul>
	</div>
</nav>

<div class="container-fluid">
	<div class="row" id="row1">
		<div class="col-sm-3" id="row1col1">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<center><a class="realmCollapse" data-toggle="collapse" href="#charSelect" style="color:black;">Select Characters and Realms</a></center>
					</h4>
				</div>
				<div id="charSelect" class="panel-collapse collapse in">
					<div class="form-group row" style="padding:20px;">
						<div class="col-xs-6">
							<input type="text" class="form-control" id="character1"  placeholder="Character 1" onchange="activateDealsButton()">
							<select class="form-control" id="realm1">
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
						<div class="col-xs-6">
							<input type="text" class="form-control" id="character2"  placeholder="Character 2">
							<select class="form-control" id="realm2">
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
					</div>
					<div class="form-group row" style="padding-left:20px;padding-right:20px;padding-bottom:20px;">
						<div class="col-xs-6">
							<input type="text" class="form-control" id="character3"  placeholder="Character 3">
							<select class="form-control" id="realm3">
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
						<div class="col-xs-6">
							<input type="text" class="form-control" id="character4"  placeholder="Character 4">
							<select class="form-control" id="realm4">
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
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<center><a class="realmCollapse" data-toggle="collapse" href="#optionsCollapse" style="color:black;">Options</a></center>
					</h4>
				</div>
				<div id="optionsCollapse" class="panel-collapse collapse">
					<div id="optionsDiv" class="panel-body">
						<h4 style="padding-left:8px;">Deal Qualities</h4>
						<table class="table">
							<tr>
								<td class="toggleTd" style="width: 20%">
									<label class="switch">
										<input id="commonSlider" type="checkbox" checked >
										<span class="slider round"></span>
									</label>
								</td>
								<td class="toggleTd" style="width: 20%">								
									<label class="switch">
										<input id="greenSlider" type="checkbox" checked >
										<span class="slider round"></span>
									</label>						
								</td>
								<td class="toggleTd" style="width: 20%">
									<label class="switch">
										<input id="blueSlider" type="checkbox" checked >
										<span class="slider round"></span>
									</label>								
								</td >
								<td class="toggleTd" style="width: 20%">
									<label class="switch">
										<input  id="epicSlider" type="checkbox" checked>
										<span class="slider round sliderEpic"></span>
									</label>								
								</td>
								<td class="toggleTd" style="width: 20%">	
									<label class="switch">
										<input  id="leggoSlider" type="checkbox" checked>
										<span class="slider round sliderEpic"></span>
									</label>							
								</td>
							</tr>
						</table>
						<h4 style="padding-left:8px;">Other</h4>
						<table class="table">
							<tr>
								<td class="toggleTd" style="width: 20%">
									<label class="switch">
										<input id="snipesSlider" type="checkbox" checked >
										<span class="slider round basicSlider"></span>
									</label>
								</td>			
								<td class="toggleTd" style="width: 20%">
									<label class="switch">
										<input id="collectedSlider" type="checkbox" checked >
										<span class="slider round basicSlider"></span>
									</label>
								</td>				
								<td class="toggleTd" style="width: 20%">
									<div class="form-group">
										<select class="form-control" id="selectMaxBuy" style="font-size: 11px;padding-right: 2px;">
											<?php
												require_once('../scripts/util.php');
												
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
								</td>				
								<td class="toggleTd" style="width: 20%">
								</td>
							</tr>
							<tr>
								<td>Show Snipes </td>			
								<td>Show Amount Caged</td>						
								<td>Max Buy %</td>						
							</tr>					
						</table>
					</div>
				</div>
			</div>
			<button id="findDealsButton" type="button" class="btn btn-default btn-block" onclick="findDeals()" style="color: #333;background-color: #f5f5f5;border-color: #ddd; padding-top:0px;padding-bottom:0px;"><h4>Find Deals <span class="glyphicon glyphicon-search"></span></h4></button>
		</div>
		<div id="dataSection" class="col-sm-9" id="row1col2">
			<div id="realmTabs">
			</div>
			<br/>
			<br/>
			<input class="form-control" id="dataFilter" type="text" placeholder="Filter..." style="display:none;">
			<div id="loadingBar" style="display:none;">
				<center><h4>Loading</h4></center>
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
				</div>
			</div>
			<div id="tableArea" class="tab-content">
				<center>
					<h1>Please select at least two characters</h1>
					<br/>
				</center>
			</div>
		</div>
	</div>
</div>
</div>
</body>
</html>
