<?php
		$className = $_POST['className'];
		$updateID = $_POST['updateID'];
		$shortName = $_POST['shortName'];
		if($className=="ResourceType"){
			$includeStats = ($_POST['stats'] == 'true') ? 1:0;
			$hideArchived = ($_POST['hideArchived'] == 'true') ? 1:0;
		}

		if ($updateID != ''){
			$instance = new $className(new NamedArguments(array('primaryKey' => $updateID)));
		}else{
			$instance = new $className();
		}

		$instance->shortName = $shortName;
		if($className == "ResourceType"){
			$instance->includeStats = $includeStats;
			$instance->hideArchived = $hideArchived;
		}


		try {
			$instance->save();
		} catch (Exception $e) {
			echo $e->getMessage();
		}

?>
