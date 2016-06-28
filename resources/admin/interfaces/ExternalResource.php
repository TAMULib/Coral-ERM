<?php
interface ExternalResource {
	/* An array mapping CORAL Resource properties to a corresponding publicly accessible method of the ExternalResource 
	* Implementers can choose which CORAL properties they want to prepopulate.
	*/
	public function getCoralMapping();
	
}
?>