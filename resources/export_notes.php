<?php

/*
**************************************************************************************************************************
** CORAL Resources Module v. 1.2
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/


include_once 'directory.php';

function escape_csv($value) {
  // replace \n with \r\n
  $value = preg_replace("/(?<!\r)\n/", "\r\n", $value);
  // escape quotes
  $value = str_replace('"', '""', $value);
  return '"'.$value.'"';
}

function array_to_csv_row($array) {
  $escaped_array = array_map("escape_csv", $array);
  return implode(",",$escaped_array)."\r\n";
}

if (empty($_GET['exportDetails'])) {
  $noteTypeObject = new NoteType();
  $noteTypes = $noteTypeObject->allAsArrayForDD();
?>
<form name="exportDetailsForm" method="GET">
  <select name="exportDetails[noteTypeID]">
<?php
  foreach ($noteTypes as $noteType) {
    echo "<option value=\"{$noteType['noteTypeID']}\">{$noteType['shortName']}</option>";
  }
?>
  </select>
  <input type="submit" name="submitDetails" value="Export Notes" />
</form>
<?php
} else {

  $replace = array("/", "-");
  $excelfile = "notes_export_" . str_replace( $replace, "_", format_date( date( 'Y-m-d' ) ) ).".csv";

  header("Pragma: public");
  header("Content-type: text/csv");
  header("Content-Disposition: attachment; filename=\"" . $excelfile . "\"");

  $columnHeaders = array(
    _("Note ID"),
    _("Resource Name"),
    _("Entity ID"),
    _("Note Type"),
    _("Update Date"),
    _("Update LoginID"),
    _("Note Text")
  );

  $queryDetails = Resource::getSearchDetails();
  $whereAdd = $queryDetails["where"];
  $searchDisplay = $queryDetails["display"];
  $orderBy = $queryDetails["order"];

  $noteTypeID = is_numeric($_GET['exportDetails']['noteTypeID']) ? $_GET['exportDetails']['noteTypeID']:null;

  if ($noteTypeID) {

    //get the results of the query into an array
    $resourceObj = new Resource();
    $resourceArray = array();
    $resourceArray = $resourceObj->export($whereAdd, $orderBy);

    $resourceIDs = array();
    foreach ($resourceArray as $resource) {
      $resourceIDs[] = $resource['resourceID'];
    }

    $resourceNote = new ResourceNote();

    $exportableNotes = $resourceNote->getNotesByEntityIds($resourceIDs,$noteTypeID);

    echo array_to_csv_row(array(_("Notes Export") . " " . format_date( date( 'Y-m-d' ))));
    if (!$searchDisplay) {
      $searchDisplay = array(_("All Resource Notes"));
    }
    echo array_to_csv_row(array(implode('; ', $searchDisplay)));
    echo array_to_csv_row($columnHeaders);

    foreach ($exportableNotes as $note) {
      $noteValues = array(
        $note['resourceNoteID'],
        $note['titleText'],
        $note['entityID'],
        $note['noteTypeID'],
        format_date($note['updateDate']),
        $note['updateLoginID'],
        $note['noteText']);

      echo array_to_csv_row($noteValues);
    }
  } else {
    echo 'Note Type was not selected.';
  }
}
?>
