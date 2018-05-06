<?php

require_once('../scripts/util.php');

$configs = include('../application/configs/configs.php');
$characters = $_POST['characters'];
$realms = $_POST['realms'];
$purpose = $_POST['purpose'];

$showCommon = $_POST['showCommon'] == "true";
$showGreen = $_POST['showGreen'] == "true";
$showBlue = $_POST['showBlue'] == "true";
$showEpic = $_POST['showEpic'] == "true";
$showLeggo = $_POST['showLeggo'] == "true";
$showSnipes = $_POST['showSnipes'] == "true";
$incCollected = $_POST['incCollected'] == "true";
$maxBuyPerc = $_POST['maxBuyPerc'];

if(!is_numeric($maxBuyPerc)) {
	echo ("Max buy Percent is not a number");
	return;
}

//$characters = json_decode($characters, true);

if($purpose == "realmTabs") {
	createRealmTabs($realms);
}
else {

	$petsAPIResponse = file_get_contents('https://us.api.battle.net/wow/character/cenarion-circle/irone?fields=pets&locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');
	$results = json_decode($petsAPIResponse, true);	
	$cagedPetsRaw = $results['pets']['collected'];
	$cagedPetsProc = [];
	
	for($i = 0; $i<sizeof($cagedPetsRaw); $i++)
	{
		array_push($cagedPetsProc, $cagedPetsRaw[$i]['stats']['speciesId']);
	}
	
	$cagedCounts = array_count_values($cagedPetsProc);
	
	$tableHTML ="";
	for($i = 0; $i<sizeof($realms); $i++)
	{
		
		// Show first realm data
		if($i == 0)
			echo '<div id="'.$realms[$i].'_tab" class="tab-pane fade in active">';
		else 
			echo '<div id="'.$realms[$i].'_tab" class="tab-pane fade">';
		
		$goodDealsRaw = findDealsForRealm($realms[$i], FALSE, $maxBuyPerc);
		$goodDealsRawSpecies = findDealsForRealm($realms[$i], TRUE, $maxBuyPerc);
							
		// Each iteration of this loop will attempt to create a cross realms deal table
		for($j = 0; $j<sizeof($realms); $j++)
		{
			if($realms[$i] === $realms[$j])
				continue;
	  
			$tableHTML =	'<div class="panel panel-default realmPanel">
										<div class="panel-heading realmPanelHeading">
											<h4 class="panel-title">	  
											  <a class="realmCollapse" data-toggle="collapse" href="#'.$realms[$i].'x'.$realms[$j].'"><b>'.getRealmNameFromSlug($realms[$j]) .'</b></a>
											</h4>
										</div>
										<div id="'.$realms[$i].'x'.$realms[$j].'" class="panel-collapse collapse in realmPanelCollapse">
											<table class="table table-hover realmTable">
												<tr style="background-color:white; color: #6b6b6b;">
													<th onclick="sortTable(this)" class="realmTableHeader">Name</th>
													<th class="realmTableHeader">Min Buy</th>
													<th class="realmTableHeader">Global Market Value</th>
													<th class="realmTableHeader">% Global Market Value</th>
												</tr>
												<tbody id="myTable1" class="realmTableBody">';			
					
			// Find good places to sell 
			$goodSellers = findSellersForRealm($realms[$j], $characters[$j]);

			// Now that good sells contains only good selling pets that i do not own, we find good selling pets which are also good deals
			$goodDealsFiltered = array_intersect($goodDealsRawSpecies,$goodSellers);
			
			if(sizeof($goodDealsFiltered) > 0)
			{					
				$totalBuy = 0;
				$totalValue = 0;
				$emptyTable = false;
	
				$subTableHTML = "";
				
				foreach($goodDealsRaw as $row) {
				
						if(in_array($row['species_id'], $goodDealsFiltered))
						{
							$value = $row['market_value_hist_median'];
							
							if($value >= $configs["threshLeggo"] && !$showLeggo)
								continue;
							elseif($value >= $configs["threshEpic"] && $value < $configs["threshLeggo"] && !$showEpic)
								continue;
							elseif($value >= $configs["threshBlue"] && $value < $configs["threshEpic"] && !$showBlue)
								continue;
							elseif($value >= $configs["threshGreen"] && $value < $configs["threshBlue"] && !$showGreen)
								continue;
							elseif($value < $configs["threshGreen"] && !$showCommon)
								continue;
								
							
							if($value > $configs["threshLeggo"])
								$subTableHTML .= '<tr class="leggodeal">';
							elseif($value > $configs["threshEpic"])
								$subTableHTML .= '<tr class="epicdeal">';
							elseif($value > $configs["threshBlue"])
								$subTableHTML .= '<tr class="bluedeal">';
							elseif($value > $configs["threshGreen"])
								$subTableHTML .= '<tr class="success">';
							else
								$subTableHTML.= '<tr>';
							
							if($incCollected)
								$subTableHTML .= "<td>" . $row['name'].'<span class="badge realmTableBadge">'.$cagedCounts[$row['species_id']].'</span>'."</td>";
							else
								$subTableHTML .= "<td>" . $row['name']."</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist_median']) . "</td>";
							$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
							$subTableHTML .=  "</tr>";	
							
							$totalBuy += $row['minbuy'];
							$totalValue += $value;
						}			
				}

				if($subTableHTML == "")
					$emptyTable = true;
				
				$subTableHTML .= '<tr class="totalRow">';			
				$subTableHTML .=  '<td>'.'<b>Total <b/>'.'</td>';
				$subTableHTML .=  '<td>'.'<b>'.convertToWoWCurrency($totalBuy).'</b>'.'</td>';
				$subTableHTML .=  '<td>'.'<b>'.convertToWoWCurrency($totalValue).'</b>'.'</td>';
				$subTableHTML .=  '<td>'.'</td>';
				$subTableHTML .=  '</tr>';
				//$subTableHTML .= '<tr style="background-color:white;"><td style="color:#ddd0;">asdf</td><td/><td/><td><td/></tr>'; // Blank Row
				$subTableHTML .= "</tbody></table></div></div>"; 	
				$tableHTML .=  $subTableHTML;
				
				if(!$emptyTable)
					echo $tableHTML;
			}
		}
		
		if($showSnipes)		
			echo (buildSnipesTables($realms[$i]));
		
		echo '</div>';
	}
}


