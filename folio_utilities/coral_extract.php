<?php
$txtout = "";

require_once('config.php');
require_once('db.php');

$conn = new mysqli($SQLIPAddress, $UserName, $Password, $db) or  die('Could not connect: ' . mysqli_error());
mysqli_select_db($conn, $db) or die('Select error: ' . mysqli_error());

$txtout = $txtout . "001" . "\t";
$txtout = $txtout . "024" . "\t";
$txtout = $txtout . "073" . "\t";
$txtout = $txtout . "1102" . "\t";
$txtout = $txtout . "200" . "\t";
$txtout = $txtout . "210" . "\t";
$txtout = $txtout . "2451" . "\t";
$txtout = $txtout . "2461" . "\t";
$txtout = $txtout . "260" . "\t";
$txtout = $txtout . "270" . "\t";
$txtout = $txtout . "307" . "\t";
$txtout = $txtout . "ALT_URL" . "\t";
$txtout = $txtout . "\r\n";
		
$count = 0;

$sqloverride = "
SELECT Distinct
  `Resource`.`metalibID`,
  `Resource`.`titleText`,
  `Resource`.`resourceAltURL`,
  `Resource`.`resourceID`,
  `ResourceFormat`.`shortName` AS `ResourceFormat`,
  `ResourceType`.`shortName` AS `ResourceType`,
  `LicenseStatus`.`shortName` AS `LicenseStatus`,
  `Resource`.`providerText`,
  `Resource`.`resourceURL`,
  `Status`.`shortName` AS `Status`,
  `Resource`.`descriptionText`,
  `Resource`.`descriptionText`,
  `AcquisitionType`.`shortName`
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
  `ResourceType`.`resourceTypeID` = 10 OR 
  `Status`.`statusID` = 4
ORDER BY
  `ResourceID`

";

if ($_GET["debug"]) {
	echo "<br>\r";  
	echo $sqloverride;
	echo "<br>\r";
	echo "<br>\r";
}

