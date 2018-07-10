<?php

$config = new Configuration();

/*
if $externalId is defined and an ExternalResource implementation is configured, we will:
	1) Try to retrieve the remote resource data by $externalId
	2) Create and save a new CORAL Resource using the remote resource data
	3) Set the newly created CORAL Resource to be edited
*/

$externalId = !empty($_POST['externalId']) ? $_POST['externalId']:null;
$forceDuplicate = !empty($_POST['forceDuplicate']) ? $_POST['forceDuplicate']:null;

$fromExternal = ($externalId && class_exists($config->settings->externalResourceRepoClass));
$outputJson = $fromExternal ? true:false;

$resourceID = null;
$createMode = null;
if (!$fromExternal) {
	$resourceID = $_POST['resourceID'];
	$resourceAcquisitionID = $_POST['resourceAcquisitionID'];
	$createMode = $_POST['createMode'];
}

if ($resourceID && $createMode != 'clone') {
	//get this resource
	$resource = new Resource(new NamedArguments(array('primaryKey' => $resourceID)));
} else {
	//set up new resource
	$resource = new Resource();
	$resource->createLoginID 		= $loginID;
	$resource->createDate			= date( 'Y-m-d' );
	$resource->updateLoginID 		= '';
	$resource->updateDate			= '';

	//get the Resource Object we'll use for cloning data from later
	if ($resourceID && $createMode == 'clone') {
		$oldResourceAcquisition = new ResourceAcquisition(new NamedArguments(array('primaryKey' => $resourceAcquisitionID)));
	}	
}

if (!$fromExternal) {
	//determine status id
	$status = new Status();
	$statusID = $status->getIDFromName($_POST['resourceStatus']);

	$resource->resourceTypeID 		= $_POST['resourceTypeID'];
	$resource->resourceFormatID 	= $_POST['resourceFormatID'];
	$resource->titleText 			= $_POST['titleText'];
	$resource->descriptionText 		= $_POST['descriptionText'];
	$resource->isbnOrISSN	 		= [];
	$resource->statusID		 		= $statusID;
	$resource->orderNumber	 		= '';
	$resource->systemNumber 		= '';
	$resource->userLimitID	 		= '';
	$resource->authenticationUserName 	= '';
	$resource->authenticationPassword 	= '';
	$resource->storageLocationID		= '';
	$resource->registeredIPAddresses 	= '';
	$resource->providerText			 	= $_POST['providerText'];
	$resource->coverageText 			= '';

	if ($_POST['resourceURL'] != 'http://'){
		$resource->resourceURL = $_POST['resourceURL'];
	}else{
		$resource->resourceURL = '';
	}

	if ($_POST['resourceAltURL'] != 'http://'){
		$resource->resourceAltURL = $_POST['resourceAltURL'];
	}else{
		$resource->resourceAltURL = '';
	}
	try {
		$resource->save();
	} catch (Exception $e) {
		error_log($e->getMessage());
	}
}

