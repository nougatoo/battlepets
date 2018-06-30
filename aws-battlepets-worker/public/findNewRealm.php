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
	
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
		(adsbygoogle = window.adsbygoogle || []).push({
		google_ad_client: "ca-pub-9728348108995055",
		enable_page_level_ads: true
		});
	</script>
	
	<title>Cross Realm Pets</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="/js/jquery-3.3.1.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script src="/js/findNewRealm.js"></script>
	<script src="/js/publicCommon.js"></script>	
	<link rel="stylesheet" href="/css/battlePetsCommon.css">
	<link rel="shortcut icon" type="image/png" href="graphics/favicon.ico"/>
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
</head>


<body style="overflow-y: scroll;background-color:#e6e6e68a" data-spy="scroll" data-target="#realmSpy" data-offset="20">

<nav class="navbar navbar-inverse" id="top" style="background-color:#333;font-family: 'Roboto', sans-serif;">
	<div class="container-fluid">
		<div class="navbar-header">
			<span class="navbar-brand" href="#" style="font-size: 33px;color: #a9e7ff;">Cross Realm Pets</span>
		</div>
		<ul class="nav navbar-nav" >
			<li ><a href="http://crossrealmpets.com"  style="font-size: 19px;color: #a9e7ff;">Home</a></li>
			<li><a href="viewAllPets.php" style="font-size: 19px;color: #a9e7ff;">All Pets</a></li>
			<li class="active"><a href="#" style="font-size: 19px;	color: #ffafa9;border-bottom: 3px solid #ffafa9;border-radius: 2px;">Find a New Realm</a></li>
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

