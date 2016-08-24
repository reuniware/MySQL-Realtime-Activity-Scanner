<?php

define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");


define("RESTRICT_SCHEMA", ""); //Here the schemas separated with ","

echo "MYSQL REALTIME SCANNER v0.1 - Detects any change in all tables in all schemas\r\n\r\n";

$conn = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\r\n");
}

$tablesarray = array();
$sql = "select table_schema, table_name from information_schema.tables";
$r = mysqli_query($conn, $sql);
while( $row = $r->fetch_assoc())
{
	$tableschema = $row['table_schema'];
	$tablename = $row['table_name'];
	echo "Scanned table name = $tableschema.$tablename\r\n";

	if (trim(RESTRICT_SCHEMA) != ''){
		$schemas = explode(',',RESTRICT_SCHEMA);
		foreach($schemas as $tableschemars){
			if ( strtolower(trim($tableschemars)) == strtolower(trim($tableschema)) )
			{
				array_push($tablesarray, $tableschema . "." . $tablename);
			}
		}
	} else {
		array_push($tablesarray, $tableschema . "." . $tablename);
	}

}

$lastcount = array();
$firstcount = array();

foreach($tablesarray as $tablename){
	$lastcount[$tablename] = -1;
	$firstcall[$tablename] = true;
}

while (true){

	foreach($tablesarray as $tablename){

		//echo "Processing $tablename\r\n";

		$sql = "SELECT count(*) FROM $tablename";

		$r = mysqli_query($conn, $sql);
		if ($r != false) {
			$row = $r->fetch_assoc();

			//$count = $r->num_rows;
			$count = $row['count(*)'];

			if ( ($lastcount[$tablename] != -1) && ($count != $lastcount[$tablename]) ){
				$objDateTime = new DateTime('NOW');
				$dt = $objDateTime->format(DateTime::ISO8601);
				echo "$dt : Données modifiées dans $tablename : Il y a $count lignes dans $tablename\r\n";		
			} 

			if ($firstcall[$tablename] == true){
				$firstcall[$tablename] = false;
				$objDateTime = new DateTime('NOW');
				$dt = $objDateTime->format(DateTime::ISO8601);
				echo "$dt : Il y a initialement $count lignes dans $tablename\r\n";
			}

			$lastcount[$tablename] = $count;
		} else {
			//echo "Erreur : " . mysqli_errno($conn) . " - " . mysqli_error($conn) . "\r\n";
		}

		//if ($r != false) $r->close();

	}

	sleep(5);
}