/**
	Finds good selling species_id from a given realm.
*/
function findSellersForRealm($realm, $character)
{	
	global $configs;
	$conn = dbConnect();	
	$realmRes = buildingRealmRes($realm);	
	$sql = "
			SELECT 
				sell_realm.species_id, realm, realms.name as sell_realm_name, min_buyout, market_value_hist_median
			FROM (
				SELECT 
					species_id, realm, MIN(buyout) as min_buyout
				FROM (
					SELECT 
						id, species_id,  :realm as realm, buyout, bid, owner, time_left, quantity
					FROM
						auctions_hourly_pet
					WHERE 
						".$realmRes."
					) a
				GROUP by species_id, realm
				) sell_realm
			INNER JOIN market_value_pets_hist_median
				ON sell_realm.species_id = market_value_pets_hist_median.species_id
			INNER JOIN realms
				ON sell_realm.realm = realms.slug
			WHERE 
				min_buyout > (market_value_hist_median * ".$configs["minGblSellPercent"].");";
		
	$sellers = []; // species_id
	
	$result = $conn->prepare($sql);
	$result->bindParam(':realm', $realm);
	$result->execute();

	if($result) {
		while($row = $result->fetch()) {		
				array_push($sellers, $row['species_id']);		
		}
	}

	// Now we have a list of good sellers on this realm.
	// Lets remove all species that i'm already selling
	$currentlySelling = []; // Species_id
	
	$sellingResult = $conn->prepare("SELECT DISTINCT species_id FROM auctions_hourly_pet WHERE owner = ? and realm = ?");
	$sellingResult->bindParam(1, $character);
	$sellingResult->bindParam(2, $realm);
	$sellingResult->execute();

	if($sellingResult) {		
		while($row = $sellingResult->fetch()) {		
				array_push($currentlySelling, $row['species_id']);		
		}
	}

	$sellers = array_diff($sellers,$currentlySelling);

	return $sellers;	
}

