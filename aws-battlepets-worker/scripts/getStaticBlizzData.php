<?php

require_once('../../scripts/util.php');
$configs = include('/var/app/current/application/configs/configs.php');
header ('Content-type: text/html; charset=utf-8');

set_time_limit(0);

getPetData("US", "en_US");
getPetData("EU", "en_GB");

// Gets both EU and US realm data
getRealmData();

/** 
 	Gets JSON pet data from blizzards master pet API.
 	This does not need to be run often as the pet list
 	should only change around patches.
	
	@param String $region - Usually either US or EU
	@param String $locale - Locale value corresponding to the region
 */
function getPetData($region, $locale)
{
	global $configs;
	// Connect to database
	$conn = dbConnect($region);
	$content = file_get_contents("https://".$region.".api.battle.net/wow/pet/?locale=".$locale."&apikey=".$configs["apiKey"]);
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
	Gets JSON EU and US realm data from blizzards realm list API.
	This does not need to be run often as the realm list
	should only change around patches.
	
*/
function getRealmData()
{
	global $configs;
	
	// Getting US realm data
	$accessURL = "https://us.battle.net/oauth/token?grant_type=client_credentials&client_id=".$configs["apiKey"]."&client_secret=".$configs["apiSecret"];
	$realmURL = "https://us.api.battle.net/data/wow/realm/?namespace=dynamic-us&locale=en_US&access_token=";
	$locale = "en_US";
	$region = "US";
	getRegionRealmData($accessURL, $realmURL, $locale, $region);
	
	// Geting EU realm data
	$accessURL = "https://eu.battle.net/oauth/token?grant_type=client_credentials&client_id=".$configs["apiKey"]."&client_secret=".$configs["apiSecret"];
	$realmURL = "https://eu.api.battle.net/data/wow/realm/?namespace=dynamic-eu&locale=en_GB&access_token=";
	$locale = "en_GB";
	$region = "EU";
	getRegionRealmData($accessURL, $realmURL, $locale, $region);
	
	// Connected US realm data
	$accessURL = "https://us.battle.net/oauth/token?grant_type=client_credentials&client_id=".$configs["apiKey"]."&client_secret=".$configs["apiSecret"];
	$allConnRealmURL = "https://us.api.battle.net/data/wow/connected-realm/?namespace=dynamic-us&locale=en_US&access_token=";
	$locale = "en_US";
	$region = "US";
	getRegionConnectedRealmData($accessURL, $allConnRealmURL, $locale, $region);
	
	// Connected EU realm data
	$accessURL = "https://eu.battle.net/oauth/token?grant_type=client_credentials&client_id=".$configs["apiKey"]."&client_secret=".$configs["apiSecret"];
	$allConnRealmURL = "https://eu.api.battle.net/data/wow/connected-realm/?namespace=dynamic-eu&locale=en_GB&access_token=";
	$locale = "en_GB";
	$region = "EU";
	getRegionConnectedRealmData($accessURL, $allConnRealmURL, $locale, $region);
}

/**
	Gets all the realm names, slugs and ids from the blizzard API for a particular region
	and puts it into the regions database
	
	@param String $accessURL - URL to get an access token from blizzard for my API key and secret
	@param String $realmURL - URL to get realm data before adding the access token
	@param String $region - Usually either US or EU
	@param String $locale - Corresponding locale to pair with the region
*/
function getRegionRealmData($accessURL, $realmURL, $region, $locale) 
{
	// Connect to database
	$conn = dbConnect($region);
	
	// Gets the new access token from blizzard. This should not take long, so don't store expiration
	$accessContent = file_get_contents($accessURL);
	$accessResult  = json_decode($accessContent, true);
	$accessToken = $accessResult['access_token'];

	// Get actual realm data now
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
	Gets all the connected realm data from the blizzard API for a particular region
	and puts it into the regions database
	
	@param String $accessURL - URL to get an access token from blizzard for my API key and secret
	@param String $allConnRealmURL - URL to get connected realm data before adding the access token
	@param String $region - Usually either US or EU
	@param String $locale - Corresponding locale to pair with the region
*/
function getRegionConnectedRealmData($accessURL, $allConnRealmURL, $region, $locale )
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




