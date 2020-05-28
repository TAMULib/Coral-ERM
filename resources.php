<?php

header("Content-type: text/xml");

$global_config = parse_ini_file("common/configuration.ini", TRUE);
$resources_config = parse_ini_file("resources/admin/configuration.ini", TRUE);

$host = $global_config['database']['host'];
$user = $global_config['database']['username'];
$pass = $global_config['database']['password'];
$database = $resources_config['settings']['resourcesDatabaseName'];
  
$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $linkID) or die("Could not find database.");

$query = "SELECT providerText, createDate, updateDate, resourceID, titleText, descriptionText, resourceURL"; 
$query = $query . " FROM Resource";
$query = $query . " Where titleText like '%mobile%'";
$query = $query . " order by titleText;";

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
        $resourceURL = htmlspecialchars($row['resourceURL']);		
		
	$xml_output .= "\t\t<resourceURL>" . $resourceURL . "</resourceURL>\n";
	$xml_output .= "\t\t<providerText>" . htmlspecialchars($row['providerText']) . "</providerText>\n";	
	$xml_output .= "\t\t<createDate>" . $row['createDate'] . "</createDate>\n";	
	$xml_output .= "\t\t<updateDate>" . $row['updateDate'] . "</updateDate>\n";	
	
    $xml_output .= "\t</resource>\n";
}

$xml_output .= "</resources>";

echo $xml_output;

?> 
