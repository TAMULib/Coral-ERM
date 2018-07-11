<?php

$resourceGroups = array();

$resourceGroups["current"] = $user->getOutstandingTasks("current");
$resourceGroups["future"] = $user->getOutstandingTasks("future");

foreach ($resourceGroups as $type=>$resourceGroup) {
	echo "<div class=\"task-group\">
			<h3 class=\"capitalize\">"._("To Do {$type} Tasks")."</h3>";
	if (count($resourceGroup) == 0){
		echo "<i>"._("No outstanding requests")."</i>";
	} else {
?>

			<table class='dataTable' id="outstandingTasks">
			<tr>
				<th><?php echo _("ID");?></th>
				<th><?php echo _("Name");?></th>
        <th><?php echo _("Order");?></th>
				<th><?php echo _("Acquisition Type");?></th>
        <th><?php echo _("Workflow Step");?></th>
				<th class="sortable"><?php echo _("Fund");?></th>
				<th><?php echo _("Start Date");?></th>
				<th><?php echo _("Last Reviewed");?></th>
				<th><?php echo _("Notes");?></th>
				<th><?php echo _("Reviewed");?></th>
				<th><?php echo _("Completed");?></th>
			</tr>

<?php
		$i=0;
		foreach ($resourceGroup as $resource){
			$taskArray = $user->getOutstandingTasksByResource($resource['resourceID']);
			$countTasks = count($taskArray);

			//for shading every other row
			$i++;
			if ($i % 2 == 0){
				$classAdd="";
			} else {
				$classAdd="class='alt'";
			}

			$acquisitionType = new AcquisitionType(new NamedArguments(array('primaryKey' => $resource['acquisitionTypeID'])));
			$status = new Status(new NamedArguments(array('primaryKey' => $resource['statusID'])));

?>
        <tr id='tr_<?php echo $resource['resourceID']; ?>' style='padding:0px;margin:0px;height:100%;'>
          <td <?php echo $classAdd; ?>><a href='resource.php?resourceID=<?php echo $resource['resourceID']; ?>&resourceAcquisitionID=<?php echo $resource['resourceAcquisitionID']?>'><?php echo $resource['resourceID']; ?></a></td>
          <td <?php echo $classAdd; ?>><a href='resource.php?resourceID=<?php echo $resource['resourceID']; ?>&resourceAcquisitionID=<?php echo $resource['resourceAcquisitionID']?>'><?php echo $resource['titleText']; ?></a></td>
          <td <?php echo $classAdd; ?>><?php echo $resource['subscriptionStartDate']; ?> - <?php echo $resource['subscriptionEndDate']; ?></a></td>
          <td <?php echo $classAdd; ?>><?php echo $acquisitionType->shortName; ?></td>

          <?php
            $j=0;


            if (count($taskArray) > 0){
              foreach ($taskArray as $task){
                if ($j > 0){
                ?>
                <tr>
                <td <?php echo $classAdd; ?> style='border-top-style:none;'>&nbsp;</td>
                <td <?php echo $classAdd; ?> style='border-top-style:none;'>&nbsp;</td>
                <td <?php echo $classAdd; ?> style='border-top-style:none;'>&nbsp;</td>
                <td <?php echo $classAdd; ?> style='border-top-style:none;'>&nbsp;</td>


<?php
						$styleAdd=" style='border-top-style:none;'";
					} else {
						$styleAdd="";
					}

					echo "
				<td " . $classAdd . " " . $styleAdd . ">" . $task['stepName'] . "</td>
				<td " . $classAdd . " " . $styleAdd . ">" . $task['fundCode'] . "</td>
				<td " . $classAdd . " " . $styleAdd . ">" . format_date($task['startDate']) ." <a href=\"ajax_forms.php?action=getResourceStepForm&amp;resourceStepID={$task['resourceStepID']}&amp;height=250&amp;width=750&amp;modal=true\" class=\"thickbox\"><img src=\"images/edit.gif\" alt=\"edit\" title=\"edit\"></a></td>
				<td " . $classAdd . " " . $styleAdd . ">" .(($task['reviewDate']) ? format_date($task['reviewDate']):_('not reviewed'))."</td>
				<td " . $classAdd . " " . $styleAdd . "><a class=\"thickbox\" href=\"ajax_forms.php?action=getNoteForm&height=500&width=410&tab=Product&resourceID={$resource['resourceID']}&resourceNoteID=&modal=true&shownotes=1\">"._("view")."</a></td>
				<td " . $classAdd . " " . $styleAdd . ">";

					if (($user->isAdmin || $user->isInGroup($task['userGroupID']))) {
						echo "<div><a href=\"{$task['resourceStepID']}\" class=\"mark-reviewed\" id=\"task_{$task['resourceStepID']}\">"._("yes")."</a></div>";
					}

					if ($task['reviewDate']) {
						$eUser = new User(new NamedArguments(array('primaryKey' => $task['reviewLoginID'])));
						if (($eUser->firstName) || ($eUser->lastName)){
							echo format_date($task['reviewDate']) . _(" by ") . $eUser->firstName . " " . $eUser->lastName;
						}
					}
echo "			</td>
				<td " . $classAdd . " " . $styleAdd . ">";
					if (!empty($task['stepEndDate'])) {
						$eUser = new User(new NamedArguments(array('primaryKey' => $resource['endLoginID'])));
						if (($eUser->firstName) || ($eUser->lastName)){
							echo format_date($resourceStep->stepEndDate) . _(" by ") . $eUser->firstName . " " . $eUser->lastName;
						}else{
							echo format_date($task['stepEndDate']) . _(" by ") . $task['endLoginID'];
						}
					} else {
						if (($user->isAdmin || $user->isInGroup($task['userGroupID'])) && $task['stepStartDate']) {
							echo "<a href=\"{$task['resourceStepID']}\" class=\"mark-complete\" id=\"task_{$task['resourceStepID']}\">"._("yes")."</a>";
						}
					}
echo "			</td>
			</tr>";
					$j++;
				}
			} else {
				echo "
				<td " . $classAdd . ">&nbsp;</td>
				<td " . $classAdd . ">&nbsp;</td>
				<td " . $classAdd . ">&nbsp;</td>
			</tr>";
			}
		}
		echo "
		</table>";
	}
	echo '
	</div>';
}
?>

