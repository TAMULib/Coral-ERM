<?php
include_once 'directory.php';

$resourceObject = new Resource();

try {
	$resourceObject->archiveExpiredResources();
} catch (RuntimeException $e) {
	error_log("Error auto-archiving expired Resources of auto-archiveable types");
}
?>