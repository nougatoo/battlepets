<?php
set_time_limit(0);
ini_set('memory_limit', '1024M');

//$conn = new mysqli('localhost',$config['username'],$config['password'],$config['dbname']);

$conn = dbConnect();


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

echo ("<br/>");

//getPetData();
//getRealmData();
getAndParseAuctionData();

/** 
 *		Gets JSON pet data from blizzards master pet API.
 *		This does not need to be run often as the pet list
 *		should only change around patches.
 */
function getPetData()
{
	// Connect to database
	$conn = dbConnect();

	$content = file_get_contents("https://us.api.battle.net/wow/pet/?locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud");
	$result  = json_decode($content, true);
	$pets = $result['pets'];

	foreach ($pets as $currentPet) {
		
		$speciesId = $currentPet['stats']['speciesId'];
		$name = $currentPet['name'];
		$qualityId= $currentPet['stats']['petQualityId'];
		$creatureId = $currentPet['creatureId'];
		$icon = $currentPet['icon'];
		
		$sql = "INSERT INTO pets (species_id, name, quality_id, creature_id, icon)
		VALUES ('" . $speciesId . "', '" . str_replace('\'', '\'\'' ,$name) . "', '" . $qualityId . "', '" . $creatureId . "', '" . $icon . "'" . " )"
				. "ON DUPLICATE KEY UPDATE "
				. "creature_id='" . $creatureId . "', name='" . str_replace('\'', '\'\'' ,$name) . "', icon='" . $icon . "', quality_id='" . $qualityId . "'";

		if ($conn->query($sql) === TRUE) {
			//echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
}


/** 
 *		Gets JSON realm data from blizzards realm list API.
 *		This does not need to be run often as the realm list
 *		should only change around patches.
 */
function getRealmData()
{
	// Connect to database
	$conn = dbConnect();
	
	$content = file_get_contents("https://us.api.battle.net/wow/realm/status?locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud");
	$result  = json_decode($content, true);
	$realms = $result['realms'];

	foreach ($realms as $currentRealm) {
		
		$slug = $currentRealm['slug'];
		$name = $currentRealm['name'];
		$locale= $currentRealm['locale']; 
		$connectedRealms = $currentRealm['connected_realms']; 

		$sql = "INSERT INTO realms (slug, name, locale)
		VALUES ('" . $slug . "', '" . str_replace('\'', '\'\'' ,$name) . "', '" . $locale . "')"
				. "ON DUPLICATE KEY UPDATE "
				. "locale='" . $locale . "', name='" . str_replace('\'', '\'\'' ,$name) . "'";

		if ($conn->query($sql) === TRUE) {
			//echo "New record created successfully";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}	
		
		// Parsing Connected Realms Data
		foreach ($connectedRealms as $childSlug){
			
			// If the connected realm is not this realm...store it
			if(strcmp($childSlug,$slug))
			{
				$sql = "INSERT INTO realms_connected (slug_parent, slug_child)
						VALUES ('" . $slug . "', '" . $childSlug . "')"
						. "ON DUPLICATE KEY UPDATE "
						. "slug_parent='" . $slug . "', slug_child='" . $childSlug . "'";

				if ($conn->query($sql) === TRUE) {
					//echo "New realm created successfully<br/>";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}	
			}			
		}
	}
}

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
	$responses = [];
	$contents = [];
	$auctions = [];
	$ahRealms = [];
	$slugMaps = [];
	file_put_contents('php://stderr', "\n".print_r("SIZEOF CURLS: ".$numCurls , TRUE));

	for($i = 0; $i<sizeof($dataUrls); $i=$i+3) {	
		
		$startTimeData = microtime(true);
		
		$responses = [];
		$contents = [];
		$auctions = [];
		$ahRealms = [];
		$slugMaps = [];
		
		curl_setopt($curls[0], CURLOPT_URL, $dataUrls[$i]);
		file_put_contents('php://stderr', "\n".print_r("Url1: ".$dataUrls[$i] , TRUE));
		
		if(($i+1) < sizeof($dataUrls)) {
			curl_setopt($curls[1], CURLOPT_URL, $dataUrls[$i+1]);
			file_put_contents('php://stderr', "\n".print_r("Url2: ".$dataUrls[$i+1] , TRUE));
		}
		
		if(($i+2) < sizeof($dataUrls)) {
			curl_setopt($curls[2], CURLOPT_URL, $dataUrls[$i+2]);
			file_put_contents('php://stderr', "\n".print_r("Url3: ".$dataUrls[$i+2] , TRUE));
		}

		$mh = curl_multi_init();
		
		for($j = 0; $j<$numCurls; $j+=1) {
			curl_multi_add_handle($mh,$curls[$j]);
		}
		
		// Run curl calls
		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while ($running);
		
		// Get the responses from all curls
		for($j = 0; $j<$numCurls; $j+=1) {
			array_push($responses, curl_multi_getcontent($curls[$j]));
		}
		
		// Get the contents from each response as json
		for($j = 0; $j<$numCurls; $j+=1) {
			array_push($contents, json_decode($responses[$j], true));
		}
		
		// Get the auctions data
		for($j = 0; $j<$numCurls; $j+=1) {
			array_push($auctions, $contents[$j]['auctions']);
		}
		
		// Get the realms array from contents
		for($j = 0; $j<$numCurls; $j+=1) {
			array_push($ahRealms, $contents[$j]['realms']);
		}
		
		
		$endTimeData = microtime(true);
		$timeDiffData = $endTimeData - $startTimeData;
		file_put_contents('php://stderr', "\n".print_r("Time to complete Raw Data: ".$timeDiffData , TRUE));
		
		
		// Creating slug map so we don't have to query db
		$startTimeAuctions = microtime(true);
		
		foreach($ahRealms as  $key => $current) {	
			$realmSlugList= [];
			$realmNameList = [];
			
			foreach($current as $aRealm) {		
				array_push($realmSlugList, $aRealm['slug']);
				array_push($realmNameList, $aRealm['name']);			
			}
			
			array_push($slugMaps, array_combine($realmNameList, $realmSlugList));
			
			unset($realmSlugList);
			unset($realmNameList);
		}
			
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
		file_put_contents('php://stderr', print_r(" Time to complete auctions insert: " . $timeDiffAuctions . "\n" , TRUE));
		file_put_contents('php://stderr', "\n".print_r("----------------------------------------------" , TRUE));
		
		// Not sure if i need these
		unset($auctions1);
		unset($auctions2);
		unset($auctions3);
		
		unset($content1);
		unset($content2);
		unset($content3);
		
		unset($response1);
		unset($response2);
		unset($response3);	
		
	}

	//close the handles

	curl_multi_remove_handle($mh, $ch1);
	curl_multi_remove_handle($mh, $ch2);
	curl_multi_remove_handle($mh, $ch3);
	curl_multi_close($mh);


	$transferToDailySql = "INSERT INTO auctions_daily_pet (id, species_id, realm, buyout, bid, owner, time_left, quantity)
	SELECT id, species_id, realm, buyout, bid, owner, time_left, quantity
	FROM auctions_hourly_pet
	ON DUPLICATE KEY UPDATE auctions_daily_pet.bid=auctions_hourly_pet.bid, auctions_daily_pet.time_left=auctions_hourly_pet.time_left;";
	$conn->query($transferToDailySql);

	$endTimeTotal = microtime(true);
	$timeDiffTotal = $endTimeTotal - $startTimeTotal;
	file_put_contents('php://stderr', "\n".print_r("Final time". ": " . $timeDiffTotal . " - " , TRUE));
	
}

function getDataUrls()
{
	// Connect to database
	$conn = dbConnect();
	
	// Getting URLs for each realm
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

	$startTimeTotal = microtime(true);
	$numReamlsToPull = sizeof($realmsToPull);
	for($i = 0; $i<$numReamlsToPull; $i+=1) {

		file_put_contents('php://stderr', "\nWorking on -- ".$realmsToPull[$i], TRUE);
		
		// Inital call to get URL for Realm
		if(!in_array($realmsToPull[$i], $realmsCompleted)) {
			$urlResponse1 = file_get_contents('https://us.api.battle.net/wow/auction/data/'.$realmsToPull[$i].'?locale=en_US&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud');	
			$result1 = json_decode($urlResponse1, true);	
			$url1 = $result1['files'][0]['url'];			
			array_push($dataUrls, $url1);
					
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
			unset($urlResponse1);
		}		
	}
	// End of getting URLs for each realm
	file_put_contents('php://stderr', "\n SIZE OF dataUrls: ".sizeof($dataUrls) , TRUE);
	file_put_contents('php://stderr', "\n SIZE OF realmsCompleted: ".sizeof($realmsCompleted) , TRUE);
	
	return $dataUrls;
}



/* Getting market value */
function calculateMarketValues()
{
	// Connect to database
	$conn = dbConnect();
	echo("Calculating Markget Value....");

	$startMvTime = microtime(true);

	$allRealms = [];
	$realmsCompleted = [];
	$sql = "SELECT slug FROM realms";
	//$sql = "SELECT slug FROM realms WHERE slug = 'eredar' or slug = 'gorefiend' or slug = 'spinebreaker' or slug = 'wildhammer'";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			array_push($allRealms, $row["slug"]);
		}
	} else {
		echo "0 results";
	}

	$debugTotalPQTime = 0;
	foreach($allRealms as $rkey=>$currentRealm)
	{

		if(!in_array($currentRealm, $realmsCompleted))
		{
			
			echo("Realm: ".$allRealms[$rkey]." ");
			
			$startTime = microtime(true);
			
			// Get distinct pets for this realm and it's connected realms
			$distinctPets = [];
			$connectedRealms = [];	
			$connectedRealmsSQL = "SELECT slug_child FROM realms_connected WHERE slug_parent = '".$allRealms[$rkey]."'";
			$connectedRealmsResult = $conn->query($connectedRealmsSQL);

			if ($connectedRealmsResult->num_rows > 0) {
				// output data of each row
				while($row = $connectedRealmsResult->fetch_assoc()) {
					array_push($connectedRealms, $row["slug_child"]);
				}
			} else {
				echo "<br/>No Connected Realms";
			}
			
			// Need to include the current realm
			array_push($connectedRealms, $allRealms[$rkey]);
			$connectedRealmClause = implode("' OR realm = '",$connectedRealms);
			
			
			$endTime = microtime(true);
			$timeDiff = $endTime - $startTime;
			//file_put_contents('php://stderr', print_r("Create Connected realms string: " . $timeDiff . "\n" , TRUE));
		
			
			$startTime = microtime(true);
			
			$sql = "SELECT DISTINCT species_id FROM auctions_daily_pet WHERE (realm = '".$connectedRealmClause."') AND buyout > 0 ";
			//$sql = "SELECT DISTINCT species_id FROM auctions_daily_pet WHERE (realm = '".$connectedRealmClause."') AND buyout > 0 and species_id = '242'";
			echo ($sql."<br/>");
			//$sql = "SELECT DISTINCT species_id FROM auctions_daily_pet WHERE realm = 'aegwynn' OR realm = 'bonechewer' OR realm = 'daggerspine' OR realm = 'gurubashi' OR realm = 'hakkar' ";
			$result = $conn->query($sql);
			while($row = $result->fetch_assoc()) {
				array_push($distinctPets, $row["species_id"]);
			}
			
			$endTime = microtime(true);
			$timeDiff = $endTime - $startTime;
			//file_put_contents('php://stderr', print_r("Select Distinct pets from realms: " . $timeDiff . "\n" , TRUE));

			

			file_put_contents('php://stderr', print_r("size of distinct pet list: " . sizeof($distinctPets) . "\n" , TRUE));
			
			foreach($distinctPets as $currentPet)
			{
				$startTime = microtime(true);
				$petBuyouts = [];
				
				// For some reason it is faster to run multiple queries instead of 1 query with multiple where's for the realms
				foreach($connectedRealms as $aConnRealm) {
					
					$sql = "SELECT buyout FROM auctions_daily_pet WHERE realm = '".$aConnRealm."' AND species_id = '" . $currentPet . "' AND buyout > 0";
					//echo("<br/>".$sql);
					//$sql = "SELECT buyout FROM auctions_daily_pet WHERE realm = 'aegwynn' OR realm = 'bonechewer' OR realm = 'daggerspine' OR realm = 'gurubashi' OR realm = 'hakkar' AND species_id = '" . $currentPet . "' ORDER BY buyout";
					$result = $conn->query($sql);
					
					while($row = $result->fetch_assoc()) {
						array_push($petBuyouts, $row["buyout"]);
					}	
				}
				
				sort($petBuyouts);
				
				$conn->query('START TRANSACTION;');
				
				$endTime = microtime(true);
				$timeDiff = $endTime - $startTime;
				//file_put_contents('php://stderr', print_r("Query from acutions_daily_pet for a single pet: " . $timeDiff . "\n" , TRUE));
				$debugTotalPQTime += $timeDiff;
				echo ("Current pet: ".$currentPet."<br/>");
				echo ("Number of pets: ". sizeof($petBuyouts)."<br/>");
				
				$startTime = microtime(true);
				// WORKING HERE - IF WE DON'T HAVE ENOUGH ARRAY VALUES, INDEXES GO TO 0. 
				if(sizeof($petBuyouts) >= 7) {
					
					// Assuming that the array is in order from smallest to largest
					$minElimIndex = floor(sizeof($petBuyouts)*0.15);
					$maxElimIndex = floor(sizeof($petBuyouts)*0.30);

				}
				else {
					$minElimIndex = 0;
					$maxElimIndex = sizeof($petBuyouts);
				}
				
				$actualElimIndex = sizeof($petBuyouts);
				echo ("<br/> Eliminex Max".$maxElimIndex);
				//print_r($petBuyouts);
				echo ("<br/>--</br>");
				foreach ($petBuyouts as $key => $value) {
					
					echo ("<br/>key ".$key);
					echo ("<br/>value ".$value);
					// If we are through 15% of the buyouts and this buyout is a 20% or more increase...throw out the rest
					//if($key > $minElimIndex && ($petBuyouts[$key]) >( $petBuyouts[$key-1]*1.2)) {
					if($key > 0 && (($petBuyouts[$key]) > ( $petBuyouts[$key-1]*1.2))) {
						$actualElimIndex = $key;
						break;
					}
					
					if($key >= $maxElimIndex) {
						$actualElimIndex = $key;
						break;
					}
						
				}
				echo ("<br/> Actual ELIM".$actualElimIndex."<br/>");
				
				$petBuyouts = array_slice($petBuyouts, 0, $actualElimIndex);

				print_r($petBuyouts);

				
				$petBuyoutsAvg = floor(array_sum($petBuyouts)/$actualElimIndex);
				//echo ($petBuyoutsAvg."-");

				if(sizeof($petBuyouts) > 1) {
					$petBuyoutsStdDv = standard_deviation($petBuyouts);
					//echo ($petBuyoutsStdDv."-");
				

					$minBuyout = $petBuyoutsAvg-($petBuyoutsStdDv*1.5);
					$maxBuyout = $petBuyoutsAvg+($petBuyoutsStdDv*1.5);
					//echo ($minBuyout."-");
					//echo ($maxBuyout."-");

					foreach ($petBuyouts as $key => $value) {
						
						if($petBuyouts[$key]<$minBuyout || $petBuyouts[$key]>$maxBuyout) {
							unset($petBuyouts[$key]);
						}
					}
				}
				
				$petMarketValue = floor(array_sum($petBuyouts)/count($petBuyouts));
				
				$endTime = microtime(true);
				$timeDiff = $endTime - $startTime;
				//file_put_contents('php://stderr', print_r("Doing market value math " . $timeDiff . "\n" , TRUE));
				echo ("<br/>MV: ".$petMarketValue."<br/>");
				
				// Insert the MV for today into market_value_pets
				$mvInsertSql = "INSERT INTO market_value_pets (species_id, realm, date, market_value)
														VALUES ('".$currentPet."' , '".$currentRealm."' , '".date('Y-m-d')."' , '".$petMarketValue."')";
			
				if ($conn->query($mvInsertSql) === TRUE) {
				//echo "New record created successfully";
				} else {
					echo "Error: " . $sql . "<br>" . $conn->error;
				}
				
				$conn->query('COMMIT;');
				$conn->query('SET autocommit=1;');
			}
			

			
			//array_push($realmsCompleted, $allRealms[$rkey]);
			
			foreach($connectedRealms as $aRealm) {
				array_push($realmsCompleted, $aRealm);
			}	
		}
	}



	echo(sizeof($realmsCompleted));

	$endMvTime = microtime(true);
	$timeDiffMv = $endMvTime - $startMvTime;
	echo ("Market Value Calculation time: " . $timeDiffMv);
	echo ("<br/> debugTotalPQTime: " . $debugTotalPQTime);

}

function standard_deviation($sample){
	if(is_array($sample)){
		$mean = array_sum($sample) / count($sample);
		foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
		return sqrt(array_sum($devs) / (count($devs) - 1));
	}
}

function loadFile($url) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function dbConnect() {

    // Define connection as a static variable, to avoid connecting more than once 
    static $connection;

    // Try and connect to the database, if a connection has not been established yet
    if(!isset($connection)) {
         // Load configuration as an array. Use the actual location of your configuration file
		$config = parse_ini_file('C:/Apache24/conf/config.ini'); 
        $connection =  new mysqli('localhost',$config['username'],$config['password'],$config['dbname']);
    }

    // If connection was not successful, handle the error
    if($connection === false) {
        // Handle error - notify administrator, log to a file, show an error screen, etc.
        return mysqli_connect_error(); 
    }
    return $connection;
}