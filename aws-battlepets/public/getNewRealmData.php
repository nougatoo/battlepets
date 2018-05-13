<?php

require_once('../scripts/util.php');

$configs = include('../application/configs/configs.php');
$characters = $_POST['characters'];
$realms = $_POST['realms'];

$url = 'https://us.api.battle.net/wow/character/' . $realms[0] . '/' . $characters[0] . '?fields=pets&locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud';

// TODO - Make this dynamic for the first character
$petsAPIResponse = file_get_contents($url);
$results = json_decode($petsAPIResponse, true);	
$cagedPetsRaw = $results['pets']['collected'];
$cagedPetsProc = [];
$cagedPetsIds = [];
$finalRealmData = [];

for($i = 0; $i<sizeof($cagedPetsRaw); $i++)
{
	array_push($cagedPetsProc, $cagedPetsRaw[$i]['stats']['speciesId']);
}

$cagedCounts = array_count_values($cagedPetsProc);

$petRestriction = "";

foreach($cagedCounts as $key => $petCount) {
	$petRestriction .= "'". $key . "',";
	array_push($cagedPetsIds, $key);
}

// Remove the last comma and add brackets
$petRestriction = "(" . substr($petRestriction, 0, -1) . ")";

$conn = dbConnect();
$sql = "SELECT sum(min_buyout) as realmSum, realm 
										FROM (
											SELECT species_id, min(buyout) AS min_buyout, realm
											FROM auctions_hourly_pet 
											WHERE species_id IN " . $petRestriction . "GROUP BY species_id, realm) b
										GROUP BY REALM
										ORDER BY realmSum DESC";
											

$result = $conn->prepare($sql);
$result->execute();	

if($result) {	
	while($row = $result->fetch()) {
		
		$realmPetId = [];
		$speciesDiff = null;
		$speciesDiffRestriction = "";
		$speciesDiffSum = 0;
		
		// Build an array of distinct species that exists on this realm's AH
		$realmSQL = "SELECT DISTINCT species_id FROM auctions_hourly_pet WHERE realm = '" . $row["realm"] . "'";
		$resultRealm = $conn->prepare($realmSQL);
		$resultRealm->execute();	
		
		if($resultRealm) {	
			while($row2 = $resultRealm->fetch()) {
				array_push($realmPetId, $row2['species_id']);
			}
		}
		
		// Find the pets that are caged, but don't exist on the realm
		$speciesDiff = array_diff($cagedPetsIds, $realmPetId);
		
		// Get the total market value for any pets that are in speciesDiff and add it to realmSum
		foreach($speciesDiff as $key => $species) {
			$speciesDiffRestriction .= "'" . $species . "',";
		}
		
		$speciesDiffRestriction = "(" . substr($speciesDiffRestriction, 0, -1) . ")";
		$speciesDiffSQL = "SELECT SUM(market_value_hist_median) as sum_median FROM market_value_pets_hist_median WHERE species_id IN " . $speciesDiffRestriction;
		$speciesDiffResult = $conn->prepare($speciesDiffSQL);
		$speciesDiffResult->execute();
		
		if($speciesDiffResult) {	
			while($row3 = $speciesDiffResult->fetch()) {
				$speciesDiffSum += $row3['sum_median'];
			}
		}
		
		$finalRealmData[$row["realm"]] = ($row["realmSum"] + $speciesDiffSum);
		//echo ( convertToWoWCurrency($row["realmSum"] + $speciesDiffSum) . " " . $row["realm"] . "<br/>");
	}
}

// Finds the max of connected realms
$completedRealms = [];

foreach($finalRealmData as $key => $realmValue) {
	
	if(!in_array($key, $completedRealms)) {
		$connectedRealms = [];
		$maxValue = 0;
		array_push($connectedRealms, $key);
		
		$sql = "SELECT slug_parent, slug_child FROM realms_connected WHERE slug_parent = '" . $key . "'";											
		$result = $conn->prepare($sql);
		$result->execute();	

		if($result) {	
			while($row = $result->fetch()) {
				array_push($connectedRealms, $row['slug_child']);
			}
		}	

		// Find the max value
		for($i = 0; $i < sizeof($connectedRealms); $i++) {		
			if($finalRealmData[$connectedRealms[$i]] > $maxValue)
				$maxValue = $finalRealmData[$connectedRealms[$i]];			
		}
		
		// Set all realms to the new max value
		for($i = 0; $i < sizeof($connectedRealms); $i++) {
			$finalRealmData[$connectedRealms[$i]] = $maxValue;
			array_push($completedRealms, $connectedRealms[$i]);
		}		
	}
}


arsort($finalRealmData);

$tableHTML =	'<table class="table table-striped table-hover realmTable">
						<tr style="background-color:white; color: #6b6b6b;">
							<th class="realmTableHeader">Realm</th>
							<th class="realmTableHeader">Value</th>
						</tr>
						<tbody id="myTable1">';
						
foreach($finalRealmData as $key => $realmValue) {
	
		$tableHTML .= '<tr>';
		$tableHTML .= '<td>' . getRealmNameFromSlug($key) .'</td>';
		$tableHTML .= '<td>' . convertToWoWCurrency($realmValue) . '</td>';
		$tableHTML .= '</tr>';
}

