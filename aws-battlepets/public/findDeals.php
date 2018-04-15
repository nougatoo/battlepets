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

//$characters = json_decode($characters, true);

if($purpose == "buttonBar") {
	createButtonBar($realms);
}
else {

	for($i = 0; $i<sizeof($realms); $i++)
	{
		// Show first realm data
		if($i == 0)
			echo '<div id="'.$realms[$i].'_Tables">';
		else 
			echo '<div id="'.$realms[$i].'_Tables" style="display:none;">';
		
		echo '<h2 id="'.$realms[$i].'">' . getRealmNameFromSlug($realms[$i]) . " - Cross Realm Deals</h2>";
		echo '<br/>';
		
		$goodDealsRaw = findDealsForRealm($realms[$i], FALSE, $configs['maxGblBuyPercent']);
		$goodDealsRawSpecies = findDealsForRealm($realms[$i], TRUE, $configs['maxGblBuyPercent']);
		
		$tableHTML = '<table class="table table-striped table-hover">
							<tr>
								<th onclick="sortTable(this)">Name</th>
								<th>Global Market Value</th>
								<th>Min Buy</th>
								<th>% Global Market Value</th>
								<th>Realm to Sell On</th>
							</tr>
							<tbody id="myTable1">';
		// Each iteration of this loop will attempt to create a cross realms deal table
		for($j = 0; $j<sizeof($realms); $j++)
		{
			if($realms[$i] === $realms[$j])
				continue;
						
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
							$value = $row['market_value_hist'];
							
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
								$subTableHTML.= "<tr>";
							
							$subTableHTML .= "<td>" . $row['name'] ."</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist']) . "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
							$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
							$subTableHTML .=  "<td>" . getRealmNameFromSlug($realms[$j]). "</td>";
							$subTableHTML .=  "</tr>";	
							
							$totalBuy += $row['minbuy'];
							$totalValue += $value;
						}			
				}

				if($subTableHTML == "")
					$emptyTable = true;
				
				$subTableHTML .= '<tr class="totalRow">';			
				$subTableHTML .=  '<td style="border-bottom: 1px solid black;">'.'<b>Total <b/>'.'</td>';
				$subTableHTML .=  '<td style="border-bottom: 1px solid black;">'.'<b>'.convertToWoWCurrency($totalValue).'</b>'.'</td>';
				$subTableHTML .=  '<td style="border-bottom: 1px solid black;">'.'<b>'.convertToWoWCurrency($totalBuy).'</b>'.'</td>';
				$subTableHTML .=  '<td style="border-bottom: 1px solid black;">'.'</td>';
				$subTableHTML .=  '<td style="border-bottom: 1px solid black;">'.'</td>';
				$subTableHTML .=  '</tr>';
				//$subTableHTML .= '<tr style="background-color:white;"><td style="color:#ddd0;">asdf</td><td/><td/><td><td/></tr>'; // Blank Row
							
				$tableHTML .=  $subTableHTML;
			}
		}
		
		$tableHTML .= "</tbody></table><br/>"; 
				
		//if(!$emptyTable)
		echo $tableHTML;
		
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
				sell_realm.species_id, realm, realms.name as sell_realm_name, min_buyout, market_value_hist
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
			INNER JOIN market_value_pets_hist
				ON sell_realm.species_id = market_value_pets_hist.species_id
			INNER JOIN realms
				ON sell_realm.realm = realms.slug
			WHERE 
				min_buyout > (market_value_hist * ".$configs["minGblSellPercent"].");";
		
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
			floor(market_value_pets_historical.market_value_hist) as market_value_hist, 
			buy_realm.minbuy,
			round(buy_realm.minbuy/market_value_hist*100,2) as percent_of_market, 
			buy_realm.realm,
			buy_realm.buy_realm_name
		FROM 
			market_value_pets_historical
		INNER JOIN pets 
			ON pets.species_id = market_value_pets_historical.species_id
		INNER JOIN 
			(SELECT min(buyout) as minbuy, species_id, realm, realms.name as buy_realm_name FROM auctions_hourly_pet INNER JOIN realms ON realms.slug = realm WHERE buyout > 0  AND ".$realmRes. "GROUP BY species_id, realm) buy_realm
			ON pets.species_id = buy_realm.species_id
		WHERE 
			".$realmRes." AND 
			buy_realm.minbuy < (market_value_hist * ".$minMarketPercent.") AND
			market_value_hist - buy_realm.minbuy > ".$configs["minGblBuyAmount"]."
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
	TODO
*/
function createButtonBar($realms)
{
	foreach($realms as $aRealm) {
		$buttonBarHTML = '<div class="btn-group">';
		$realmName = getRealmNameFromSlug($aRealm);
		
		$buttonBarHTML .= '<button type="button" class="btn btn-primary" id="button_'.$aRealm .'" onclick="showRealmTables(this)">'.$realmName.'</button></div>';
			
		echo $buttonBarHTML;
		//customLog("findData", $buttonBarHTML);
	}
	
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
		
	// $tableHTML .= '<h2><a data-toggle="collapse" href="'.getRealmNameFromSlug($realm).'_Tables>"'.getRealmNameFromSlug($realm). " - Snipes" . "</a></h2>";
	$tableHTML .= '<h2>'.getRealmNameFromSlug($realm). " - Snipes" . "</h2>";
	$tableHTML .= '<br/>';
	
	$tableHTML .= '<table class="table table-striped table-hover">
						<tr>
							<th>Name</th>
							<th>Global Market Value</th>
							<th>Min Buy</th>
							<th>% Global Market Value</th>
						</tr>
						<tbody id="myTable1">';				
	$subTableHTML = "";
	
	foreach($snipeDeals as $row) {

		$value = $row['market_value_hist'];
		
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
		
		$subTableHTML .= "<td>" . $row['name'] ."</td>";
		$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist']) . "</td>";
		$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
		$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
		$subTableHTML .=  "</tr>";	
		
		$totalBuy += $row['minbuy'];
		$totalValue += $value;
					
	}
	
	if($subTableHTML == "")
			$emptyTable = true;
		
	$subTableHTML.= "<tr>";			
	$subTableHTML .=  "<td>"."<b>Total<b/>"."</td>";
	$subTableHTML .=  "<td>"."</td>";
	$subTableHTML .=  "<td>"."<b>".convertToWoWCurrency($totalValue)."</b>"."</td>";
	$subTableHTML .=  "<td>"."<b>".convertToWoWCurrency($totalBuy)."</b>"."</td>";
	$subTableHTML .=  "<td>"."</td>";
	$subTableHTML .=  "<td>"."</td>";
	$subTableHTML .=  "</tr>";
	
	$tableHTML .=  $subTableHTML."</tbody></table><br/>"; 
			
	if(!$emptyTable)
		return $tableHTML;
}























?>