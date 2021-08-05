<?php
$txtout = "";

require_once('config.php');
require_once('db.php');

mysql_connect($SQLIPAddress,$UserName,$Password) or  die('Could not connect: ' . mysql_error());
mysql_select_db($db) or die('Select error: ' . mysql_error());

$txtout = $txtout . "LINK TITLE REQUIRED - 255 CHAR MAX" . "\t";
$txtout = $txtout . "LINK URL REQUIRED - 1000 CHAR. MAX" . "\t";
$txtout = $txtout . "DESCRIPTION OPTIONAL - 500 CHAR. MAX" . "\t";
$txtout = $txtout . "MORE INFORMATION OPTIONAL - 5000 CHAR. MAX" . "\t";
$txtout = $txtout . "USE PROXY? OPTIONAL - FALSE IS DEFAULT" . "\t";
$txtout = $txtout . "\r\n";
		
$count = 0;

$sqloverride = "
SELECT 
  `Resource`.`metalibID`,
  `Resource`.`titleText`,
  `Resource`.`resourceAltURL`,
  `Resource`.`resourceID`,
  `ResourceFormat`.`shortName` AS `ResourceFormat`,
  `ResourceType`.`shortName` AS `ResourceType`,
  `LicenseStatus`.`shortName` AS `LicenseStatus`,
  `Resource`.`providerText`,
  CONCAT('http://coral.library.tamu.edu/resourcelink.php?resource=', CAST(`Resource`.`resourceID` AS CHAR)) AS `resourceURL`,
  `AcquisitionType`.`shortName` AS `AcquisitionType`,
  `Status`.`shortName` AS `Status`,
  `Resource`.`descriptionText`,
  `Resource`.`descriptionText`
FROM
  `Resource`
  INNER JOIN `ResourceType` ON (`Resource`.`resourceTypeID` = `ResourceType`.`resourceTypeID`)
  INNER JOIN `ResourceFormat` ON (`Resource`.`resourceFormatID` = `ResourceFormat`.`resourceFormatID`)
  INNER JOIN `LicenseStatus` ON (`Resource`.`statusID` = `LicenseStatus`.`licenseStatusID`)
  INNER JOIN `Status` ON (`Resource`.`statusID` = `Status`.`statusID`)
  INNER JOIN `ResourceAcquisition` ON (`Resource`.`resourceID` = `ResourceAcquisition`.`resourceID`)
  INNER JOIN `AcquisitionType` ON (`ResourceAcquisition`.`acquisitionTypeID` = `AcquisitionType`.`acquisitionTypeID`)
WHERE
  `Status`.`statusID` = 1 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 1 OR 
  `Status`.`statusID` = 1 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 7 OR 
  `Status`.`statusID` = 1 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 6 OR 
  `Status`.`statusID` = 1 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 9 OR 
  `Status`.`statusID` = 1 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 10 OR 
  `Status`.`statusID` = 2 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 1 OR 
  `Status`.`statusID` = 2 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 7 OR 
  `Status`.`statusID` = 2 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 6 OR 
  `Status`.`statusID` = 2 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 9 OR 
  `Status`.`statusID` = 2 AND 
  `ResourceAcquisition`.`acquisitionTypeID` <> 3 AND 
  `ResourceType`.`resourceTypeID` = 10
ORDER BY
  `ResourceID`

";

if ($_GET["debug"]) {
	echo "<br>\r";  
	echo $sqloverride;
	echo "<br>\r";
	echo "<br>\r";
}

$rs = mysql_query($sqloverride) or die('mysql error: ' . mysql_error());
	
	while($row = mysql_fetch_array( $rs )) {
			if ($count == 500) {
				if ($_GET["debug"]) {
					break;
				}
			} else {
				$count = $count + 1;
			}
			

		if ( (!is_null($row['resourceURL'])) && (!is_null($row['resourceURL'])) )	{	 
		
			if (is_null($row['titleText'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . trim($row['titleText']);
				$txtout = $txtout . "\t"; //"2451" **
			}					

			if (is_null($row['resourceURL'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . substr(trim($row['resourceURL']),0, 999);
				$txtout = $txtout . "\t"; //"85641"
			}		

			$txtout = $txtout . "\t"; //"More Info"

			if (is_null($row['descriptionText'])) {
				$txtout = $txtout . "\t";
			} else {
			
				$tmpTxt = ereg_replace("\r\n?", "\n", trim($row['descriptionText']));
				$tmpTxt = str_replace("\n", '<BR>', $tmpTxt);
				$tmpTxt = nl2br($tmpTxt);					
			
				$txtout = $txtout . substr(trim($tmpTxt),0, 5000);
				$txtout = $txtout . "\t"; //"520" 
			}
			
			$txtout = $txtout . "FALSE\t"; //"Use Proxy"		
		
			$txtout = $txtout . "\r\n";
		}
	}

	header("Content-Type: text/tsv");
	header('Content-Disposition: attachment; filename=coral_csv_extract.tsv');
	echo $txtout;
	
?>