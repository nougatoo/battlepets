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
	$marketValueHist = -1;
	
	$sql = "SELECT market_value_pets.species_id, market_value_pets.market_value, pets.name, market_value_pets_hist_median.market_value_hist_median, market_value_pets_hist.market_value_hist
		FROM market_value_pets 
		INNER JOIN pets ON pets.species_id = market_value_pets.species_id 
		INNER JOIN market_value_pets_hist_median ON market_value_pets_hist_median.species_id = market_value_pets.species_id 
        INNER JOIN market_value_pets_hist on market_value_pets_hist.species_id = pets.species_id
		WHERE market_value_pets.species_id = '".$aSpecies['species_id']."' AND
			((`market_value_pets`.`date` >= (CURDATE() - INTERVAL 50 DAY))
			AND (`market_value_pets`.`date` < (CURDATE() + INTERVAL 1 DAY))) 
			AND market_value_pets.market_value < (market_value_pets_hist_median.market_value_hist_median*2)";
	
	$result = $conn->prepare($sql);
	$result->execute();
	
	if($result) {	
		while($row = $result->fetch()) {
				array_push($marketValues, $row['market_value']);
				$marketValueHist = $row['market_value_hist'];
		}
	}
	

	// Now we have all buyout for a species
	
	// Sort least to greatest
	sort($marketValues);
	$medianIndex = floor(sizeof($marketValues)/2);
	$medianValue = $marketValues[$medianIndex];
	
	// TODO - ANYTHING OVER 25K...USER MEDIAN
	// user average for anything else
	if($medianValue < 250000000) {
		$medianValue = ($medianValue + $marketValueHist) / 2 ;
	}
	else if ($medianValue < 100000000) {
		$medianValue = $marketValueHist;
	}
	
	$mvMedianInsertSql = "INSERT INTO market_value_pets_hist_median (`species_id`,  `market_value_hist_median`) VALUES ('".$aSpecies['species_id']."' , '".$medianValue."') ON DUPLICATE KEY UPDATE market_value_hist_median = ".$medianValue.";";
		
	echo ($mvMedianInsertSql."<br/>");
	$result = $conn->prepare($mvMedianInsertSql);
	$result->execute();		
	
}

?>