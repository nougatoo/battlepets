<?php

require_once('../../scripts/util.php');

$text = $_POST['text']; // Used to avoid having another .php files...may refactor later
$region = $_POST['region'];

$text = str_replace("'","''",$text);
$text = str_replace(";",":",$text);

$conn = dbConnect($region);	
$result = $conn->prepare("INSERT INTO bug_reports (date, text) VALUES (NOW(), ?)");
$result->bindParam(1, $text);
$result->execute();

?>