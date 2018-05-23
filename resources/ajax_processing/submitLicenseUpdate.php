<?php
$resourceAcquisition = new ResourceAcquisition(new NamedArguments(array('primaryKey' => $_POST['resourceAcquisitionID'])));
$resourceAcquisition->processLicense(explode(':::',$_POST['licenseList']), $_POST['licenseStatusID'], $loginID);
?>
