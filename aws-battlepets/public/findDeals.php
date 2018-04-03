<?php

/*
 WORKING HERE - TODO:
 - pass in the character names for each realm somehow.
 - I don't have to have duplicated code four times. Refactor into something managable with a for loop
 - Adding an echo for a load bar return would be cool!!!
*/



require_once('../scripts/util.php');


$characters = $_POST['characters'];
$realms = $_POST['realms'];
$purpose = $_POST['purpose'];

$showCommon = $_POST['showCommon'] == "true";
$showGreen = $_POST['showGreen'] == "true";
$showBlue = $_POST['showBlue'] == "true";
$showEpic = $_POST['showEpic'] == "true";
$showLeggo = $_POST['showLeggo'] == "true";

//$characters = json_decode($characters, true);

if($purpose == "buttonBar") {
	createButtonBar($realms);
}
else {

	for($i = 0; $i<sizeof($realms); $i++)
	{

		echo '<h3 id="'.$realms[$i].'">' . getRealmNameFromSlug($realms[$i]) . "</h3>";

		$goodDealsRaw = findDealsForRealm($realms[$i], FALSE);
		$goodDealsRawSpecies = findDealsForRealm($realms[$i], TRUE);
		
		for($j = 0; $j<sizeof($realms); $j++)
		{
			if($realms[$i] === $realms[$j])
				continue;
		
						
			// Find good places to sell 
			$goodSellers1 = findSellersForRealm($realms[$j], $characters[$j]);

			// Now that good sells contains only good selling pets that i do not own, we find good selling pets which are also good deals
			$goodDealsFiltered1 = array_intersect($goodDealsRawSpecies,$goodSellers1);

			if(sizeof($goodDealsFiltered1) > 0)
			{
				$tableHTML = '<table class="table table-striped table-hover">
							<tr>
								<th>Name</th>
								<th>Realm</th>
								<th>Global Market Value</th>
								<th>Min Buy</th>
								<th>% Global Market Value</th>
								<th>Realm to Sell On</th>
							</tr>
							<tbody id="myTable1">';
						
				$subTableHTML = "";
				
				foreach($goodDealsRaw as $row) {

						if(in_array($row['species_id'], $goodDealsFiltered1))
						{
							$value = $row['market_value_hist'];
							
							if($value >= 500000000 && !$showLeggo)
								continue;
							elseif($value >= 200000000 && $value < 500000000 && !$showEpic)
								continue;
							elseif($value >= 100000000 && $value < 200000000 && !$showBlue)
								continue;
							elseif($value >= 50000000 && $value < 100000000 && !$showGreen)
								continue;
							elseif($value < 50000000 && !$showCommon)
								continue;
								
							if($value > 500000000)
								$subTableHTML .= '<tr class="leggodeal">';
							elseif($value > 200000000)
								$subTableHTML .= '<tr class="epicdeal">';
							elseif($value > 100000000)
								$subTableHTML .= '<tr class="bluedeal">';
							elseif($value > 50000000)
								$subTableHTML .= '<tr class="success">';
							else
								$subTableHTML.= "<tr>";
							
							$subTableHTML .= "<td>" . $row['name'] ."</td>";
							$subTableHTML .=  "<td>" . $row['buy_realm_name'] . "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['market_value_hist']) . "</td>";
							$subTableHTML .=  "<td>" . convertToWoWCurrency($row['minbuy']) . "</td>";
							$subTableHTML .=  "<td>" . $row['percent_of_market']. "%</td>";
							$subTableHTML .=  "<td>" . getRealmNameFromSlug($realms[$j]). "</td>";
							$subTableHTML .=  "</tr>";	
						}			
				}

				$tableHTML .=  $subTableHTML."</tbody></table><br/>"; 
				
				if($subTableHTML != "")
					echo $tableHTML;
			}
		}
	}
}



/**
	Finds good selling species_id from a given realm.
*/
function findSellersForRealm($realm, $character)
{
	$conn = dbConnect();
	
	/*
	$realmRes = "(realm =  '".$realm."')";
	// TODO - make this beter
	if($realm == "cenarion-circle")
		$realmRes ="(realm =  '".$realm."' OR realm = 'sisters-of-elune')";
	*/
	
	$realmRes = buildingRealmRes($realm);
	
	$sql = "
			SELECT 
				sell_realm.species_id, realm, realms.name as sell_realm_name, min_buyout, market_value_hist
			FROM (
				SELECT 
					species_id, realm, MIN(buyout) as min_buyout
				FROM (
					SELECT 
						id, species_id,  '".$realm."' as realm, buyout, bid, owner, time_left, quantity
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
				min_buyout > (market_value_hist * 0.75);";
		
	$sellers = []; // species_id
	$result = $conn->query($sql);

	if($result) {
		while($row = $result->fetch()) {		
				array_push($sellers, $row['species_id']);		
		}
	}

	// Now we have a list of good sellers on this realm.
	// Lets remove all species that i'm already selling
	$currentlySelling = []; // Species_id
	$sql = "SELECT DISTINCT species_id FROM auctions_hourly_pet WHERE owner = '".$character."' and realm = '".$realm."'";
	$sellingResult = $conn->query($sql);

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
function findDealsForRealm($realm, $getSpecies)
{
	$conn = dbConnect();
	$realmRes = buildingRealmRes($realm);

	// Gets the pets that are a good deal on selected realm (Less than 50% global market avg)
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
			buy_realm.minbuy < (market_value_hist * 0.4) AND
			market_value_hist - buy_realm.minbuy > 10000000
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


function buildingRealmRes($realm) 
{
	$conn = dbConnect();
	$realmRes = "(realm =  '".$realm."'";
	
	$sql = "SELECT slug_child FROM realms_connected WHERE slug_parent = '". $realm . "'";
	$result = $conn->query($sql);
	
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
		$buttonBarHTML = '<a href="#';
		
		$realmName = getRealmNameFromSlug($aRealm);
		$buttonBarHTML .= $aRealm . '" class="btn btn-primary btn-bar">'.$realmName.'</a>';
			
		echo $buttonBarHTML;
		//customLog("findData", $buttonBarHTML);
	}
	
}





















?>