/**
	Finds the good deals (buys) for a realm
*/
function findDealsForRealm($realm, $getSpecies, $minMarketPercent)
{
	global $configs;
	
	$conn = dbConnect();
	$realmRes = buildingRealmRes($realm);

	// Gets the pets that are a good deal on selected realm (Less than minMarketPercent global market avg)
	$goodDealsRawSql = "
		SELECT 
			pets.species_id, 
			pets.name, 
			floor(market_value_pets_hist_median.market_value_hist_median) as market_value_hist_median, 
			buy_realm.minbuy,
			round(buy_realm.minbuy/market_value_hist_median*100,2) as percent_of_market, 
			buy_realm.realm,
			buy_realm.buy_realm_name
		FROM 
			market_value_pets_hist_median
		INNER JOIN pets 
			ON pets.species_id = market_value_pets_hist_median.species_id
		INNER JOIN 
			(SELECT min(buyout) as minbuy, species_id, realm, realms.name as buy_realm_name FROM auctions_hourly_pet INNER JOIN realms ON realms.slug = realm WHERE buyout > 0  AND ".$realmRes. "GROUP BY species_id, realm) buy_realm
			ON pets.species_id = buy_realm.species_id
		WHERE 
			".$realmRes." AND 
			buy_realm.minbuy < (market_value_hist_median * ".$minMarketPercent.") AND
			market_value_hist_median - buy_realm.minbuy > ".$configs["minGblBuyAmount"]."
		ORDER BY percent_of_market;";

	$goodDealsRawSpecies = [];
	$goodDealsRaw = []; 
	$result = $conn->query($goodDealsRawSql);

	if($result) {
		// output data of each row
		while($row = $result->fetch()) {	
		
				array_push($goodDealsRawSpecies, $row['species_id']);
				array_push($goodDealsRaw, $row);				
		}
	}
		
	if($getSpecies)
		return $goodDealsRawSpecies;
	else
		return $goodDealsRaw;
}

/**
	TODO
*/
function buildingRealmRes($realm) 
{
	$conn = dbConnect();
	$realmRes = "(realm =  '".$realm."'";
	
	$result = $conn->prepare("SELECT slug_child FROM realms_connected WHERE slug_parent = ?");
	$result->bindParam(1, $realm);	
	$result->execute();	

	if($result) {	
		while($row = $result->fetch()) {
			$realmRes .= " OR realm = '" . $row['slug_child'] . "'";
		}
	}

	$realmRes .= ")";
	return $realmRes;
}


/**
	TODO - rename
*/
function createRealmTabs($realms)
{
	echo '<h1 style="padding-bottom: 15px;">
				<span class="label label-default colHeader">Buy</span>
			</h1>';
	echo '<ul class="nav nav-stacked" style="padding-bottom: 15px;">';
	
	foreach($realms as $key=> $aRealm) {
		$realmTabHTML = '';
		$realmName = getRealmNameFromSlug($aRealm);
		
		if($key == 0)
			$realmTabHTML .= '<li class="active"><a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'</a></li>';
		else
			$realmTabHTML .= '<li> <a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'</a></li>';
	
		echo $realmTabHTML;
	}	
	
	echo '</ul>';	
	

	/*
	echo '<div class="form-group has-feedback"><input class="form-control" id="dataFilter" type="text" placeholder="Filter Pets" style="display:none;box-shadow: none; border: 1px solid #e0e0e0;"><i class="form-control-feedback glyphicon glyphicon-filter" style="color: grey;"></i></div>';
	*/
	/*
	echo '<div class="panel panel-default" style="border: 1px solid #e0e0e0;">
				<div class="panel-heading" style="border: none; background-color: white;">
					<h3 class="panel-title">
						<a class="realmCollapse" data-toggle="collapse" href="#optionsCollapse"  style="color:black; color: #00000094; font-weight: bold;">Options</a>
					</h3>
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
										<select class="form-control" id="selectMaxBuy" style="font-size: 11px;padding-right: 2px;">';
												
	for($i = 10; $i<100; $i+=5) {
		if($i == 55) // Default is 55
			echo ('<option value="'.($i/100).'" selected="true">'.$i.'%</option>');
		else
			echo ('<option value="'.($i/100).'">'.$i.'%</option>');
	}
	
	echo							'</select>
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
			</div>';
			*/
}

