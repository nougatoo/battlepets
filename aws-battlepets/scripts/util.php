<?php


/**
	TODO
*/
function standard_deviation($sample){
	if(is_array($sample)){
		$mean = array_sum($sample) / count($sample);
		foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
		return sqrt(array_sum($devs) / (count($devs) - 1));
	}
}


function dbConnect() {
	
	/*
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
	*/
	
	static $conn;
	
	$dbserver = 'mysql:dbname=' . "battlepets". ';host=' . "battlepets-example.cqei6votoxra.us-east-2.rds.amazonaws.com";
	$conn = new PDO($dbserver, "nougatoo", "Brandonbrien12!");
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	return $conn;
		
}

function customLog($type, $text) {
	
	echo ("[".date('Y-m-d H:i:s')."][".$type."] -- ".$text."\n");
	
}

/**
	TODO: Returns a string of wow gold
*/
function convertToWoWCurrency($value) {
	
	$value = floor($value);
	$copper = $value%100;
	$value = ($value-$copper)/100;
	
	$silver = $value%100;
	$value = ($value-$silver)/100;
	
	$gold = $value;
	
	return number_format($gold).'<strong style="color:#CAAA00">g</strong> '.$silver.'<strong style="color:#777">s</strong> '.$copper.'<strong style="color:#B87333">c</strong>';
	
}

/**
	TODO
*/

function getRealmNameFromSlug($slug)
{
	$conn = dbConnect();
	
	$result = $conn->prepare("SELECT name FROM realms WHERE slug = ?");
	$result->bindParam(1, $slug);
	$result->execute();
	
	while($row = $result->fetch()) {		
		return $row['name'];
	}

}
?>