<?php

require_once('../scripts/util.php');

$configs = include('../application/configs/configs.php');

$sortBy = $_POST['sortBy'];
$sortOrder = $_POST['sortOrder'];

$conn = dbConnect();

$tableHTML = 	'<table class="table table-striped table-hover">
							<tr>
								<th onclick="sortTable(0)">Name</th>
								<th onclick="sortTable(1)">Global Market Value</th>
							</tr>
							<tbody id="myTable1">';

$sql = 'SELECT 
				pets.species_id, pets.name, market_value_pets_hist_median.market_value_hist_median
			FROM 
				market_value_pets_hist_median
			INNER JOIN 
				pets ON market_value_pets_hist_median.species_id = pets.species_id
			ORDER BY 
				'.$sortBy.' '.$sortOrder;
				
$result = $conn->prepare($sql);
$result->execute();

if($result) {	
	while($row = $result->fetch()) {
		$tableHTML .= '<tr>';
		$tableHTML .= '<td>' . $row['name'] .'</td>';
		$tableHTML .= '<td>' . convertToWoWCurrency($row['market_value_hist_median']) . '</td>';
		$tableHTML .= '</tr>';	
	}
}


$tableHTML .= '</tbody></table>';

echo $tableHTML;

?>