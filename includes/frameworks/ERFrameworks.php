<?php
interface ERFramework {
	function getID();
	function getName();
	function getDescription();
	function match(SMWEnrichJob $job); // return: MatchingResult
	function getProgress();
	function cancel();
	function createResultObject(SMWEnrichJob $job); // factory method for MatchingResult instances
}
abstract class ERFrameworkBase implements ERFramework {
}
class ERFrameworkRegistry {
	protected static $instance;
	protected $frameworks;
	private function __construct() {
		$frameworks = array();
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new ERFrameworkRegistry();
		}
		return self::$instance;
	} 
	public function addFramework(ERFramework $framework) {
		$frameworks[] = array(
			'id' => $framework->getID(),
			'instance' => $framework);
	}
	public function getFrameworks() {
		return $this->frameworks;
	}
	public function getDefaultFramework() {
		return new ERFrameworkLIMES();
	}
}
?>
