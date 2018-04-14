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
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="jquery-3.3.1.js"></script>
	<script src="index.js"></script>
	<link rel="stylesheet" href="myCustomStyle.css">
	<style>
		.table {
		  font-size: 12px !important;
		}
		
		.affix {
			top: 0;
			width: 100%;
			z-index: 9999 !important;
		}

		.affix + .container-fluid {
			padding-top: 70px;
		}
		
		.btn-primary {
			background-color:#333; 
			border-color:#333;
		
		}
		
		.btn-primary:hover {
			background-color:#1a1a1a; 
			border-color:#1a1a1a;
		
		}
		
		.btn-primary:focus {
			background-color:#333 !important; 
			border-color:#333 !important;
		
		}
		
		.btn-primary:active {
			background-color:#1a1a1a !important; 
			border-color:#1a1a1a !important;
		
		}
		
		.btn-bar {
			font-size: 16px !important;
			background-color:#333; 
			border-color:#333;
		
		}
		
		.btn-bar:hover {
			background-color:#1a1a1a; 
			border-color:#1a1a1a;
		
		}
		
		.btn-bar:focus {
			background-color:#1a1a1a !important; 
			border-color:#1a1a1a !important;
		
		}
		
		#backFooter {
			height: 30px !important;
		}
		
		.leggodeal {
		  background-color: #ffebcc !important;
		}
		.leggodeal:hover,
		.leggodeal:focus {
		  background-color: #ffe0b3 !important;
		}
		
		.epicdeal {
		  background-color: #e0d6f5 !important;
		}
		.epicdeal:hover,
		.epicdeal:focus {
		  background-color: #d1c2f0 !important;
		}
		
		.bluedeal {
		  background-color: #cce0ff !important;
		}
		.bluedeal:hover,
		.bluedeal:focus {
		  background-color: #b3d1ff !important;
		}
		
	</style>
</head>


<body>

<!--
<div class="container-fluid" style="background-color:#F44336;color:#fff;height:200px;">
	<h1>Bootstrap Affix Example</h1>
	<h3>Fixed (sticky) navbar on scroll</h3>
	<p>Scroll this page to see how the navbar behaves with data-spy="affix".</p>
	<p>The navbar is attached to the top of the page after you have scrolled a specified amount of pixels.</p>
</div> -->

<nav class="navbar navbar-inverse" data-offset-top="1" id="top" style="background-color:#333;">
	<div class="container-fluid">
		<div class="navbar-header">
			<a class="navbar-brand" href="#">Battle Pets</a>
		</div>
		<ul class="nav navbar-nav">
			<li class="active"><a href="#">Home</a></li>
			<li><a href="#">All Pets</a></li>
		</ul>
		<ul class="nav navbar-nav navbar-right">
			<li><a href="#">About me</a></li>
		</ul>
	</div>
</nav>

<div class="container-fluid">
	<div class="row" id="row1">
		<div class="col-sm-3" id="row1col1">
			<div style="background-color:#ffffff;border-radius:6px;">
				<div class="form-group">
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
									echo ('<option value="'.$row[slug].'">'.$row['name'].'</option>');
								}	
							}
						?>
					</select>
				</div>
				<br/>
				<div class="form-group">
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
									echo ('<option value="'.$row[slug].'">'.$row['name'].'</option>');
								}	
							}
						?>
					</select>
				</div>
				<br/>
				<div class="form-group">
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
									echo ('<option value="'.$row[slug].'">'.$row['name'].'</option>');
								}	
							}
						?>
					</select>
				</div>
				<br/>
				<div class="form-group">
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
									echo ('<option value="'.$row[slug].'">'.$row['name'].'</option>');
								}	
							}
						?>
					</select>
				</div>
				<table class="table">
					<tr>
						<td class="toggleTd">
							<label class="switch">
								<input id="commonSlider" type="checkbox" checked >
								<span class="slider round"></span>
							</label>
						</td>
						<td class="toggleTd">
							<label class="switch">
								<input id="greenSlider" type="checkbox" checked >
								<span class="slider round"></span>
							</label>
						</td>
						<td class="toggleTd">
							<label class="switch">
								<input id="blueSlider" type="checkbox" checked >
								<span class="slider round"></span>
							</label>
						</td class="toggleTd">
						<td class="toggleTd">
							<label class="switch">
								<input  id="epicSlider" type="checkbox" checked>
								<span class="slider round sliderEpic"></span>
							</label>
						</td>
						<td class="toggleTd">
							<label class="switch">
								<input  id="leggoSlider" type="checkbox" checked>
								<span class="slider round sliderEpic"></span>
							</label>
						</td>
					</tr>
				</table>
				<button id="findDealsButton" type="button" class="btn btn-primary btn-block" id="findDealsButton" onclick="findDeals()" style="margin-bottom:20px;">Find Deals</button>
			</div>
		</div>
		<div id="dataSection" class="col-sm-9" id="row1col2">
			<div id="buttonBar" class="btn-group btn-group-justified" style="display:none;">
			</div>
			<br/>
			<br/>
			<input class="form-control" id="dataFilter" type="text" placeholder="Search.." style="display:none;">
			<div id="loadingBar" style="display:none;">
				<center><h4>Loading</h4></center>
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width:100%; background-color:#333;"></div>
				</div>
			</div>
			<div id="tableArea">
				<center>
					<h1>Please select at least two characters</h1>
					<br/>
				</center>
			</div>
		</div>
	</div>
	<div class="row" id="row2">
		<div class="col-sm-12">
			<div id="backFooter" class="footer navbar-fixed-bottom" style="background-color:#222;color:white;display:none;">
				<h5 onclick="topFunction()"><center>Back to top</center></h5>
			</div>
	</div>
</div>



</div>
</body>
</html>