/**
	TODO
*/
function buildSnipesTables($realm)
{
	global $showCommon, $showGreen, $showBlue, $showEpic, $showLeggo, $configs;
	$totalBuy = 0;
	$totalValue = 0;
	$emptyTable = false;
	$snipeDeals = findDealsForRealm($realm, FALSE, $configs['maxGblSnipePercent']);
	
	$petsAPIResponse = file_get_contents('https://us.api.battle.net/wow/character/cenarion-circle/irone?fields=pets&locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');
	$results = json_decode($petsAPIResponse, true);	
	$cagedPetsRaw = $results['pets']['collected'];
	$cagedPetsProc = [];
	
	for($i = 0; $i<sizeof($cagedPetsRaw); $i++)
	{
		array_push($cagedPetsProc, $cagedPetsRaw[$i]['stats']['speciesId']);
	}
	
	$cagedCounts = array_count_values($cagedPetsProc);
	
	$tableHTML = 	'<div class="panel panel-default realmPanel">
								<div class="panel-heading realmPanelHeading">
									<h4 class="panel-title">
									  <a class="realmCollapse realmCollapse" data-toggle="collapse" href="#'.$realm.'_snipes"><b>Snipes</b></a>
									</h4>
								</div>
								<div id="'.$realm.'_snipes" class="panel-collapse collapse in realmPanelCollapse">
									<table class="table table-striped table-hover realmTable">
										<tr  style="background-color:white; color: #6b6b6b;">
											<th class="realmTableHeader">Name</th>
											<th class="realmTableHeader">Min Buy</th>
											<th class="realmTableHeader">Global Market Value</th>
											<th class="realmTableHeader">% Global Market Value</th>
										</tr>
										<tbody id="myTable1" class="realmTableBody">';
									

	$subTableHTML = "";
	
	foreach($snipeDeals as $row) {

		$value = $row['market_value_hist_median'];
		
		if($value >=  $configs["threshLeggo"] && !$showLeggo)
			continue;
		elseif($value >= $configs["threshEpic"] && $value < $configs["threshLeggo"] && !$showEpic)
			continue;
		elseif($value >= $configs["threshBlue"] && $value < $configs["threshEpic"] && !$showBlue)
			continue;
		elseif($value >= $configs["threshGreen"] && $value < $configs["threshBlue"] && !$showGreen)
			continue;
		elseif($value < $configs["threshGreen"] && !$showCommon)
			continue;
			
		if($value > $configs["threshLeggo"])
			$subTableHTML .= '<tr class="leggodeal">';
		elseif($value > $configs["threshEpic"])
			$subTableHTML .= '<tr class="epicdeal">';
		elseif($value > $configs["threshBlue"])
			$subTableHTML .= '<tr class="bluedeal">';
		elseif($value > $configs["threshGreen"])
			$subTableHTML .= '<tr class="success">';
		else
			$subTableHTML.= "<tr>";
		
		$subTableHTML .= "<td>" . $row['name'].'<span class="badge realmTableBadge">'.$cagedCounts[$row['species_id']].'</span>'."</td>";
		$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
		$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist_median']) . "</td>";
		$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
		$subTableHTML .=  "</tr>";	
		
		$totalBuy += $row['minbuy'];
		$totalValue += $value;
					
	}
	
	if($subTableHTML == "")
			$emptyTable = true;
		
	$subTableHTML.= '<tr class="totalRow">';			
	$subTableHTML .=  "<td>"."<b>Total<b/>"."</td>";
	$subTableHTML .=  "<td>"."<b>".convertToWoWCurrency($totalBuy)."</b>"."</td>";
	$subTableHTML .=  "<td>"."<b>".convertToWoWCurrency($totalValue)."</b>"."</td>";
	$subTableHTML .=  "<td>"."</td>";
	$subTableHTML .=  "</tr>";
	
	$tableHTML .=  $subTableHTML."</tbody></table></div></div><br/>"; 
			
	if(!$emptyTable)
		return $tableHTML;
}























?>