<?php

require_once('../scripts/util.php');
header ('Content-type: text/html; charset=utf-8');

set_time_limit(0);

//customLog("getStaticBlizzData","Getting pet data...");
getPetData("US", "en_US");
getPetData("EU", "en_GB");
//customLog("getStaticBlizzData","Done getting pet data...");

//customLog("getStaticBlizzData","Geting realm data...");
// Gets both EU and US realm data
getRealmData();
//customLog("getStaticBlizzData","Done getting realm data...");


/** 
 *		Gets JSON pet data from blizzards master pet API.
 *		This does not need to be run often as the pet list
 *		should only change around patches.
 */
function getPetData($region, $locale)
{
	// Connect to database
	$conn = dbConnect($region);
	$content = file_get_contents("https://".$region.".api.battle.net/wow/pet/?locale=".$locale."&apikey=r52egwgeefzmy4jmdwr2u7cb9pdmseud");
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
			echo "Error: " . $sql . "<br>";
		}
	}
}


/** 
 *		Gets JSON realm data from blizzards realm list API.
 *		This does not need to be run often as the realm list
 *		should only change around patches.

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
			echo "Error: " . $sql . "<br>";
		}	
		
		// Parsing Connected Realms Data
		foreach ($connectedRealms as $childSlug){
			
			// If the connected realm is not this realm...store it
			if(strcmp($childSlug,$slug))
			{
				$sql = "INSERT INTO realms_connected (slug_parent, slug_child)
						VALUES ('" . $slug . "', '" . $childSlug . "')"
						. " ON DUPLICATE KEY UPDATE "
						. "slug_parent='" . $slug . "', slug_child='" . $childSlug . "'";

				if ($conn->query($sql) === TRUE) {
					//echo "New realm created successfully<br/>";
				} else {
					echo "Error: " . $sql . "<br>";
				}	
			}			
		}
	}
}
 */
function getRealmData()
{
	// Getting US realm data
	$accessURL = "https://us.battle.net/oauth/token?grant_type=client_credentials&client_id=r52egwgeefzmy4jmdwr2u7cb9pdmseud&client_secret=GY3NmjDgvrvJBzs2RwpMu8A5EJGG4SD8";
	$realmURL = "https://us.api.battle.net/data/wow/realm/?namespace=dynamic-us&locale=en_US&access_token=";
	$locale = "en_US";
	$region = "US";
	getRegionRealmData($accessURL, $realmURL, $locale, $region);
	
	// Geting EU realm data
	$accessURL = "https://eu.battle.net/oauth/token?grant_type=client_credentials&client_id=r52egwgeefzmy4jmdwr2u7cb9pdmseud&client_secret=GY3NmjDgvrvJBzs2RwpMu8A5EJGG4SD8";
	$realmURL = "https://eu.api.battle.net/data/wow/realm/?namespace=dynamic-eu&locale=en_GB&access_token=";
	$locale = "en_GB";
	$region = "EU";
	getRegionRealmData($accessURL, $realmURL, $locale, $region);
	
	// Connected US realm data
	$accessURL = "https://us.battle.net/oauth/token?grant_type=client_credentials&client_id=r52egwgeefzmy4jmdwr2u7cb9pdmseud&client_secret=GY3NmjDgvrvJBzs2RwpMu8A5EJGG4SD8";
	$allConnRealmURL = "https://us.api.battle.net/data/wow/connected-realm/?namespace=dynamic-us&locale=en_US&access_token=";
	$locale = "en_US";
	$region = "US";
	getRegionConnectedRealmData($accessURL, $allConnRealmURL, $locale, $region);
	
	// Connected EU realm data
	$accessURL = "https://eu.battle.net/oauth/token?grant_type=client_credentials&client_id=r52egwgeefzmy4jmdwr2u7cb9pdmseud&client_secret=GY3NmjDgvrvJBzs2RwpMu8A5EJGG4SD8";
	$allConnRealmURL = "https://eu.api.battle.net/data/wow/connected-realm/?namespace=dynamic-eu&locale=en_GB&access_token=";
	$locale = "en_GB";
	$region = "EU";
	getRegionConnectedRealmData($accessURL, $allConnRealmURL, $locale, $region);
}

/**
	TODO
*/
function getRegionRealmData($accessURL, $realmURL, $locale, $region) 
{
	// Connect to database
	$conn = dbConnect($region);
	
	$accessContent = file_get_contents($accessURL);
	$accessResult  = json_decode($accessContent, true);
	$accessToken = $accessResult['access_token'];

	$content = file_get_contents($realmURL.$accessToken);
	$result  = json_decode($content, true);
	$realms = $result['realms'];
	
	foreach ($realms as $currentRealm) {
		$slug = $currentRealm['slug'];
		$name = $currentRealm['name'];
		$id = $currentRealm["id"]; 
		
		$sql = "INSERT INTO realms (slug, name, locale, id)
		VALUES ('" . $slug . "', '" . str_replace('\'', '\'\'' ,$name) . "', '" . $locale . "', '".$id."')"
				. " ON DUPLICATE KEY UPDATE "
				. "locale='" . $locale . "', name='" . str_replace('\'', '\'\'' ,$name) . "'";
				
		echo ($sql."<br/>");
		if ($conn->query($sql) === TRUE) {
			echo "New realm created successfully<br/>";
		} else {
			echo "Error: " . $sql . "<br>";
		}	
	}
}

/**
	TODO
*/
function getRegionConnectedRealmData($accessURL, $allConnRealmURL, $locale, $region)
{
	// Connect to database
	$conn = dbConnect($region);
	
	// Get the access token
	$accessContent = file_get_contents($accessURL);
	$accessResult  = json_decode($accessContent, true);
	$accessToken = $accessResult['access_token'];
	
	// Get the intial connected realms content
	$content = file_get_contents($allConnRealmURL.$accessToken);
	$result  = json_decode($content, true);
	$connectedRealms = $result['connected_realms'];
	
	// Each "currentRealm" is a grouping of connected realm objects
	foreach ($connectedRealms as $currentRealm) {
		$aConnRealmURL = $currentRealm["href"];
		$aRealmContent = file_get_contents($aConnRealmURL."&locale=".$locale."&access_token=".$accessToken);
		$aRealmResult  = json_decode($aRealmContent, true);
		$aRealmsConnected = $aRealmResult["realms"];
		
		$realmGroupId = [];
		$realmGroupSlug = [];
		
		// For each connected realm we need to create entires into the realms_connected table
		foreach ($aRealmsConnected as $key=>$aConnRealm) {		
			array_push($realmGroupId, $aConnRealm['id']);
			array_push($realmGroupSlug, $aConnRealm['slug']);
		}
		
		// $i index is the parent, $j index is the child
		for($i = 0; $i<sizeof($realmGroupId); $i++) {
			for($j = 0; $j<sizeof($realmGroupId); $j++) {
					if($i != $j) {
						$sql = "INSERT INTO realms_connected (slug_parent, slug_child, id_parent, id_child)
						VALUES ('" . $realmGroupSlug[$i] . "', '" . $realmGroupSlug[$j] . "', '" . $realmGroupId[$i] . "', '" . $realmGroupId[$j] . "')"
						. " ON DUPLICATE KEY UPDATE "
						. "slug_parent='" . $realmGroupSlug[$i] . "', slug_child='" . $realmGroupSlug[$j] . "'";
						
						if ($conn->query($sql) === TRUE) {
							echo "New realm created successfully<br/>";
						} else {
							echo "Error: " . $sql . "<br>";
						}	
					}					
			}
		}

		
	}
}


?>




