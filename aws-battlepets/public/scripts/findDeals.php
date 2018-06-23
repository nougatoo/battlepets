<?php

require_once('../../scripts/util.php');

$configs = include('../../application/configs/configs.php');
$characters = $_POST['characters'];
$realms = $_POST['realms'];
$purpose = $_POST['purpose']; // Used to avoid having another .php files...may refactor later
$region = $_POST['region'];
$showCommon = $_POST['showCommon'] == "true";
$showGreen = $_POST['showGreen'] == "true";
$showBlue = $_POST['showBlue'] == "true";
$showEpic = $_POST['showEpic'] == "true";
$showLeggo = $_POST['showLeggo'] == "true";
$showSnipes = $_POST['showSnipes'] == "true";
$incCollected = $_POST['incCollected'] == "true";
$maxBuyPerc = $_POST['maxBuyPerc'];
$minSellPrice = $_POST['minSellPrice'];
$locale;

// In case someone tried to change in javascript
if(!is_numeric($maxBuyPerc) || !is_numeric($minSellPrice) ) {
	echo ("One of the options is not numeric!");
	return;
}

// Hard-coded way to get the local
if($region == "US")
	$locale  = "en_US";
else if ($region == "EU")
	$locale = "en_GB";

if($purpose == "realmTabs") {
	createRealmList($realms);
}
else {
	
	// Gets the current pets owned by the account of the first character entered
	$petsAPIResponse = file_get_contents('https://'.$region.'.api.battle.net/wow/character/cenarion-circle/'.$characters[0].'?fields=pets&locale='.$locale.'&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');
	$results = json_decode($petsAPIResponse, true);	
	$cagedPetsRaw = $results['pets']['collected']; // The raw comes with a bunch of extra data that we don't need
	$cagedPetsProc = [];
	
	// Create a "processed" version of the array that is just the species id
	for($i = 0; $i<sizeof($cagedPetsRaw); $i++) {
		array_push($cagedPetsProc, $cagedPetsRaw[$i]['stats']['speciesId']);
	}
	
	$cagedCounts = array_count_values($cagedPetsProc);
	$tableHTML ="";
	
	// Loop through all the realms that user entered and create the data tables for them
	for($i = 0; $i<sizeof($realms); $i++)
	{
		
		// Show first realm data
		if($i == 0)
			echo '<div id="'.$realms[$i].'_tab" class="tab-pane fade in active">';
		else 
			echo '<div id="'.$realms[$i].'_tab" class="tab-pane fade">';
		
		// The "raw" contains all the pricing data, along with name and id information
		$goodDealsRaw = findDealsForRealm($realms[$i], FALSE, $maxBuyPerc); 
		$goodDealsRawSpecies = findDealsForRealm($realms[$i], TRUE, $maxBuyPerc);
							
		// Each iteration of this loop will attempt to create a cross realms deal table
		for($j = 0; $j<sizeof($realms); $j++)
		{
			// Can't compare a realm to itself
			if($realms[$i] === $realms[$j])
				continue;
	  
			$tableHTML =		'<div class="panel panel-default realmPanel">
										<div class="panel-heading realmPanelHeading">
											<h4 class="panel-title">	  
											  <a class="realmCollapse" data-toggle="collapse" href="#'.$realms[$i].'x'.$realms[$j].'"><b>'.getRealmNameFromSlug($realms[$j], $region) .'</b></a>
											</h4>
										</div>
										<div id="'.$realms[$i].'x'.$realms[$j].'" class="panel-collapse collapse in realmPanelCollapse">
											<table class="table table-hover realmTable">
												<tr style="background-color:white; color: #6b6b6b;">
													<th onclick="sortTable(this)" class="realmTableHeader">Name</th>
													<th class="realmTableHeader">'.getRealmNameFromSlug($realms[$j], $region).' Price</th>
													<th class="realmTableHeader">'.getRealmNameFromSlug($realms[$i], $region).' Price</th>
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
				
				// Creating a table row for each pet
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

				// $subTableHTML will be empty if there were no deals for a realm
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
	
	@param String $realm - The realm slug to find good sellers for
	@param String $character - The character on the realm used to sell pets
	@param bool $returnPriceArray - True if you want the price in the array was well. False for just the species
	
	@return Array - Array of good selling species id's. Can contain price as well
*/
function findSellersForRealm($realm, $character, $returnPriceArray)
{	
	global $configs, $region, $minSellPrice;
	$conn = dbConnect($region);	
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
				min_buyout > (market_value_hist_median * ".$minSellPrice.");";
		
	$sellers = []; // species_id
	$sellersAndPrice = []; // Array to hold the sells and buy price
	
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

/**
	Finds the good deals (buys) for a realm.
	
	@param String $realm - The realm to find good deals for
	@param bool $getSpecies - True if you want just the species, false if you want the raw row object from the query
	@param int $maxBuyPerc - The max market % you would buy a pet for
	
	@return Array - Array of objects or strings
*/
function findDealsForRealm($realm, $getSpecies, $maxBuyPerc)
{
	global $configs, $region;
	
	$conn = dbConnect($region);
	$realmRes = buildingRealmRes($realm);

	// Gets the pets that are a good deal on selected realm (Less than maxBuyPerc global market avg)
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
			buy_realm.minbuy < (market_value_hist_median * ".$maxBuyPerc.") AND
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
	Builds a SQL restriction for a realm to include all it's connected realms.
	Restriction will contain just the realm if it doesn't have any connected realms
	
	@param String $realm - The realm to build a restriction of and find connected realms for
	
	@return String $realmRes - SQL restriction for passed in realm and it's connected realm
*/
function buildingRealmRes($realm) 
{
	global $region, $locale; 
	$conn = dbConnect($region);
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
	Creates the left-hand side realm list. One for each of the distinct realms the user selected.
	Outputs the realm list html.
	
	@param Array $realms - Array of strings for all the realms we're finding deals for 
*/
function createRealmList($realms)
{
	global $region;
	
	echo '<ul class="nav nav-stacked" style="padding-bottom: 15px;">';
	
	foreach($realms as $key=> $aRealm) {
		$realmListHTML = '';
		$realmName = getRealmNameFromSlug($aRealm,$region);
		
		if($key == 0)
			$realmListHTML .= '<li class="active"><a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'<span class="glyphicon glyphicon-shopping-cart" style="padding-left:10px;color:#e6e6e600"></span></a></li>';
		else
			$realmListHTML .= '<li> <a class="buyRealmList" data-toggle="tab" href="#'.$aRealm.'_tab">'.$realmName.'<span class="glyphicon glyphicon-shopping-cart" style="padding-left:10px;color:#e6e6e600"></a></li>';
	
		echo $realmListHTML;
	}	
	
	echo '</ul>';	
}

/**
	Builds and outputs the sniper panel and table AKA "All Realm Deals" HTML.
	
	@param String $realm - Realm slug that this sniper table is for
	
	@return String $tableHTML - HTML code for the panel and table
*/
function buildSnipesTables($realm)
{
	global $showCommon, $showGreen, $showBlue, $showEpic, $showLeggo, $configs, $region, $locale;
	$totalBuy = 0;
	$totalValue = 0;
	$emptyTable = false;
	$snipeDeals = findDealsForRealm($realm, FALSE, $configs['maxGblSnipePercent']);
	
	// Gets the current pets owned by the account of the first character entered
	$petsAPIResponse = file_get_contents('https://'.$region.'.api.battle.net/wow/character/cenarion-circle/'.$characters[0].'?fields=pets&locale='.$locale.'&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');
	$results = json_decode($petsAPIResponse, true);	
	$cagedPetsRaw = $results['pets']['collected']; // Raw contains a bunch of extra data that we don't need
	$cagedPetsProc = [];
	
	// Create a "Processed" array of just the species id's
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
											<th class="realmTableHeader">'.getRealmNameFromSlug($realm,$region) .' Price</th>
											<th class="realmTableHeader">Market Value</th>
											<th class="realmTableHeader">% Market Value</th>
										</tr>
										<tbody id="myTable1" class="realmTableBody">';
									

	$subTableHTML = "";
	
	// For each deal we found, create a table row
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
	
	// $subTableHTML will be empty if there is no deals for this realm
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























?>