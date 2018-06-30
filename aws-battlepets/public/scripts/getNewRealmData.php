<?php

require_once('../../scripts/util.php');

$configs = include('../../application/configs/configs.php');
$characters = $_POST['characters'];
$realms = $_POST['realms'];
$region = $_POST['region'];
$locale;

if($region == "US")
	$locale  = "en_US";
else if ($region == "EU")
	$locale = "en_GB";

$url = 'https://'.$region.'.api.battle.net/wow/character/' . $realms[0] . '/' . $characters[0] . '?fields=pets&locale='.$locale.'&apikey='.$configs["apiKey"];
$petsAPIResponse = file_get_contents($url);
$results = json_decode($petsAPIResponse, true);	
$cagedPetsRaw = $results['pets']['collected'];
$cagedPetsProc = [];
$cagedPetsIds = [];
$finalRealmData = [];

if(sizeof($cagedPetsRaw) == 0)
{
	echo "No pets found";
	return;
}

// Create a "processed" array of just the species id. The raw has a bunch of data we're not interested in
for($i = 0; $i<sizeof($cagedPetsRaw); $i++)
{
	array_push($cagedPetsProc, $cagedPetsRaw[$i]['stats']['speciesId']);
}

$cagedCounts = array_count_values($cagedPetsProc);
$petRestriction = "";

// Builds an array of species id that the user owns and builds a partial SQL restriction to be used in a query
foreach($cagedCounts as $key => $petCount) {
	$petRestriction .= "'". $key . "',";
	array_push($cagedPetsIds, $key);
}

// Remove the last comma and add brackets
$petRestriction = "(" . substr($petRestriction, 0, -1) . ")";

$conn = dbConnect($region);
$sql = "SELECT sum(min_buyout) as realmSum, realm 
										FROM (
											SELECT species_id, min(buyout) AS min_buyout, realm
											FROM auctions_hourly_pet 
											WHERE species_id IN " . $petRestriction . "GROUP BY species_id, realm) b
										GROUP BY REALM
										ORDER BY realmSum DESC";								
$result = $conn->prepare($sql);
$result->execute();	

if($result) {	
	while($row = $result->fetch()) {
		
		$realmPetId = [];
		$speciesDiff = null;
		$speciesDiffRestriction = "";
		$speciesDiffSum = 0;
		
		// Build an array of distinct species that exists on this realm's AH
		$realmSQL = "SELECT DISTINCT species_id FROM auctions_hourly_pet WHERE realm = '" . $row["realm"] . "'";
		$resultRealm = $conn->prepare($realmSQL);
		$resultRealm->execute();	
		
		if($resultRealm) {	
			while($row2 = $resultRealm->fetch()) {
				array_push($realmPetId, $row2['species_id']);
			}
		}
		
		// Find the pets that are caged, but don't exist on the realm
		$speciesDiff = array_diff($cagedPetsIds, $realmPetId);
		
		// Get the total market value for any pets that are in speciesDiff and add it to realmSum
		foreach($speciesDiff as $key => $species) {
			$speciesDiffRestriction .= "'" . $species . "',";
		}
		
		$speciesDiffRestriction = "(" . substr($speciesDiffRestriction, 0, -1) . ")";
		$speciesDiffSQL = "SELECT SUM(market_value_hist_median) as sum_median FROM market_value_pets_hist_median WHERE species_id IN " . $speciesDiffRestriction;
		$speciesDiffResult = $conn->prepare($speciesDiffSQL);
		$speciesDiffResult->execute();
		
		if($speciesDiffResult) {	
			while($row3 = $speciesDiffResult->fetch()) {
				$speciesDiffSum += $row3['sum_median'];
			}
		}
		
		$finalRealmData[$row["realm"]] = ($row["realmSum"] + $speciesDiffSum);
		//echo ( convertToWoWCurrency($row["realmSum"] + $speciesDiffSum) . " " . $row["realm"] . "<br/>");
	}
}

// Finds the max of connected realms
$completedRealms = [];

foreach($finalRealmData as $key => $realmValue) {
	
	if(!in_array($key, $completedRealms)) {
		$connectedRealms = [];
		$maxValue = 0;
		array_push($connectedRealms, $key);
		
		$sql = "SELECT slug_parent, slug_child FROM realms_connected WHERE slug_parent = '" . $key . "'";											
		$result = $conn->prepare($sql);
		$result->execute();	

		if($result) {	
			while($row = $result->fetch()) {
				array_push($connectedRealms, $row['slug_child']);
			}
		}	

		// Find the max value
		for($i = 0; $i < sizeof($connectedRealms); $i++) {		
			if($finalRealmData[$connectedRealms[$i]] > $maxValue)
				$maxValue = $finalRealmData[$connectedRealms[$i]];			
		}
		
		// Set all realms to the new max value
		for($i = 0; $i < sizeof($connectedRealms); $i++) {
			$finalRealmData[$connectedRealms[$i]] = $maxValue;
			array_push($completedRealms, $connectedRealms[$i]);
		}		
	}
}

// Reverse sort - greatest to least
arsort($finalRealmData);

$tableHTML =	'<table class="table table-striped table-hover realmTable">
						<tr style="background-color:white; color: #6b6b6b;">
							<th class="realmTableHeader">Realm</th>
							<th class="realmTableHeader">Region Market Value</th>
						</tr>
						<tbody id="myTable1">';
						
foreach($finalRealmData as $key => $realmValue) {
	
		$tableHTML .= '<tr>';
		$tableHTML .= '<td>' . getRealmNameFromSlug($key, $region) .'</td>';
		$tableHTML .= '<td>' . convertToWoWCurrency($realmValue) . '</td>';
		$tableHTML .= '</tr>';
}

$tableHTML .= '</tbody></table>';
echo $tableHTML;

?>