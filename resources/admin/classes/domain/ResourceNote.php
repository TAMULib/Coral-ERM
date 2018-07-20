<?php

/*
**************************************************************************************************************************
** CORAL Resources Module v. 1.0
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

class ResourceNote extends DatabaseObject {

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}

  public function getNotesByEntityIds($entityIDs, $noteTypeID = null) {
    $query = "SELECT * FROM ResourceNote WHERE entityID IN (".implode(",",$entityIDs).")";
    if ($noteTypeID && is_numeric($noteTypeID)) {
      $query .= " AND noteTypeID={$noteTypeID}";
    }

    $result = $this->db->processQuery($query, 'assoc');
    //need to do this since it could be that there's only one result and this is how the dbservice returns result
    $notesArray = [];
    if (isset($result['resourceNoteID'])) { $result = [$result]; }

    foreach ($result as $row) {
      array_push($notesArray, $row);
    }
    return $notesArray;
  }
}

?>
