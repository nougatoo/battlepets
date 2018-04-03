<?php

require_once('util.php');

set_time_limit(0);
ini_set('memory_limit', '1024M');

customLog ("INFO", "Calculating Daily Market Values...");
calculateDailyMarketValues();

/**
	Calculates the market value for each seen pet on all realms.
	Connected realms are considered 1 realm.
 */
function calculateDailyMarketValues()
{
	// Connect to database
	$conn = dbConnect();
	$startMvTime = microtime(true);
	$allRealms = [];
	$realmsCompleted = [];
	$sql = "SELECT slug FROM realms";
	//$sql = "SELECT slug FROM realms WHERE slug = 'eredar' or slug = 'gorefiend' or slug = 'spinebreaker' or slug = 'wildhammer'";
	$result = $conn->query($sql);

	if ($result) {
		while($row = $result->fetch()) {
			array_push($allRealms, $row["slug"]);
		}
	} else {
		customLog ("ERROR", "0 results");
	}

	// Calculate the MV for each active pet on each realm
	foreach($allRealms as $rkey=>$currentRealm) {
		
		if(!in_array($currentRealm, $realmsCompleted)) {
						
			// Get distinct pets for this realm and it's connected realms
			$distinctPets = [];
			$connectedRealms = [];	
			$connectedRealmsSQL = "SELECT slug_child FROM realms_connected WHERE slug_parent = '".$allRealms[$rkey]."'";
			$connectedRealmsResult = $conn->query($connectedRealmsSQL);

			if ($connectedRealmsResult) {
				// output data of each row
				while($row = $connectedRealmsResult->fetch()) {
					array_push($connectedRealms, $row["slug_child"]);
				}
			} else {
				customLog ("INFO", "No Connected Realms");
			}
			
			// Need to include the current realm
			array_push($connectedRealms, $allRealms[$rkey]);
			$connectedRealmClause = implode("' OR realm = '",$connectedRealms);

			$sql = "SELECT DISTINCT species_id FROM auctions_daily_pet WHERE (realm = '".$connectedRealmClause."') AND buyout > 0;";
			//$sql = "SELECT DISTINCT species_id FROM auctions_daily_pet WHERE (realm = '".$connectedRealmClause."') AND buyout > 0 and species_id = '242'";
			$result = $conn->query($sql);
			
			if($result) {
				while($row = $result->fetch()) {
					array_push($distinctPets, $row["species_id"]);
				}
			}
			
			// For each distinct pet that was found on this realm today, calculate it's market value
			foreach($distinctPets as $currentPet)
			{
				$petBuyouts = [];
				
				// Build an array of buyouts for this pet from this realm and it's connected realms
				// For some reason it is faster to run multiple queries instead of 1 query with multiple where's for the realms
				foreach($connectedRealms as $aConnRealm) {		
					$sql = "SELECT buyout FROM auctions_daily_pet WHERE realm = '".$aConnRealm."' AND species_id = '" . $currentPet . "' AND buyout > 0;";
					//$sql = "SELECT buyout FROM auctions_daily_pet WHERE realm = 'aegwynn' OR realm = 'bonechewer' OR realm = 'daggerspine' OR realm = 'gurubashi' OR realm = 'hakkar' AND species_id = '" . $currentPet . "' ORDER BY buyout";
					$result = $conn->query($sql);
					
					if($result) {
						while($row = $result->fetch()) {
							array_push($petBuyouts, $row["buyout"]);
						}	
					}
				}
				
				// Sort the pet buyouts from least to greatest
				sort($petBuyouts);
				$conn->query('START TRANSACTION;');

				// If we can, we only want to consider the lowest 30% of buyouts
				if(sizeof($petBuyouts) >= 7) {					
					$maxElimIndex = floor(sizeof($petBuyouts)*0.30);
				}
				else {
					$maxElimIndex = sizeof($petBuyouts);
				}
				
				$actualElimIndex = sizeof($petBuyouts);
				
				// Exclude all buyouts after we find a 20% jump in price - or we hit the maxElimIndex
				foreach ($petBuyouts as $key => $value) {
					
					// If  this buyout is a 20% or more increase...throw out this buy out and anything greater
					if($key > 0 && (($petBuyouts[$key]) > ( $petBuyouts[$key-1]*1.2))) {
						$actualElimIndex = $key;
						break;
					}
					
					if($key >= $maxElimIndex) {
						$actualElimIndex = $key;
						break;
					}			
				}
				
				$petBuyouts = array_slice($petBuyouts, 0, $actualElimIndex); // Remove values after actualElimIndex
				$petBuyoutsAvg = floor(array_sum($petBuyouts)/$actualElimIndex); // Basic average
				
				// If there was more than 1 pet, find the std deviation of the remaining buy outs 
				// exclude any buyouts that are more or less than 1.5 StdDv's away
				if(sizeof($petBuyouts) > 1) {
					$petBuyoutsStdDv = standard_deviation($petBuyouts);
					$minBuyout = $petBuyoutsAvg-($petBuyoutsStdDv*1.5);
					$maxBuyout = $petBuyoutsAvg+($petBuyoutsStdDv*1.5);

					foreach ($petBuyouts as $key => $value) {				
						if($petBuyouts[$key]<$minBuyout || $petBuyouts[$key]>$maxBuyout) {
							unset($petBuyouts[$key]);
						}
					}
				}
				
				// Simple average of what's left is our final MV for the day
				$petMarketValue = floor(array_sum($petBuyouts)/count($petBuyouts)); 
							
				// TODO: Strange error where it's trying to insert multiple duplicate records
				// Insert into market_value_pets
				$mvInsertSql = "INSERT INTO market_value_pets (`species_id`, `realm`, `date`, `market_value`) VALUES ('".$currentPet."' , '".$currentRealm."' , '".date('Y-m-d H:i:s')."' , '".$petMarketValue."')";
			
				if ($conn->query($mvInsertSql) === TRUE) {
					//customLog "New record created successfully";
				} else {
					//customLog ("ERROR", $mvInsertSql);
				}
				
				$conn->query('COMMIT;');
				$conn->query('SET autocommit=1;');
			}
			
			// Add this realm and all connected realms to completed list
			foreach($connectedRealms as $aRealm) {
				array_push($realmsCompleted, $aRealm);
			}	
		}
	}

	// Clear out the daily auctions table
	$removeDailySql = "DELETE FROM auctions_daily_pet;" ;
	$conn->query($removeDailySql);
	
	// Clear out old 14-day avg
	$removeHist = "DELETE FROM market_value_pets_hist;" ;
	$conn->query($removeHist);
	
	// Clear out old 14-day avg
	$addHist = "INSERT INTO market_value_pets_hist (species_id, market_value_hist) SELECT species_id, market_value_hist FROM market_value_pets_historical;" ;
	$conn->query($addHist);
	
	$endMvTime = microtime(true);
	$timeDiffMv = $endMvTime - $startMvTime;
	customLog ("INFO", "Market Value Calculation time: " . $timeDiffMv);
}

?>