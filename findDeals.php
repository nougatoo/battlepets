<?php

/**
	Finds good selling species_id from a given realm.
*/
function findSellersForRealm($realm)
{
	$conn = dbConnect();
	
	$realmRes = "(realm =  '".$realm."')";
	// TODO - make this beter
	if($realm == "cenarion-circle")
		$realmRes ="(realm =  '".$realm."' OR realm = 'sisters-of-elune')";
	
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
			INNER JOIN market_value_pets_historical
				ON sell_realm.species_id = market_value_pets_historical.species_id
			INNER JOIN realms
				ON sell_realm.realm = realms.slug
			WHERE 
				min_buyout > (market_value_hist * 0.75);";
		
	$sellers = []; // species_id
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {		
				array_push($sellers, $row['species_id']);		
		}
	} else {
		echo "0 results";
	}
	
	$myRealmName = "";
	
	if ($realm == "proudmoore")
		$myRealmName = "Lladox";
	elseif ($realm == "cenarion-circle")
		$myRealmName = 'Irone';
	elseif ($realm == "wyrmrest-accord")
		$myRealmName = 'Valamyr';
	elseif ($realm == "emerald-dream")
		$myRealmName = 'Åurd';
		
	// Now we have a list of good sellers on this realm.
	// Lets remove all species that i'm already selling
	$currentlySelling = []; // Species_id
	$sql = "SELECT DISTINCT species_id FROM auctions_hourly_pet WHERE owner = '".$myRealmName."' and realm = '".$realm."'";
	$sellingResult = $conn->query($sql);

	if ($sellingResult->num_rows > 0) {
		// output data of each row
		while($row = $sellingResult->fetch_assoc()) {		
				array_push($currentlySelling, $row['species_id']);		
		}
	} else {
		echo "0 results";
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

	$realmRes = "(realm =  '".$realm."')";
	
	// TODO - make this beter
	if($realm == "cenarion-circle")
		$realmRes ="(realm =  '".$realm."' OR realm = 'sisters-of-elune')";
	
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
			(SELECT min(buyout) as minbuy, species_id, realm, realms.slug as buy_realm_name FROM auctions_hourly_pet INNER JOIN realms ON realms.slug = realm WHERE buyout > 0 GROUP BY species_id, realm) buy_realm
			ON pets.species_id = buy_realm.species_id
		WHERE 
			".$realmRes." AND 
			buy_realm.minbuy < (market_value_hist * 0.4) AND
			market_value_hist - buy_realm.minbuy > 10000000
		ORDER BY percent_of_market;";

	$goodDealsRawSpecies = [];
	$goodDealsRaw = []; 
	$result = $conn->query($goodDealsRawSql);

	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {	
		
				array_push($goodDealsRawSpecies, $row['species_id']);
				array_push($goodDealsRaw, $row);				
		}
	} else {
		echo "0 results";
	}
		
	if($getSpecies)
		return $goodDealsRawSpecies;
	else
		return $goodDealsRaw;
}


?>