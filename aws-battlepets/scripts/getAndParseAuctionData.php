<?php


require_once('util.php');
$configs = include('/var/app/current/application/configs/configs.php');
$region = get_cfg_var('REGION');
$locale = get_cfg_var("LOCALE");
header ('Content-type: text/html; charset=utf-8');

set_time_limit(2700); // Run for 45 minutes max
ini_set('memory_limit', '1024M');

customLog("INFO", "Calling getAndParseAuctionData ".$region);
getAndParseAuctionData($region, $locale);

/*****************
 * Begin Functions 
 ****************/
 
 
/** 
	Gets the pet auction data from blizzard. 
	Puts auction data into hourly and daily table.
	First, it makes initial calls to build an array of URLs (that contain the actual data).
	If realms are connected it won't get data for both of them.
	Once the array of URLs is built, get the actual data (3 realms at a time using multi curl)
	and insert into auctions_hourly_pet. Once all the hourly data has been inserted, insert it 
	all into the auctions_daily_pet.
	
	This function should be called once per hour (blizzard usually updates their data once per hour)
 
	@param String $region -  Usually either US or EU
	@param String $locale -  Locale should be paired with the region
 */
function getAndParseAuctionData($region, $locale)
{
	// Connect to database
	$conn = dbConnect($region);
	
	$startTimeTotal = microtime(true);
	
	// Make the inital call to get the data URLs
	$dataUrls = getDataUrls($region, $locale);
	
	$ch1 = curl_init();
	$ch2 = curl_init();
	$ch3 = curl_init();

	curl_setopt($ch1, CURLOPT_HEADER, 0);
	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch1, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, 120);
	
	curl_setopt($ch2, CURLOPT_HEADER, 0);
	curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch2, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 120);
	
	curl_setopt($ch3, CURLOPT_HEADER, 0);
	curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch3, CURLOPT_TIMEOUT, 120);
	curl_setopt($ch3, CURLOPT_CONNECTTIMEOUT, 120);	

	$curls= array($ch1,$ch2,$ch3);
	$numCurls = sizeof($curls);

	for($i = 0; $i<sizeof($dataUrls); $i=$i+$numCurls) {	
		
		$startTimeData = microtime(true);
		
		$responses = []; // String (json) array of what we get back from the data url call
		$contents = []; // a response json object as an array
		$auctions = []; // auction data for realms
		$ahRealms = []; // realms for which the auction data is for
		$slugMaps = []; // maps of a realm name to a realm slug
		
		if($dataUrls[$i]) {
			customLog("INFO","Index ".($i)." URL: ".$dataUrls[$i]);
			curl_setopt($curls[0], CURLOPT_URL, $dataUrls[$i]);
		} else {
			customLog("ERROR","Index ".($i)." Url1 Not Found");
		}
 		
		// Safeguard if the length of realms is not a multiple of 3
		if(($i+1) < sizeof($dataUrls) &&  $dataUrls[$i+1]) {
			customLog("INFO","Index ".($i+1)." URL: ".$dataUrls[$i+1]);
			curl_setopt($curls[1], CURLOPT_URL, $dataUrls[$i+1]);
		} else {
			customLog("ERROR","Index ".($i+1)." Url2 Not Found");
		}
		
		// Safeguard if the length of realms is not a multiple of 3
		if(($i+2) < sizeof($dataUrls) && $dataUrls[$i+2]) {
			customLog("INFO","Index ".($i+2)." URL: ".$dataUrls[$i+2]);
			curl_setopt($curls[2], CURLOPT_URL, $dataUrls[$i+2]);
		} else {
			customLog("ERROR","Index ".($i+2)." Url3 Not Found");
		}

		// For logging
		$startCurlTime = microtime(true);
		
		try {
			// Curl multi handler
			$mh = curl_multi_init();
			
			// Add curls to the multi handlers
			for($j = 0; $j<$numCurls; $j+=1) {
				curl_multi_add_handle($mh,$curls[$j]);
			}
			
			// Run curl calls
			$running = null;
			do {
				curl_multi_select($mh, 5);
				curl_multi_exec($mh, $running);
			} while ($running);
			
			// Get the meat from all the curls
			for($j = 0; $j<$numCurls; $j+=1) {
				array_push($responses, curl_multi_getcontent($curls[$j]));
				array_push($contents, json_decode($responses[$j], true));
				array_push($auctions, $contents[$j]['auctions']);
				array_push($ahRealms, $contents[$j]['realms']);
			}
			
		} catch (Exception $e) {
			customLog("ERROR", "Failed to get data");
		}
		
		// Log time
		$endCurlTime = microtime(true);
		$timeCurlDiff = $endCurlTime - $startCurlTime;
		customLog ("INFO", "Time to complete curls: " . $timeCurlDiff);
		
		// If it came back with data
		if($auctions && $ahRealms) {
			
			// Because the realm name given in the EU Ah pull can be wonky, just throw them under first name in the realm list
			$euSlugList = [];
			
			// Creating slug map so we don't have to query db
			foreach($ahRealms as  $key => $current) {	
				$realmSlugList= [];
				$realmNameList = [];
				
				foreach($current as $key2 => $aRealm) {		
					if($key2 == 0)
						array_push($euSlugList, $aRealm['slug']);
					
					array_push($realmSlugList, $aRealm['slug']);
					array_push($realmNameList, $aRealm['name']);

				}
				
				array_push($slugMaps, array_combine($realmNameList, $realmSlugList));
			}
			
			insertAuctionData($auctions, $slugMaps, $region, $euSlugList, $ahRealms);	
		} else {
			customLog("ERROR", "No auction data found from Url ".$i.".");
		}
	}

	// Close the handles
	curl_multi_remove_handle($mh, $ch1);
	curl_multi_remove_handle($mh, $ch2);
	curl_multi_remove_handle($mh, $ch3);
	curl_multi_close($mh);

			/*
	try {
		

		// Remove all existing auctions from the hourly table
		$removeHourlySql = "DELETE FROM auctions_hourly_pet;" ;
		$conn->query($removeHourlySql);
		
		
		// Move this from the staging table to the real hourly table
		$transferOutOfStgSql = "INSERT INTO auctions_hourly_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity) SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity FROM auctions_hourly_pet_stg ON DUPLICATE KEY UPDATE auctions_hourly_pet.bid = auctions_hourly_pet_stg.bid, auctions_hourly_pet.time_left=auctions_hourly_pet_stg.time_left;";
		$conn->query($transferOutOfStgSql);
		
		// Add all this new data into the daily table as well
		$transferToDailySql = "INSERT INTO auctions_daily_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity) SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity FROM auctions_hourly_pet ON DUPLICATE KEY UPDATE auctions_daily_pet.bid=auctions_hourly_pet.bid, auctions_daily_pet.time_left=auctions_hourly_pet.time_left;";
		$conn->query($transferToDailySql);
		
		// Clear out the staging table
		$removeHourlySql = "DELETE FROM auctions_hourly_pet_stg;" ;
		$conn->query($removeHourlySql);
	

	} catch(Throwable $e) {
		echo $e->getMessage();
	}
	
		*/
	// Log Time
	$endTimeTotal = microtime(true);
	$timeDiffTotal = $endTimeTotal - $startTimeTotal;
	customLog("INFO","Final time". ": " . $timeDiffTotal . " - ");
}

