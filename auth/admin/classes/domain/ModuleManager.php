<?php

/* A class that provides the CORAL module names and metadata and automates user account creation across modules - Jason Savell
*/
class ModuleManager {
	private $authModuleDB;
	private $moduleNames;
	private $moduleDBs;
	private $modulePrivileges;
	private $db;
	private $enabledModules;

	function __construct() {
		$this->db = DBService::getInstance();
		$config = new Configuration();

		$this->setAuthModuleDB($config->database->name);

		$this->enabledModules = $config->getEnabledModules();

		//only add the modules we want to manage
		$this->setModuleNames(array_diff($this->enabledModules,array("auth","reports")));

		foreach ($config->getModuleConfigurations() as $moduleName=>$details) {
			$this->setModuleDB($moduleName,$details['database']['name']);
		}

		$this->buildModulePrivileges();

	}

	//build an array of module configurations
	private function buildModulePrivileges() {
		foreach ($this->getModuleNames() as $module) {
			$this->modulePrivileges[$module] = $this->getPrivilege($module);
		}
	}

	//get the privileges from the DB for the given module
	private function getPrivilege($module) {
		//exclude admin privilegeID
		$sql = "SELECT * FROM `{$this->moduleDBs[$module]}`.`Privilege` ORDER BY `privilegeID`";
		if ($result = $this->db->processQuery($sql,'assoc')) {
			return $result;
		}
		return false;
	}

	private function setAuthModuleDB($db) {
		$this->authModuleDB = $db;
	}

	private function setModuleDB($name,$db) {
		$this->moduleDBs[$name] = $db;
	}

	public function getModuleNames() {
		return $this->moduleNames;
	}

	public function setModuleNames($names) {
		$this->moduleNames = $names;
	}

	public function getModulePrivileges() {
		return $this->modulePrivileges;
	}

	//add new user to each requested module
	public function processRequest($data) {
		//loop through module names
		foreach ($data['modules'] as $module) {
			if (in_array($module,$this->moduleNames)) {
				//insert into user table for the current module
				$sql = "INSERT INTO `{$this->moduleDBs[$module]}`.`User` SET ";
				foreach ($data['userdata'] as $field=>$val) {
					$sql .= "`{$field}`='".mysql_real_escape_string($val)."',";
				}
				$sql = rtrim($sql,',');
				if ($data['modulePrivilege'][$module]) {
					$sql .= ",`privilegeID`=".mysql_real_escape_string($data['modulePrivilege'][$module]);
				}
				if ($module == 'resources') {
					$sql .= ",`emailAddress`='".mysql_real_escape_string($data['extras']['email'])."'";
				}
				if (!mysql_query($sql)) {
					$error[] = $module;
				}
			}
		}
		if (!$error) {
			return true;
		}
		return false;
	}

	//boolean check for username availability
	function userExists($loginID) {
		$x = 1;
		foreach ($this->enabledModules as $module) {
			if ($module != 'reports') {
				$meta['fields'] .= " l{$x}.`loginID`,";
				$meta['tables'] .= " `{$this->moduleDBs[$module]}`.`User` l{$x},";
				$meta['params'] .= " l{$x}.`loginID`,";
				$x++;
			}
		}
		foreach ($meta as $field=>$val) {
			$meta[$field] = rtrim($val,',');
		}

		$sql = "SELECT {$meta['fields']}
					FROM {$meta['tables']}
					WHERE '".$this->db->escapeString($loginID)."' IN ({$meta['params']}) LIMIT 0,1";
		$result = $this->db->processQuery($sql);
		if (is_array($result) && count($result) > 0) {
			return true;
		}
		return false;
	}

	public function getUserPrivileges($loginID) {
		$privileges = array();
		foreach ($this->getModuleNames() as $moduleName) {
			$sql = "SELECT privilegeID
				FROM `{$this->moduleDBs[$moduleName]}`.`User`
				WHERE loginID='".$this->db->escapeString($loginID)."'";
			$result = $this->db->processQuery($sql,'assoc');
			$privileges[$moduleName] = $result['privilegeID'];
		}
		return $privileges;
	}
}