<div class="container-fluid" style="min-height:85%;font-family: 'Roboto', sans-serif;">
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
		<div class="col-sm-4" id="row3col1" style="padding-right: 0px;">
			<div id="realmSpy" data-spy="affix" data-offset-top="50">
				<div id="realmSpySatic">
					<div class="form-group has-feedback">
						<input class="form-control" id="dataFilter" type="text" placeholder="Filter Realms.." style="display:none;box-shadow: none; border: 1px solid #e0e0e0;">
						<i class="form-control-feedback glyphicon glyphicon-filter" style="color: grey;"></i>
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
		<div id="dataSection" class="col-sm-4" id="row3col2">
			<div class="panel panel-default realmPanel">
				<div class="panel-heading realmPanelHeading">
					<h4 class="panel-title">	  
					  <a class="realmCollapse" data-toggle="collapse" href="#realmValueTable"><b>Realm Values</b></a>
					</h4>
				</div>	
				<div id="realmValueTable" class="panel-collapse collapse in realmPanelCollapse">		
				</div>					
			</div>
		</div>
		<div class="col-sm-4" id="row3col3" style="padding-left: 0px;">
			<div id="charactersSpy" data-spy="affix" data-offset-top="50">
				<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
					<div class="panel-heading" style="border: none; background-color: white;">
						<h3 class="panel-title">
							<a class="realmCollapse" data-toggle="collapse" href="#charSelectb" style="color:black; color: #00000094; font-weight: bold;">Character From Your Account</a>
						</h3>
					</div>
					<div id="charSelectb" class="panel-collapse collapse in">
						<form id="charSelectFormb" class="form-inline" style="padding-left:15px;padding-right:15px">
						</form>
					</div>
				</div>

				<button id="findRealmButton" type="button" class="btn btn-default btn-block" onclick="findNewRealm()"><h4 style="color: #6b6b6b;font-weight: bold;">Find New Realm <span class="glyphicon glyphicon-search" style="color: #6b6b6b;"></span></h4></button>
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
			<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
				<div class="panel-heading" style="border: none; background-color: white;">
					<h3 class="panel-title">
						<a class="realmCollapse" data-toggle="collapse" href="#welcomeMessage" style="color:black; color: #00000094; font-weight: bold;">Welcome!</a>
					</h3>
				</div>
				<div id="welcomeMessage" class="panel-collapse collapse in realmPanelCollapse">
					<div style="color: #6b6b6b;font-size: 14px;">
						<br/>
						<span>
							<b style="color:black;">Cross Realm Pets <sup>BETA</sup></b> is a tool that tells you where to <b style="color:black;">buy</b> and <b style="color:black;">sell</b> your World of Warcraft pets to <b style="color:black;">maximize cross realm/server profits</b>  - All based on <b style="color:black;">your </b>characters and realms
							<br/>
							<br/>
							What to expect from the  <b style="color:black;">Find a New Realm Page:</b>
						</span>
						<ul>
							<li style="padding-left: 5px;padding-top: 5px;padding-bottom: 5px;padding-top: 10px;">Helps you make a decision about <b style="color:black;">which realm to sell pets on next</b></li>
							<li style="padding-left: 5px;padding-top: 5px;padding-bottom: 5px;padding-top: 5px;">Displays a <b style="color:black;">sum of Region Market Value</b> for your account's pets per realm</li>
							<li style="padding: 5px;"><b style="color:black;">Supplumental information</b> - to be used with other realm statistics</li>
						</ul>
						<span>Please be patient and don't give up on me while I work through this <b style="color:black;">BETA</b> phase!  <i class="far fa-smile"></i></span>
						<br/>
						<br/>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-4" id="row4col2" >
			<div id="charactersSpy" data-spy="affix" data-offset-top="50">
				<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
					<div class="panel-heading" style="border: none; background-color: white;">
						<h3 class="panel-title">
							<a class="realmCollapse" data-toggle="collapse" href="#charSelect" style="color:black; color: #00000094; font-weight: bold;">Character From Your Account</a>
						</h3>
					</div>
					<div id="charSelect" class="panel-collapse collapse in">
						<form id="charSelectForm" class="form-inline" style="padding-left:15px;padding-right:15px">
							<div id="realmSelectDiv1" class="form-group charFormGroup" style="width:100%;">
								<input style="width:49%;" type="text" class="form-control charInput" id="character1"  placeholder="Character 1">
								<select style="width:49%;" class="form-control realmInput" id="realm1">
								</select>
							</div>
						</form>
					</div>
				</div>
				<button id="findRealmButton" type="button" class="btn btn-default btn-block" onclick="findNewRealm()"><h4 style="color: #6b6b6b;font-weight: bold;">Find New Realm <span class="glyphicon glyphicon-search" style="color: #6b6b6b;"></span></h4></button>
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
			  <h5>
				  <a href="privacypolicy.htm" target="_blank" style="color:#5f5f5f;padding-right:5px;"><b>Privacy Policy<b></a>
				  &#8226;
				  <a href="#" style="color:#5f5f5f;padding-right:5px;padding-left:5px;"><b>Change Log<b></a>
			  </h5>
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
		  <p>Please contact me at crossrealmpets@gmail.com or directly through reddit to /u/nougatoo</p>
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
			<form>
				<div class="form-group">
					<label for="bugReportText">Explain your bug:</label>
					<textarea class="form-control" rows="5" id="bugReportText" style="font-weight:300;resize: none;" maxlength="1000"></textarea>
				</div>
				<button type="button" class="btn btn-default" onclick="submitBugReport($('#bugReportText').val())">Submit</button>
			</form>		
		  <p>Alternatively, you can report bugs to crossrealmpets.bugs@gmail.com or through reddit to /u/nougatoo. Please include as much detail as possible.</p>
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
		  <span style="font-weight:300;"><ul><li>This is for small/medium goblins in the pet market. When you have limited gold on a server, it's best to buy pets that are already selling well on your other realms. This application will hopefully find those pets for you.</li></ul></span>
		  
		  <h4><b>Who is this NOT for?</h4>
		  <span style="font-weight:300;"><ul><li>While still totally usuable by large goblins, they may get less value out of it. Once you're trading on enough realms, you don't have to worry about buying a pet that's not selling well on your other realms. You're probably trading on enough realms that within a short amount of time, one of your realms will have it around ~100% Region Market Value.</li></ul></span>
		  
		  <h4><b>How do I use it? / How does it work?</h4>
		  <span style="font-weight:300;"><ul><li>Enter your current pet-selling characters and then hit Find Pets. The application will tell you which server to buy them on and which server(s) you could sell them on. In addition, it will tell you if you already own that pet and how many you own (this assumes all your pet-selling characaters are on the same account).</li><li>It works by first, finding pets on your realm that are selling for <b>below</b> your specified "Max Buy %". Then it will that that list of pets, and search all your specified realms to find pets that are selling <b>above</b> your "Min Sell %". This final list is then displayed to you.</li></ul></span>
		  
		  <h4><b>Why don't the market values match TSM?</b></h4>
		  <span style="font-weight:300;"><ul><li>This application does not pull data from TSM. It creates and stores it's own pricing data from the blizzard API. I've attempted to match TSM's values as closely as possible. It's definitely not perfect and you'll see variation at times.</li></ul></span>
		  
		  <h4><b>What are the default values?</b></h4>
		  <span style="font-weight:300;"><ul><li>The default mininum sell value is 75% region market value and the default maxium buy value is 50% region market value.</li></ul></span>
		  
		  <h4><b>How does the "Find a New Realm" page work?</b></h4>
		  <span style="font-weight:300;"><ul><li>This is a non-sophisticated way to see the value of your current pet collection by realm. This page is not meant to tell you exactly which realm you should start sell on next. Instead, it's one piece of the puzzle. You need to use other information like realm population and realm type to make an informed decision.</li></ul></span>
		  
		  <h4><b>Is this application mobile friendly?</b></h4>
		  <span style="font-weight:300;"><ul><li>It's designed to be usable on mobile, but optimized for desktop</li></ul></span>
		  
		  <h4><b>What future changes do you have planned?</b></h4>
		  <span style="font-weight:300;"><ul><li>Table Sorting</li><li>Make character selection optional so you can just compare realms</li><li>Possibly add links to wowhead.com for more details of the pet</li><li>Write the text for the question mark icons</li></ul></span>
		  
		  <h4><b>Known Bugs</b></h4>
		  <span style="font-weight:300;"><ul><li>Duplicates pets showing for connected realms</li></ul></span>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div>	  
	</div>
</div>
</body>
</html>
