try {
	if ($fromExternal) {
		$remoteResourceRepo = new $config->settings->externalResourceRepoClass($externalId);
		//$resourceData is the JSON response for New Resource records originating from an External API
		$resourceData = array();
		//if we don't at least have a title, give up
		if (!$remoteResourceRepo->getResourceObject() || !$remoteResourceRepo->getResourceObject()->getTitleText()) {
			$resourceData = array("error"=>"Unable to retrieve the External Resource");
		} elseif (!$forceDuplicate && $resource->getResourceByTitle($remoteResourceRepo->getResourceObject()->getTitleText())) {
			$resourceData = array("error"=>"Title Exists: {$remoteResourceRepo->getResourceObject()->getTitleText()}","isDuplicate"=>1);
		} else {

			$resource->resourceTypeID = 2;
			$resource->resourceFormatID = 2;
			$resource->acquisitionTypeID = 1;

			$status = new Status();
			$resource->statusID = $status->getIDFromName('progress');

			$resource->setTitleText($remoteResourceRepo->getResourceObject()->getTitleText());

			$addableIsbnOrIssns = array();
			foreach ($remoteResourceRepo->getIsbnOrIssnObjects() as $isbnOrIssnObject) {
				$addableIsbnOrIssns[] = $isbnOrIssnObject->getIsbnOrIssn();
			}
			$resource->setIsbnOrIssn($addableIsbnOrIssns);

			try {
				$resource->save();
			} catch (Exception $e) {
				error_log($e->getMessage());
			}

			$resourceAcquisition = new ResourceAcquisition();
			$resourceAcquisition->resourceID = $resource->primaryKey;
			$resourceAcquisition->acquisitionTypeID = $resource->acquisitionTypeID;
			$resourceAcquisition->subscriptionStartDate = date("Y-m-d");
			$resourceAcquisition->subscriptionEndDate = date("Y-m-d");
			$resourceAcquisition->save();

			$fund = new Fund();
			foreach ($remoteResourceRepo->getResourcePaymentObjects() as $remoteResourcePayment) {
				$fundID = 0;
				if ($remoteResourcePayment->getFundCode()) {
					$fundPrefix = substr($remoteResourcePayment->getFundCode(),0,3);
					$fundSpecial = substr($remoteResourcePayment->getFundCode(),3,1);
					
		 			if ($fundCandidate = $fund->getByFundCode($fundPrefix)) {
						$fundID = $fundCandidate['fundID'];
						if (empty($organizationID)) {
							$organizationID = !empty($fundCandidate['organizationID']) ? $fundCandidate['organizationID']:null;
						}
					} else {
						$fund->fundCode = $fundPrefix;
						$fund->save();
						$fundID = $fund->primaryKey;
					}
				}
				$resourcePayment = new ResourcePayment();
				$resourcePayment->year          = date("Y");
				$resourcePayment->fundID      	= $fundID;
				$resourcePayment->fundSpecial      	= $fundSpecial;
				$resourcePayment->purchaseOrder = $remoteResourcePayment->getPurchaseOrder();
				$resourcePayment->systemID      = $remoteResourcePayment->getSystemID();
				$resourcePayment->vendorCode 	= $remoteResourcePayment->getVendorCode();
				$resourcePayment->paymentAmount = 0;
				$resourcePayment->currencyCode  = $config->settings->defaultCurrency;
				$resourcePayment->orderTypeID   = 1;
				$resourcePayment->resourceAcquisitionID = $resourceAcquisition->primaryKey;
				$paymentYear = date("Y");
				if (date("Y-m-d") > $currentYear.'-07-01') {
					$paymentYear++;
				}
				$resourcePayment->subscriptionStartDate	= "{$paymentYear}-01-01";
				
				try {
					$resourcePayment->save();
				} catch (Exception $e) {
					error_log($e->getMessage());
				}
			}
			$resourceData = array("resourceID"=>$resource->primaryKey);
		}
	} else {
		$organizationID = !empty($_POST['organizationID']) ? $_POST['organizationID']:null;
	}

	if ($outputJson) {
		echo json_encode($resourceData);
		if (!empty($resourceData['error'])) {
			exit;
		}
	} else {
		echo $resource->primaryKey;
	}

	$resourceID=$resource->primaryKey;

	if (!$fromExternal) {
		// Create the default order
		//first, remove existing order in case this was saved before
		$resource->removeResourceAcquisitions();

		$resourceAcquisition = new ResourceAcquisition();
		$resourceAcquisition->resourceID = $resourceID;
		$resourceAcquisition->acquisitionTypeID = $_POST['acquisitionTypeID'];
		$resourceAcquisition->subscriptionStartDate = date("Y-m-d");
		$resourceAcquisition->subscriptionEndDate = date("Y-m-d");
		$resourceAcquisition->save();
	}

			if ($createMode == 'clone' && $oldResourceAcquisition) {
				$licenseIds = array();
				foreach ($oldResourceAcquisition->getLicenseArray() as $license) {
					$licenseIds[] = $license['licenseID'];
				}
				$resourceAcquisition->processLicense($licenseIds);
			}

			//get the provider ID in case we insert what was entered in the provider text box as an organization link
			$organizationRole = new OrganizationRole();
			$organizationRoleID = $organizationRole->getProviderID();

			//add notes
			if (($_POST['noteText']) || (($_POST['providerText']) && (!$_POST['organizationID']))){
				//first, remove existing notes in case this was saved before
				$resource->removeResourceNotes();

				//this is just to figure out what the creator entered note type ID is
				$noteType = new NoteType();

				$resourceNote = new ResourceNote();
				$resourceNote->resourceNoteID 	= '';
				$resourceNote->updateLoginID 	= $loginID;
				$resourceNote->updateDate		= date( 'Y-m-d' );
				$resourceNote->noteTypeID 		= $noteType->getInitialNoteTypeID();
				$resourceNote->tabName 			= 'Product';
				$resourceNote->entityID 		= $resourceID;

				//only insert provider as note if it's been submitted
				if (($_POST['providerText']) && (!$_POST['organizationID']) && ($_POST['resourceStatus'] == 'progress')){
					$resourceNote->noteText 	= "Provider:  " . $_POST['providerText'] . "\n\n" . $_POST['noteText'];
				}else{
					$resourceNote->noteText 	= $_POST['noteText'];
				}

				$resourceNote->save();
			}

	if ($organizationID) {
		if ($fromExternal) {
			$organizationRoleID = 5;
		} else {
			//get the provider ID in case we insert what was entered in the provider text box as an organization link
			$organizationRole = new OrganizationRole();
			$organizationRoleID = $organizationRole->getProviderID();

			//first remove the organizations if this is a saved request
			$resource->removeResourceOrganizations();
		}

		if ($organizationRoleID) {
			$resourceOrganizationLink = new ResourceOrganizationLink();
			$resourceOrganizationLink->resourceID = $resourceID;
			$resourceOrganizationLink->organizationID = $organizationID;
			$resourceOrganizationLink->organizationRoleID = $organizationRoleID;
			$resourceOrganizationLink->save();
		}
	}

	if (!$fromExternal) {
		$yearArray          = array();  $yearArray          = explode(':::',$_POST['years']);
		$subStartArray      = array();  $subStartArray      = explode(':::',$_POST['subStarts']);
		$subEndArray        = array();  $subEndArray        = explode(':::',$_POST['subEnds']);
		$fundIDArray        = array();  $fundIDArray        = explode(':::',$_POST['fundIDs']);
		$paymentAmountArray = array();  $paymentAmountArray = explode(':::',$_POST['paymentAmounts']);
		$currencyCodeArray  = array();  $currencyCodeArray  = explode(':::',$_POST['currencyCodes']);
		$orderTypeArray     = array();  $orderTypeArray     = explode(':::',$_POST['orderTypes']);
		$costDetailsArray   = array();  $costDetailsArray   = explode(':::',$_POST['costDetails']);
		$costNoteArray      = array();  $costNoteArray      = explode(':::',$_POST['costNotes']);
		$invoiceArray       = array();  $invoiceArray       = explode(':::',$_POST['invoices']);

            // Is this really needed ?
/*
		//first remove all payment records, then we'll add them back
		$resource->removeResourcePayments();

		foreach ($orderTypeArray as $key => $value){
			if (($value) && ($paymentAmountArray[$key] || $yearArray[$key] || $fundIDArray[$key] || $costNoteArray[$key])){
				$resourcePayment = new ResourcePayment();
				$resourcePayment->resourceID    = $resourceID;
				$resourcePayment->year          = $yearArray[$key];
				$resourcePayment->subscriptionStartDate = $subStartArray[$key];
				$resourcePayment->subscriptionEndDate   = $subEndArray[$key];
				$resourcePayment->fundID      = $fundIDArray[$key];
				$resourcePayment->paymentAmount = cost_to_integer($paymentAmountArray[$key]);
				$resourcePayment->currencyCode  = $currencyCodeArray[$key];
				$resourcePayment->orderTypeID   = $value;
				$resourcePayment->costDetails   = $costDetailsArray[$key];
				$resourcePayment->costNote      = $costNoteArray[$key];
				$resourcePayment->invoice       = $invoiceArray[$key];
				try {
					$resourcePayment->save();
				} catch (Exception $e) {
					echo $e->getMessage();
				}
			}
		}
*/
	}

	//next if the resource was submitted, enter into workflow
	if ($statusID == $status->getIDFromName('progress')){
		$resource->enterNewWorkflow();
	}

} catch (Exception $e) {
	echo $e->getMessage();
}


?>