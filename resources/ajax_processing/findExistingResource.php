<?php
$resourceObject = new Resource();
if ($existingResourceID = $resourceObject->findByPurchaseOrder($_GET['externalId'])) {
	$resource = new Resource(new NamedArguments(array('primaryKey' => $existingResourceID)));
	echo json_encode(array("resourceID"=>$resource->resourceID,"titleText"=>$resource->titleText));
} else {
	echo json_encode(array("result"=>"No Existing Resource found"));
}
?>