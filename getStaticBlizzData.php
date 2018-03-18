<?php

require_once('util.php');

set_time_limit(0);
ini_set('memory_limit', '1024M');

customLog("getStaticBlizzData","Getting pet data...");
getPetData();
customLog("getStaticBlizzData","Done getting pet data...");

customLog("getStaticBlizzData","Geting realm data...");
getRealmData();
customLog("getStaticBlizzData","Done getting realm data...");

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


?>