$rs = mysqli_query($conn, $sqloverride) or die('mysql error: ' . mysqli_error());
	
	while($row = mysqli_fetch_array( $rs )) {
			if ($count == 500) {
				if ($_GET["debug"]) {
					break;
				}
			} else {
				$count = $count + 1;
			}
	
		if (is_null($row['resourceID'])) {
			$txtout = $txtout . "\t";
		} else {
			$txtout = $txtout . trim($row['resourceID']);
			$txtout = $txtout . "\t"; //"001" **
		}

//		if (is_null($row['metalibID'])) {
//			$txtout = $txtout . "\t";
//		} else {
//			$txtout = $txtout . trim($row['metalibID']);
//			$txtout = $txtout . "\t"; //"001" **
//		}

		$txtout = $txtout . "\t"; //"024"
		$txtout = $txtout . "\t"; //"073"

		$query3 = "
			SELECT DISTINCT 
			  `coral_organizations_prod`.`Organization`.`name`,
			  `ResourceOrganizationLink`.`organizationRoleID`,
			  `ResourceOrganizationLink`.`resourceID`
			FROM
			  `ResourceOrganizationLink`
			  INNER JOIN `coral_organizations_prod`.`Organization` ON (`ResourceOrganizationLink`.`organizationID` = `coral_organizations_prod`.`Organization`.`organizationID`)
			WHERE
			  `ResourceOrganizationLink`.`resourceID` = " . $row["resourceID"] . " AND `ResourceOrganizationLink`.`organizationRoleID` = 5
			ORDER BY 
			  `coral_organizations_prod`.`Organization`.`name`
		";
//		echo $query3;
		$result3 = mysqli_query($conn, $query3) or die("Query 3 Failure");
		$row3 = mysqli_fetch_array( $result3  );
//		echo "<br>";
//		echo "<br>";		
//		echo $row3['name'];
//		echo "<br>";
//		echo "<br>";		
		
			if (is_null($row3['name'])) {
				if (is_null($row['providerText'])) {
					//echo "I have no provider" . $row["resourceID"];
					$txtout = $txtout . "\t";
				} else {
					$txtout = $txtout . trim($row['providerText']);				
					$txtout = $txtout . "\t"; //"1102" **					
				}
				//$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . trim($row3['name']);
				$txtout = $txtout . "\t"; //"1102" **
			}		

		$txtout = $txtout . "\t"; //"200"
		$txtout = $txtout . "\t"; //"210"

			if (is_null($row['titleText'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . trim($row['titleText']);
				$txtout = $txtout . "\t"; //"2451" **
			}		
		
		$txtout = $txtout . "\t"; //"2461" **

			if (is_null($row3['name'])) {
				if (is_null($row['providerText'])) {
					$txtout = $txtout . "\t";
				} else {
					$txtout = $txtout . trim($row['providerText']);				
					$txtout = $txtout . "\t"; //"260" **					
				}
				//$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . trim($row3['name']);
				$txtout = $txtout . "\t"; //"260" **
			}			
	
		$txtout = $txtout . "\t"; //"270"
		$txtout = $txtout . "\t"; //"307"
		$txtout = $txtout . "\t"; //"500"
		$txtout = $txtout . "\t"; //"505"

		$sql2 = "SELECT * FROM ResourceNote where resourceID = " . $row['resourceID'] . " AND noteTypeID = 6"; 
		$rs2 = mysqli_query($conn, $sql2 );
		$row2 = mysqli_fetch_array( $rs2 );
		
			if (is_null($row2['noteText'])) {
				$txtout = $txtout . "\t";
			} else {
				$tmpTxt = preg_replace("/(\r\n|\n|\r|\t)/i", "\n", trim($row2['noteText']));
				$tmpTxt = str_replace("\n", '<BR>', $tmpTxt);
				$tmpTxt = str_replace("\r", '<BR>', $tmpTxt);	
				$tmpTxt = nl2br($tmpTxt);				
				$txtout = $txtout . trim($tmpTxt);				
				$txtout = $txtout . "\t"; //"506" //Access Information
			}






		$txtout = $txtout . "\t"; //"513"
	
			if (is_null($row['descriptionText'])) {
				$txtout = $txtout . "\t";
			} else {
				$tmpTxt = preg_replace("/(\r\n|\n|\r|\t)/i", "\n", trim($row['descriptionText']));
				$tmpTxt = str_replace("\n", '<BR>', $tmpTxt);
				$tmpTxt = nl2br($tmpTxt);					
			
				$txtout = $txtout . trim($tmpTxt);
				$txtout = $txtout . "\t"; //"520" 
			}	
			
		$txtout = $txtout . "\t"; //"531"
		$txtout = $txtout . "\t"; //"540"
		$txtout = $txtout . "\t"; //"545"
		$txtout = $txtout . "\t"; //"546"
		$txtout = $txtout . "\t"; //"561"
		$txtout = $txtout . "\t"; //"590"
		$txtout = $txtout . "\t"; //"591"
		$txtout = $txtout . "\t"; //"592"
		$txtout = $txtout . "\t"; //"593"
		$txtout = $txtout . "\t"; //"594"
		$txtout = $txtout . "\t"; //"595"
		$txtout = $txtout . "\t"; //"650"

		$sql2 = "SELECT * FROM ResourceNote where resourceID = " . $row['resourceID'] . " AND noteTypeID = 7"; 
		$rs2 = mysqli_query($conn, $sql2 );
		$row2 = mysqli_fetch_array( $rs2 );
		
			if (is_null($row2['noteText'])) {
				$txtout = $txtout . "\t";
			} else {
				echo "Keywords";
				$tmpTxt = preg_replace("/(\r\n|\n|\r|\t)/i", "\n", trim($row2['noteText']));
				$tmpTxt = str_replace("\n", '<BR>', $tmpTxt);
				$tmpTxt = nl2br($tmpTxt);		
				$txtout = $txtout . trim($tmpTxt);					
				$txtout = $txtout . "\t";  //"653"
			}
		
			if (is_null($row['ResourceType'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . substr(trim($row['ResourceType']),0, 255);
				$txtout = $txtout . "\t"; //"655"
			}		

		$txtout = $txtout . "\t"; //"7102"
		$txtout = $txtout . "\t"; //"720"
		
			if (is_null($row['resourceURL'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . substr(trim($row['resourceURL']),0, 2000);
				$txtout = $txtout . "\t"; //"85641"
			}		

		$txtout = $txtout . "\t"; //"85642"
		$txtout = $txtout . "\t"; //"85643"
		$txtout = $txtout . "\t"; //"85644"
		$txtout = $txtout . "\t"; //"85645"
		$txtout = $txtout . "\t"; //"85649"
		$txtout = $txtout . "\t"; //"901"
		$txtout = $txtout . "\t"; //"902"
		$txtout = $txtout . "\t"; //"956"


		$txtout = $txtout . "TAMUCS" . "\t"; //"AF1"
		
		
		$txtout = $txtout . "\t"; //"AF3"
		$txtout = $txtout . "\t"; //"AIP"
		
			if (is_null($row['AcquisitionType'])) {
				$txtout = $txtout . "\t";
			} else {
				if (strtolower(trim($row['AcquisitionType'])) == "paid") {
					$txtout = $txtout . "SUBSCRIPTION" ;
				} elseif (strtolower(trim($row['AcquisitionType'])) == "free") {
					$txtout = $txtout . "FREE";
				}
				
				//$txtout = $txtout . trim($row['AcquisitionType']);
				
				$txtout = $txtout . "\t"; //"ATG"
			}			

		$txtout = $txtout . "\t"; //"CKB"
		$txtout = $txtout . "\t"; //"DEL"
		$txtout = $txtout . "\t"; //"FIL"
		$txtout = $txtout . "\t"; //"FMT"
		$txtout = $txtout . "\t"; //"FTL"
		$txtout = $txtout . "\t"; //"Gro"
		$txtout = $txtout . "\t"; //"ICN"
		$txtout = $txtout . "\t"; //"LDR"
		$txtout = $txtout . "\t"; //"LUP"
		$txtout = $txtout . "\t"; //"MTD"
		$txtout = $txtout . "\t"; //"NEW"
		$txtout = $txtout . "\t"; //"NWD"
		$txtout = $txtout . "\t"; //"OPD"
		$txtout = $txtout . "\t"; //"PXY"
		$txtout = $txtout . "\t"; //"RNK"
		$txtout = $txtout . "\t"; //"SES"
		$txtout = $txtout . "\t"; //"SFX"
	
			if (is_null($row['Status'])) {
				$txtout = $txtout . "\t";
			} else {
				if ((strtolower(trim($row['Status'])) == "in progress") || (strtolower(trim($row['Status'])) == "completed")) {
					$txtout = $txtout . "ACTIVE" ;
				} elseif (strtolower(trim($row['Status'])) == "archived") {
					$txtout = $txtout . "INACTIVE";
				}
				
				//$txtout = $txtout . trim($row['Status']);
				
				$txtout = $txtout . "\t"; //"STA"
			}			
		
		$txtout = $txtout . "\t"; //"TAR"
		$txtout = $txtout . "\t"; //"UPD"
		$txtout = $txtout . "\t"; //"VER"
		$txtout = $txtout . "\t"; //"WCNV"
		$txtout = $txtout . "\t"; //"WGEN"
		$txtout = $txtout . "\t"; //"WHTP"
		$txtout = $txtout . "\t"; //"WSFX"
		$txtout = $txtout . "\t"; //"WTRM"
		$txtout = $txtout . "\t"; //"WZ39"
		$txtout = $txtout . "\t"; //"Z58-ACCESS-METHOD"
		$txtout = $txtout . "\t"; //"Z58-ACTIVE"
		$txtout = $txtout . "\t"; //"Z58-BASE"
		$txtout = $txtout . "\t"; //"Z58-CATALOGER"
		$txtout = $txtout . "\t"; //"Z58-IN-RECORD-CHAR-CONV"
		$txtout = $txtout . "\t"; //"Z58-IN-RECORD-TYPE"
		$txtout = $txtout . "\t"; //"Z58-SORT"
		$txtout = $txtout . "\t"; //"Z58-STATUS"
		$txtout = $txtout . "\t"; //"ZAT"
		$txtout = $txtout . "\t"; //"ZDC"
		$txtout = $txtout . "\t"; //"ZHS"
		$txtout = $txtout . "\t"; //"TARSS"

			if (is_null($row['resourceAltURL'])) {
				$txtout = $txtout . "\t";
			} else {
				$txtout = $txtout . trim($row['resourceAltURL']);
				$txtout = $txtout . "\t"; //"85641"
			}	
		
		$txtout = $txtout . "\r\n";
	}

	header("Content-Type: text/plain");
	header('Content-Disposition: attachment; filename=coral_extract.txt');
	echo $txtout;
	
?>