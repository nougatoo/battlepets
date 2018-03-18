<?php

require_once('util.php');

set_time_limit(0);
ini_set('memory_limit', '1024M');

customLog("auctionData","Calling getAndParseAuctionData...");
getAndParseAuctionData();
customLog("auctionData","Finished calling getAndParseAuctionData...");

/** 
 *		Gets the pet auction data from blizzard. 
 *		Puts auction data into hourly and daily table.
 *		First, it makes initial calls to build an array of URLs (that contain the actual data).
 *		If realms are connected it won't get data for both of them.
 *		Once the array of URLs is built, get the actual data (3 realms at a time using multi curl)
 *		and insert into auctions_hourly_pet. Once all the hourly data has been inserted, insert it 
 * 	all into the auctions_daily_pet.
 *		
 *		This function should be called once per hour (blizzard usually updates their data once per hour)
 * 	
 *		TODO: add error handling
 */
function getAndParseAuctionData()
{
	// Connect to database
	$conn = dbConnect();
	
	// Remove all existing auctions from the hourly table
	$removeHourlySql = "DELETE FROM auctions_hourly_pet;" ;
	$conn->query($removeHourlySql);

	$startTimeTotal = microtime(true);
	
	// Make the inital call to get the data URLs
	$dataUrls = getDataUrls();
	
	// Getting Auction Data and inserting 
	$ch1 = curl_init();
	$ch2 = curl_init();
	$ch3 = curl_init();

	curl_setopt($ch1, CURLOPT_HEADER, 0);
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch2, CURLOPT_HEADER, 0);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch3, CURLOPT_HEADER, 0);
	curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);

	$curls= array($ch1,$ch2,$ch3);
	$numCurls = sizeof($curls);

	for($i = 0; $i<sizeof($dataUrls); $i=$i+$numCurls) {	
		
		$startTimeData = microtime(true);
		
		$responses = []; // String (json) array of what we get back from the data url call
		$contents = []; // a response json object as an array
		$auctions = []; // auction data for realms
		$ahRealms = []; // realms for which the auction data is for
		$slugMaps = []; // maps of a realm name to a realm slug
		
		curl_setopt($curls[0], CURLOPT_URL, $dataUrls[$i]);
		customLog("auctionData","Url1: ".$dataUrls[$i]);
		
		// Safeguard if the length of realms is not a multiple of 3
		if(($i+1) < sizeof($dataUrls)) {
			curl_setopt($curls[1], CURLOPT_URL, $dataUrls[$i+1]);
			customLog("auctionData","Url2: ".$dataUrls[$i+1]);
		}
		
		// Safeguard if the length of realms is not a multiple of 3
		if(($i+2) < sizeof($dataUrls)) {
			curl_setopt($curls[2], CURLOPT_URL, $dataUrls[$i+2]);
			customLog("auctionData","Url3: ".$dataUrls[$i+2]);
		}

		// Curl multi handler
		$mh = curl_multi_init();
		
		// Add curls to the multi handlers
		for($j = 0; $j<$numCurls; $j+=1) {
			curl_multi_add_handle($mh,$curls[$j]);
		}
		
		// Run curl calls
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while ($running);
		
		// Get the meat from all the curls
		for($j = 0; $j<$numCurls; $j+=1) {
			array_push($responses, curl_multi_getcontent($curls[$j]));
			array_push($contents, json_decode($responses[$j], true));
			array_push($auctions, $contents[$j]['auctions']);
			array_push($ahRealms, $contents[$j]['realms']);
		}
		
		// Creating slug map so we don't have to query db
		foreach($ahRealms as  $key => $current) {	
			$realmSlugList= [];
			$realmNameList = [];
			
			foreach($current as $aRealm) {		
				array_push($realmSlugList, $aRealm['slug']);
				array_push($realmNameList, $aRealm['name']);			
			}
			
			array_push($slugMaps, array_combine($realmNameList, $realmSlugList));
		}
		
		insertAuctionData($auctions, $slugMaps);	
	}

	// Close the handles
	curl_multi_remove_handle($mh, $ch1);
	curl_multi_remove_handle($mh, $ch2);
	curl_multi_remove_handle($mh, $ch3);
	curl_multi_close($mh);

	// Add all this new data into the daily table as well
	$transferToDailySql = "INSERT INTO auctions_daily_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity)
									SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity
									FROM auctions_hourly_pet
									ON DUPLICATE KEY UPDATE auctions_daily_pet.bid=auctions_hourly_pet.bid, auctions_daily_pet.time_left=auctions_hourly_pet.time_left;";
	$conn->query($transferToDailySql);

	$endTimeTotal = microtime(true);
	$timeDiffTotal = $endTimeTotal - $startTimeTotal;
	customLog("auctionData","Final time". ": " . $timeDiffTotal . " - ");
}

