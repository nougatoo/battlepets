<?php
	require_once('../../scripts/util.php');
	
	$region = $_POST['region'];
	$conn = dbConnect($region);
	
	$sql = "SELECT slug, name FROM realms";
	$result = $conn->query($sql);
	
	echo ('<option value=""></option>'); // Empty value first
	
	if($result) {
		while($row = $result->fetch()) {
			echo ('<option value="'.$row['slug'].'">'.$row['name'].'</option>');
		}	
	}
?>






