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


<body style="overflow-y: scroll;background-color:#e6e6e68a" data-spy="scroll" data-target="#realmSpy" data-offset="20">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#" style="font-size: 18px;">Battle Pets</a>
		</div>
		<ul class="nav navbar-nav" >
			<li class="active"><a href="#"  style="font-size: 18px;">Home</a></li>
			<li><a href="viewAllPets.php" style="font-size: 18px;">All Pets</a></li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li><a href="#" style="font-size: 18px;">About me</a></li>
		</ul>
	</div>
</nav>

<div class="container-fluid">
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
			</div>
		</div>	
		<div id="dataSection" class="col-sm-6" id="row3col2">
			<h1 style="padding-bottom: 15px;">
					<span class="label label-default">Sell On</span>
			</h1>
			<div id="realmTabs" >
			</div>
			<!--<br/>
			<br/>-->
			<!--
			<div id="loadingBar" style="display:none;">
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
				</div>
			</div>
			-->
			<div id="tableArea" class="tab-content">
				<center>
					<h1></h1>
					<br/>
				</center>
			</div>
		</div>
		
		<div class="col-sm-3" id="row3col3" style="padding-left: 0px;">
			
		</div>
	</div>
	<div class="row" id="row4">
		<div class="col-sm-4" id="row4col1">
		</div>
		<div class="col-sm-4" id="row4col2" >
			<div id="charactersSpy" data-spy="affix" data-offset-top="50">
				<h1 style="padding-bottom: 15px;">
					<span class="label label-default">Find</span>
				</h1>
				<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
					<div class="panel-heading" style="border: none; background-color: white;">
						<h3 class="panel-title">
							<a class="realmCollapse" data-toggle="collapse" href="#charSelect" style="color:black; color: #00000094; font-weight: bold;">Characters &amp; Realms</a>
						</h3>
					</div>
					<div id="charSelect" class="panel-collapse collapse in">
						<form class="form-inline" style="padding-left:15px;padding-right:15px">
							<div class="form-group charFormGroup" style="width:100%;">
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
							<div class="form-group charFormGroup" style="width:100%;">
								<input style="width:49%;" type="text" class="form-control charInput" id="character2"  placeholder="Character 2">
								<select style="width:49%;" class="form-control realmInput" id="realm2">
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
							<div class="form-group charFormGroup" style="width:100%;">
								<input style="width:49%;" type="text" class="form-control charInput" id="character3"  placeholder="Character 3">
								<select style="width:49%;" class="form-control realmInput" id="realm3">
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
							<div class="form-group charFormGroup" style="width:100%;">
								<input style="width:49%;" type="text" class="form-control charInput" id="character4"  placeholder="Character 4">
								<select style="width:49%;" class="form-control realmInput" id="realm4">
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
					</div>
				</div>

				<button id="findDealsButton" type="button" class="btn btn-default btn-block" onclick="findDeals()"><h4 style="color: #6b6b6b;font-weight: bold;">Find Deals <span class="glyphicon glyphicon-search"></span></h4></button>
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

</body>
</html>