/**
	Makes the initial call to the blizzard API and builds an array of the URLs 
	that we want to get the data for.
	
	Returns and array of strings
*/
function getDataUrls()
{
	// Connect to database
	$conn = dbConnect();

	$realmsToPull = []; // List of all realms 
	$realmsCompleted = []; // Used to know which connected realms we've done
	$dataUrls = []; // contains the urls where the real auction data is contained
	$sql = "SELECT slug FROM realms";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			array_push($realmsToPull, $row["slug"]);
		}
	} else {
		echo "0 results";
	}

	$numRealmsToPull = sizeof($realmsToPull);
	
	for($i = 0; $i<$numRealmsToPull; $i+=1) {

		customLog("auctionData","Working on -- ".$realmsToPull[$i]);
		
		// Inital call to get URL for Realm
		if(!in_array($realmsToPull[$i], $realmsCompleted)) {
			$urlResponse = file_get_contents('https://us.api.battle.net/wow/auction/data/'.$realmsToPull[$i].'?locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');	
			$result = json_decode($urlResponse, true);	
			$url = $result['files'][0]['url'];			
			array_push($dataUrls, $url);
					
			// Add all connected realms to the completed realms list so that we don't pull data that we already have		
			$connectedRealmsSQL = "SELECT slug_child FROM realms_connected WHERE slug_parent = '".$realmsToPull[$i]."'";
			$connectedRealmsResult = $conn->query($connectedRealmsSQL);

			if ($connectedRealmsResult->num_rows > 0) {
				// output data of each row
				while($row = $connectedRealmsResult->fetch_assoc()) {
					array_push($realmsCompleted, $row["slug_child"]);
				}
			} else {
				echo "0 results";
			}
			
			// Add current realm to the completed list
			array_push($realmsCompleted, $realmsToPull[$i]);
			unset($urlResponse);
		}		
	}
	// End of getting URLs for each realm
	customLog("auctionData","SIZE OF dataUrls: ".sizeof($dataUrls));
	customLog("auctionData","SIZE OF realmsCompleted: ".sizeof($realmsCompleted));
	
	return $dataUrls;
}

/**
	Inserts the passed auction data into the auctions_hourly_table
	
*/
function insertAuctionData($auctions, $slugMaps)
{
	// Connect to database
	$conn = dbConnect();
	
	$startTimeAuctions = microtime(true);
		
	// Insert auctions for a realm in one transaction per realm
	foreach($auctions as  $key => $currentRealmAh) {		
	
		$conn->query('START TRANSACTION;');
		
		// This could be optimized by adding more than one "values" but for now the speed is less
		// than one second fpr 3 realms.
		foreach ($currentRealmAh as $key2 => $currentAuction) {
		  
			$id = $currentAuction['auc'];
			$realmName = $currentAuction['ownerRealm']; 
			$buyout = $currentAuction['buyout'];
			$bid = $currentAuction['bid'];
			$isPet = isset($currentAuction['petSpeciesId']);
			$owner = $currentAuction['owner'];
			$timeLeft = $currentAuction['timeLeft'];
			$quantity = $currentAuction['quantity'];
			
			// Only inserting pets
			if($isPet)
			{
				$speciesId = $currentAuction['petSpeciesId'];
				
				$sql = "INSERT INTO auctions_hourly_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity)
				VALUES ('" . $id . "', '" . $speciesId . "', '" . $slugMaps[$key][$realmName] . "', '" . $buyout . "', '" . $bid . "', '" . $owner . "', '" . $timeLeft . "', '" . $quantity . "')"
						. "ON DUPLICATE KEY UPDATE "
						. "bid='" . $bid  . "', time_left='" . $timeLeft  . "'";

				if ($conn->query($sql) === TRUE) {
					//echo "New record created successfully";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}
			}
		}
		
		$conn->query('COMMIT;');
		$conn->query('SET autocommit=1;');
	}
	
	$endTimeAuctions = microtime(true);
	$timeDiffAuctions = $endTimeAuctions - $startTimeAuctions;
	customLog("auctionData","Time to complete auctions insert: " . $timeDiffAuctions);
	customLog("auctionData","----------------------------------------------");
}
?>