$tableHTML .= '</tbody></table>';
echo $tableHTML;

//echo ($cagedCounts);
/*
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
													<th class="realmTableHeader">'.getRealmNameFromSlug($realms[$j]).' Price</th>
													<th class="realmTableHeader">'.getRealmNameFromSlug($realms[$i]).' Price</th>
													<th class="realmTableHeader">Market Value</th>
													<th class="realmTableHeader">% Market Value</th>
												</tr>
												<tbody id="myTable1" class="realmTableBody">';			
					
			// Find good places to sell 
			$goodSellers = findSellersForRealm($realms[$j], $characters[$j], false);
			$goodSellersPrice = findSellersForRealm($realms[$j], $characters[$j], true);

			// Now that good sells contains only good selling pets that i do not own, we find good selling pets which are also good deals
			$goodDealsFiltered = array_intersect($goodDealsRawSpecies,$goodSellers);
			
			if(sizeof($goodDealsFiltered) > 0)
			{					
				$totalBuy = 0;
				$totalSell = 0;
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
							$subTableHTML .=  "<td>" . convertToWoWCurrency($goodSellersPrice[$row['species_id']]). "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist_median']) . "</td>";
							$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
							$subTableHTML .=  "</tr>";	
							
							$totalBuy += $row['minbuy'];
							$totalSell += $goodSellersPrice[$row['species_id']];
							$totalValue += $value;
						}			
				}

				if($subTableHTML == "")
					$emptyTable = true;
				
				$subTableHTML .= '<tr class="totalRow">';			
				$subTableHTML .=  '<td>'.'<b>Total <b/>'.'</td>';
				$subTableHTML .=  '<td>'.'<b>'.convertToWoWCurrency($totalSell).'</b>'.'</td>';
				$subTableHTML .=  '<td>'.'<b>'.convertToWoWCurrency($totalBuy).'</b>'.'</td>';
				$subTableHTML .=  '<td>'.'<b>'.convertToWoWCurrency($totalValue).'</b>'.'</td>';
				$subTableHTML .=  '<td>'.'</td>';
				$subTableHTML .=  '</tr>';
				
				// TODO - add real last updated
				$subTableHTML .= '<tr>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
												<td style="color: #ababab;font-size: 11px; text-align: right;">
													Last Updated: 12:54pm
												</td>
											</tr>';
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

*/
/**
	Finds good selling species_id from a given realm.

function findSellersForRealm($realm, $character, $returnPriceArray)
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
	$sellersAndPrice = []; // Array to hold the sells are buy price
	
	$result = $conn->prepare($sql);
	$result->bindParam(':realm', $realm);
	$result->execute();

	if($result) {
		while($row = $result->fetch()) {		
				array_push($sellers, $row['species_id']);		
				$sellersAndPrice[$row['species_id']] = $row['min_buyout'];		
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

	if($returnPriceArray)
		return $sellersAndPrice;
	else
		return $sellers;	
}
*/
/**
	Finds the good deals (buys) for a realm

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
*/
/**
	TODO

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
*/

/**
	TODO - rename

function createRealmTabs($realms)
{
	echo '<ul class="nav nav-stacked" style="padding-bottom: 15px;">';
	
	foreach($realms as $key=> $aRealm) {
		$realmTabHTML = '';
		$realmName = getRealmNameFromSlug($aRealm);
		
		if($key == 0)
			$realmTabHTML .= '<li class="active"><a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'<span class="glyphicon glyphicon-shopping-cart" style="padding-left:10px;color:#e6e6e600"></span></a></li>';
		else
			$realmTabHTML .= '<li> <a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'<span class="glyphicon glyphicon-shopping-cart" style="padding-left:10px;color:#e6e6e600"></a></li>';
	
		echo $realmTabHTML;
	}	
	
	echo '</ul>';	
}
*/
/**
	TODO

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
									  <a class="realmCollapse realmCollapse" data-toggle="collapse" href="#'.$realm.'_snipes"><b>All Realm Deals</b></a>
									</h4>
								</div>
								<div id="'.$realm.'_snipes" class="panel-collapse collapse in realmPanelCollapse">
									<table class="table table-striped table-hover realmTable">
										<tr  style="background-color:white; color: #6b6b6b;">
											<th class="realmTableHeader">Name</th>
											<th class="realmTableHeader">'.getRealmNameFromSlug($realm) .' Price</th>
											<th class="realmTableHeader">Market Value</th>
											<th class="realmTableHeader">% Market Value</th>
										</tr>
										<tbody id="myTable1" class="realmTableBody">';
									

	$subTableHTML = "";
	
	foreach($snipeDeals as $key => $row) {

		// TODO - check for duplicates. If this row's species id are the same as the last...skip iteration
		
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

	// TODO - get a real last updated
	$subTableHTML .= '<tr>
									<td></td>
									<td></td>
									<td></td>
									<td style="color: #ababab;font-size: 11px; text-align: right;">
										Last Updated: 12:54pm
									</td>
								</tr>';
	$tableHTML .=  $subTableHTML."</tbody></table></div></div><br/>"; 
			
	if(!$emptyTable)
		return $tableHTML;
}

*/





















?>