/**
	Makes the initial call to the blizzard API and builds an array of the URLs 
	that we want to get the data for. The built list will not contain duplicates of connected realms.
	They will have the same auction house data, which makes it a waste of time to get both.
	
	@param String $region - Usually either US or EU
	@param String $locale - Should be the paired locale for the region
*/
function getDataUrls($region, $locale)
{
	global $configs;
	// Connect to database
	$conn = dbConnect($region);

	$realmsToPull = []; // List of all realms 
	$realmsCompleted = []; // Used to know which connected realms we've done
	$dataUrls = []; // contains the urls where the real auction data is contained
	$sql = "SELECT slug FROM realms";
	$result = $conn->query($sql);

	if ($result) {
		while($row = $result->fetch()) {
			array_push($realmsToPull, $row["slug"]);
		}
	}

	$numRealmsToPull = sizeof($realmsToPull);
	
	for($i = 0; $i<$numRealmsToPull; $i+=1) {

		customLog("INFO", $realmsToPull[$i]);
		// Inital call to get URL for Realm
		if(!in_array($realmsToPull[$i], $realmsCompleted)) {
			
			customLog( "INFO", 'https://'.$region.'.api.battle.net/wow/auction/data/'.$realmsToPull[$i].'?locale='.$locale.'&apikey='.$configs["apiKey"]);
			
			$urlResponse = file_get_contents('https://'.$region.'.api.battle.net/wow/auction/data/'.$realmsToPull[$i].'?locale='.$locale.'&apikey='.$configs["apiKey"]);	
			$result = json_decode($urlResponse, true);	
			$url = $result['files'][0]['url'];			
			$lastModified = $result['files'][0]['lastModified'];
			$myLastUpdated = getRealmLastUpdated($realmsToPull[$i], $region);
			$doUpdate = true;
			
			if($myLastUpdated == $lastModified) {
				customLog ("INFO", "API Data has not been updated for: " . $realmsToPull[$i] . ". Last API Update: " . $lastModified . " My Last Update: " . $myLastUpdated . ".");
				$url = false;
				$doUpdate = false;
			}
			else {
				updateRealmLastUpdated($lastModified, $realmsToPull[$i], $region); // TODO - THIS IS NOT THE BEST PLACE TO DO THIS. WE'RE JUST TRYING HERE...NOTHING HAS BEEN UPDATED
			}

			if($url) {
				array_push($dataUrls, $url);
			} 
			else if ($doUpdate){
				customLog ("ERROR", "Could not get URL for: ".$realmsToPull[$i]);
			}
					
			// Add all connected realms to the completed realms list so that we don't pull data that we already have		
			$connectedRealmsSQL = "SELECT slug_child FROM realms_connected WHERE slug_parent = '".$realmsToPull[$i]."'";
			$connectedRealmsResult = $conn->query($connectedRealmsSQL);

			if ($connectedRealmsResult) {
				while($row = $connectedRealmsResult->fetch()) {
					array_push($realmsCompleted, $row["slug_child"]);
					updateRealmLastUpdated($lastModified, $row["slug_child"], $region); // TODO - THIS IS NOT THE BEST PLACE TO DO THIS. WE'RE JUST TRYING HERE...NOTHING HAS BEEN UPDATED
				}
			} 
			else {
				// Do nothing - has no connected realms
			}
			
			// Add current realm to the completed list
			array_push($realmsCompleted, $realmsToPull[$i]);
			unset($urlResponse);
		}		
	}
	// End of getting URLs for each realm
	
	customLog ("INFO", "Size OF dataUrls: ".sizeof($dataUrls));
	customLog ("INFO", "Size OF realmsCompleted: ".sizeof($realmsCompleted));
	
	return $dataUrls;
}

