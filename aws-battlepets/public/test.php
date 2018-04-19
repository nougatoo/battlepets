<?php


require_once('../scripts/util.php');


set_time_limit(0);
ini_set('memory_limit', '1024M');

$conn = dbConnect();
$distinctPets = [];

$result = $conn->prepare("SELECT distinct market_value_pets.species_id, pets.name FROM market_value_pets INNER JOIN pets ON pets.species_id = market_value_pets.species_id");
$result->execute();	

if($result) {	
	while($row = $result->fetch()) {
		array_push($distinctPets, $row);
	}
}


// Go through each pet 
foreach ($distinctPets as $key => $aSpecies) {

	$marketValues = [];
	
	$sql = "SELECT market_value_pets.species_id, market_value_pets.market_value, pets.name, market_value_pets_hist.market_value_hist FROM market_value_pets INNER JOIN pets ON pets.species_id = market_value_pets.species_id INNER JOIN market_value_pets_hist ON market_value_pets_hist.species_id = market_value_pets.species_id WHERE market_value_pets.species_id = '".$aSpecies['species_id']."' AND ((`market_value_pets`.`date` >= (CURDATE() - INTERVAL 100 DAY))
            AND (`market_value_pets`.`date` < (CURDATE() + INTERVAL 1 DAY))) AND market_value_pets.market_value < (market_value_pets_hist.market_value_hist*2)";
	
	$result = $conn->prepare($sql);
	$result->execute();
	
	if($result) {	
		while($row = $result->fetch()) {
				array_push($marketValues, $row['market_value']);
		}
	}
	
	// Now we have all buyout for a species
	
	// Sort least to greatest
	sort($marketValues);
	$medianIndex = floor(sizeof($marketValues)/2);
	$medianValue = $marketValues[$medianIndex];
	
	$mvMedianInsertSql = "INSERT INTO market_value_pets_hist_median (`species_id`,  `market_value_hist_median`) VALUES ('".$aSpecies['species_id']."' , '".$medianValue."')";
		
	echo ($mvMedianInsertSql."<br/>");
	$result = $conn->prepare($mvMedianInsertSql);
	$result->execute();		
	
}

?>