<?php

define('ADMIN_DIR', dirname(__FILE__) . '/resources/admin/');
define('BASE_DIR', dirname(__FILE__) . '/resources/');
define('CLASSES_DIR', ADMIN_DIR . 'classes/');

require_once(CLASSES_DIR . 'common/' . 'Utility.php');
require_once(CLASSES_DIR . 'common/' . 'NamedArguments.php');
require_once(CLASSES_DIR . 'common/' . 'Object.php');
require_once(CLASSES_DIR . 'common/' . 'DynamicObject.php');
require_once(CLASSES_DIR . 'common/' . 'Configuration.php');

$config = new Configuration;

$host = $config->database->host;
$username = $config->database->username;
$password = $config->database->password;
$databaseName = $config->database->name;
	
header("Content-type: text/xml");

$linkID = mysql_connect($host, $username, $password) or die("Could not connect to host.");
mysql_select_db($databaseName, $linkID) or die("Could not find database.");

$query = "
SELECT 
  `Resource`.`resourceID`,
  `Resource`.`titleText`,
  `Resource`.`createDate`,
  `Resource`.`updateDate`,
  `AcquisitionType`.`shortName`,
  `Resource`.`descriptionText`,
  `ResourceType`.`shortName` AS `ResourceType`,
  `Resource`.`providerText`
FROM
  `Resource`
  INNER JOIN `ResourceAcquisition` ON (`Resource`.`resourceID` = `ResourceAcquisition`.`resourceID`)
  INNER JOIN `ResourceType` ON (`Resource`.`resourceTypeID` = `ResourceType`.`resourceTypeID`)
  INNER JOIN `Status` ON (`Resource`.`statusID` = `Status`.`statusID`)
  INNER JOIN `AcquisitionType` ON (`ResourceAcquisition`.`acquisitionTypeID` = `AcquisitionType`.`acquisitionTypeID`)
WHERE
  (`AcquisitionType`.`acquisitionTypeID` = 3 OR 
  `AcquisitionType`.`acquisitionTypeID` = 8) AND 
  `Resource`.`statusID` = 1 OR 
  (`AcquisitionType`.`acquisitionTypeID` = 3 OR 
  `AcquisitionType`.`acquisitionTypeID` = 8) AND 
  `Resource`.`statusID` = 2
ORDER BY
  `Resource`.`titleText`
";

//echo $query . "<br>";

$resultID = mysql_query($query, $linkID) or die("<?xml version=\"1.0\"?>\n<resources>\n</resources>");

$xml_output = "<?xml version=\"1.0\"?>\n";
$xml_output .= "<resources>\n";

for($x = 0 ; $x < mysql_num_rows($resultID) ; $x++){
    $row = mysql_fetch_assoc($resultID);
    $xml_output .= "\t<resource>\n";
    $xml_output .= "\t\t<resourceID>" . $row['resourceID'] . "</resourceID>\n";

		// Escaping illegal characters
        $titleText = htmlspecialchars($row['titleText']);		
	
	$xml_output .= "\t\t<titleText>" . $titleText . "</titleText>\n";
	
        // Escaping illegal characters
        $descriptionText = htmlspecialchars($row['descriptionText']);

	$xml_output .= "\t\t<descriptionText>" . $descriptionText . "</descriptionText>\n";

		// Escaping illegal characters
		$resourceURL =	"http://ezproxy.library.tamu.edu/login?url=http://coral.library.tamu.edu/resourcelink.php?resource=" . $row["resourceID"];						

	$xml_output .= "\t\t<resourceURL>" . $resourceURL . "</resourceURL>\n";
	
		// Escaping illegal characters
        $providerText = htmlspecialchars($row['providerText']);		
	
	$xml_output .= "\t\t<providerText>" . $providerText . "</providerText>\n";	
	
	$xml_output .= "\t\t<createDate>" . $row['createDate'] . "</createDate>\n";	
	$xml_output .= "\t\t<updateDate>" . $row['updateDate'] . "</updateDate>\n";	
	$xml_output .= "\t\t<resourceType>" . $row['ResourceType'] . "</resourceType>\n";	
	
	$query2 = "SELECT `ResourceNote`.`noteText` FROM `ResourceNote` WHERE `ResourceNote`.`noteTypeID` = 9 AND `ResourceNote`.`resourceID` = " . $row['resourceID'];
	$note_text = mysql_query($query2, $linkID);
	$row2 = mysql_fetch_assoc($note_text);
	$notes = htmlspecialchars($row2['noteText']);
	
	$xml_output .= "\t\t<noteText>" . $notes . "</noteText>\n";	
	
    $xml_output .= "\t</resource>\n";
}

$xml_output .= "</resources>";

echo $xml_output;

?> 