/**
	Inserts the passed auction data into the auctions_hourly_table_stg.
	Using a staging table so that we don't disrupt the use of the application
	while our getAndParseAuctionData is running.
	
	@param Array $auctions - Array of all the auctions that are going to be inserted for a realm
	@param Array $slugMaps - An array of slug maps that associates a slug with a realm name so we don't have to query the db
	@param String $region - Usually either US or EU
	@param String $euSlugList - First slug in the auction json data retrived. Since EU uses realm names that can't be parsed or matched in db. 
	
*/
function insertAuctionData($auctions, $slugMaps, $region, $euSlugList, $ahRealms)
{
	// Connect to database
	$conn = dbConnect($region);

	// Log Time
	$startTimeAuctions = microtime(true);

	// Insert auctions for a realm in one transaction per realm
	foreach($auctions as  $key => $currentRealmAh) {		
	
		$conn->beginTransaction();
		
		// TODO - this could be the place to build a restriction for ahRealms[$key]. Then I could technically do all the moving queries for eahc group of realms instead of all at once in the end.
		$realmRes = buildRealmRes($region, $ahRealms[$key]); // Build the restriction off of the first realm in the list...they're all connected
		
		// This could be optimized by adding more than one "values" but for now the speed is less than one second fpr 3 realms.
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
				
				// Avoid indexing a slug map with a crappy russian name from the AH data...
				// TODO: All connected realm data is going to be housed under 1 realm in EU for now
				if($region == "EU")
				{
					$sql = "INSERT INTO auctions_hourly_pet_stg (`id`, `species_id`, `realm`, `buyout`, `bid`, `owner`, `time_left`,`quantity`) VALUES ('" . $id . "', '" . $speciesId . "', '" . $euSlugList[$key] . "', '" . $buyout . "', '" . $bid . "', '" . $owner . "', '" . $timeLeft . "', '" . $quantity . "')";
				}
				else
				{
					$sql = "INSERT INTO auctions_hourly_pet_stg (`id`, `species_id`, `realm`, `buyout`, `bid`, `owner`, `time_left`,`quantity`) VALUES ('" . $id . "', '" . $speciesId . "', '" . $slugMaps[$key][$realmName] . "', '" . $buyout . "', '" . $bid . "', '" . $owner . "', '" . $timeLeft . "', '" . $quantity . "')";
				}


				if ($conn->query($sql) === TRUE) {
					//customLog "New record created successfully";
				}
				
			}
		}
		$conn->commit();
		
		// NEW CODE
		
		// Remove all existing auctions from the hourly table
		$removeHourlySql = "DELETE FROM auctions_hourly_pet ".$realmRes.";";
		$conn->query($removeHourlySql);	
		
		// Move this from the staging table to the real hourly table
		$transferOutOfStgSql = "INSERT INTO auctions_hourly_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity) SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity FROM auctions_hourly_pet_stg " . $realmRes . " ON DUPLICATE KEY UPDATE auctions_hourly_pet.bid = auctions_hourly_pet_stg.bid, auctions_hourly_pet.time_left=auctions_hourly_pet_stg.time_left;";
		$conn->query($transferOutOfStgSql);
		
		// Add all this new data into the daily table as well
		$transferToDailySql = "INSERT INTO auctions_daily_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity) SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity FROM auctions_hourly_pet " . $realmRes . " ON DUPLICATE KEY UPDATE auctions_daily_pet.bid=auctions_hourly_pet.bid, auctions_daily_pet.time_left=auctions_hourly_pet.time_left;";
		$conn->query($transferToDailySql);
		
		// Clear out the staging table
		$removeHourlySql = "DELETE FROM auctions_hourly_pet_stg " . $realmRes . " ;" ;
		$conn->query($removeHourlySql);
	}

	// Log Time
	$endTimeAuctions = microtime(true);
	$timeDiffAuctions = $endTimeAuctions - $startTimeAuctions;
	
	customLog ("INFO", "Time to complete auctions insert: " . $timeDiffAuctions);
	customLog ("INFO", "----------------------------------------------");
}

/**
	Updates the realm's last_updated value 
	
	@param {int} $lastModified - Value given by blizzard as the last modified time as an int
	@param {string} $slug - Slug name of the realm
	@param {string} $region - Usually either US or EU

*/
function updateRealmLastUpdated($lastModified, $slug, $region)
{
	// Connect to database
	$conn = dbConnect($region);
	
	$result = $conn->prepare("UPDATE realms SET last_updated = ? WHERE slug = ?");
	$result->bindParam(1, $lastModified);
	$result->bindParam(2, $slug);
	$result->execute();
}

/**
	Builds a SQL restriction for all the realms passed in 
	
	@param {string} $region - Usually either US or EU
	@param {array} $realms - Array of string for the realms to create the restriction for
	
	@return String $realmRes - SQL restriction for passed in realm and it's connected realm
*/
function buildRealmRes($region, $realms) 
{
	// Connect to database
	$conn = dbConnect($region);
	
	$realmRes = "WHERE  (realm =  '" . $realms[0]['slug'] . "'";
	
	for($i = 1; $i<sizeof($realms); $i++) {
		$realmRes .= " OR realm = '" . $realms[$i]['slug'] . "'";
	}
	
	$realmRes .= ")";
	return $realmRes;
}




?>
















