<?php


/**
	Calculates the standard deviation of an array
	
	@param Array $sample - Array of numbers
*/
function standard_deviation($sample){
	if(is_array($sample)){
		$mean = array_sum($sample) / count($sample);
		foreach($sample as $key => $num) $devs[$key] = pow($num - $mean, 2);
		return sqrt(array_sum($devs) / (count($devs) - 1));
	}
}

/**
	Connects to the regions database
	
	@param String $region - Usually either US or EU
	
	@return PDO Database connection
*/
function dbConnect($region) {
	
	$configs = include("/var/app/current/application/configs/dbConfigs.php");
	static $conn; 
	$dbserver = 'mysql:dbname=' . $configs["dbName".$region]. ';host=' . $configs["dbHost"];
	$conn = new PDO($dbserver, $configs["dbUser"], $configs["dbPwd"], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	return $conn;
		
}

/**
	Echo with the type and the datetime
	
	@param String $type - Either INFO or ERROR
	@param String $text - Text that needs to be logged
*/
function customLog($type, $text) {
	
	echo ("[".date('Y-m-d H:i:s')."][".$type."] -- ".$text."\n");
	
}

/**
	Returns a string of wow gold with hightlighted g,s, and c leetters
	
	@param int $value - Integer amount of copper
	
	@return String 
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
	Queries the database and find name of realm from a slug
	
	@param String $slug - Realm slug
	@param String $region - Usually either US or EU
	
	@return String - Realm corresponding the slug passed in
*/

function getRealmNameFromSlug($slug, $region)
{
	$conn = dbConnect($region);
	
	$result = $conn->prepare("SELECT name FROM realms WHERE slug = ?");
	$result->bindParam(1, $slug);
	$result->execute();
	
	while($row = $result->fetch()) {		
		return $row['name'];
	}

}
?>