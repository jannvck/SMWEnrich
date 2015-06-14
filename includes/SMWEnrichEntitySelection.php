<?php
class SMWEnrichEntitySelection {
	protected $id;
	protected $name;
	protected $description;
	protected $entities;
	public function __construct($id, $name, $description) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
	}
	public function getID() {
		return $this->id;
	}
	public function setID($id) {
		$this->id = $id;
	}
	public function getName() {
		return $this->name;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getDescription() {
		return $this->description;
	}
	public function setDescription($description) {
		$this->description = $description;
	}
	public function getEntities() { // TODO: paging
		$db = SMWEnrichEntitySelectionDB::getInstance();
		$result = $db->listEntities("selection_id = " . $this->getID());
		$entities = array();
		foreach($result as $entity) {
			$entities[] = $entity->entity_id;
		}
		return $entities;
	}
	public function addEntity($entityId) {
		$this->entities[] = $entityId;
		$db = new SMWEnrichEntitySelectionDB();
		$db->addEntity($this->id, entityId);
	}
	public function exportTo($baseDirectory) {
		$exporter = new SMWExportToFileController(new SMWRDFXMLSerializer());
		$ioUtil = IOUtil::getInstance();
		$ioUtil->mkdir($baseDirectory);
		$path = $baseDirectory . "/" . $this->getExportFileName();
		$file = fopen($path, "wb");
		if($file == false) {
			throw new RuntimeException("Could not open file " . $path . " for writing");
		}
		$exporter->printPagesToFile($file, $this->getEntities());
		fclose($file);
		return $path;
	}
	public function hasExportIn($baseDirectory) {
		return file_exists($baseDirectory . "/" . $this->getExportFileName());
	}
	public function getExportFileName() {
		return "export-" . $this->getID() . ".rdf";
	}
}
class SMWEnrichEntitySelectionDB {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function addSelection($name, $description) {
		$selectionId = mt_rand(); // TODO check for duplicate
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_entity_selections_meta', // table
			array( // data
				'selection_id' => $selectionId,
				'selection_name' => $name,
				'selection_description' => $description),
			__METHOD__,
			array());
		return $selectionId;
	}
	public function removeSelection($selectionId) {
		$db = wfGetDB( DB_SLAVE );
		$this->removeAllEntities($selectionId);
		return $db->delete(
			'smw_enrich_entity_selections_meta', // table
			array('selection_id = ' . $selectionId), // conditions
			__METHOD__);
	}
	public function getSelectionById($selectionId) {
		$selections = $this->listSelections('selection_id = ' . $selectionId);
		if(isset($selections)) {
			return $selections[0];
		}
		return false;
	}
	public function listSelections($contitions) {
		// FIXME:
		// parameters not passed to functions, sometimes!
		// Maybe there is a typo somewhere...
		// Thus another function to retrieve arguments of this function.
		$args = func_get_args();
		$conditions = $args[0];
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_entity_selections_meta', // table
			array('selection_id', 'selection_name', 'selection_description'), // columns
			$conditions,
			__METHOD__,
			array()); // options
		$selections = array();
		foreach($result as $selection) {
			$selections[] = new SMWEnrichEntitySelection(
				$selection->selection_id,
				$selection->selection_name,
				$selection->selection_description);
		}
		return $selections;
	}
	public function addEntity($selectionId, $entityId) {
		$jobId = mt_rand(); // TODO check for duplicate
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_entity_selections', // table
			array( // data
				'entity_id' => $entityId,
				'selection_id' => $selectionId),
			__METHOD__,
			array());
		return $jobId;
	}
	public function removeEntity($selectionId, $entityId) {
		return $this->removeEntities(
			array('entity_id' => $entityId,
				'selection_id' => $selectionId));
	}
	public function removeAllEntities($selectionId) {
		return $this->removeEntities(
			array('selection_id = ' . $selectionId));
	}
	protected function removeEntities(Array $conditions) {
		$db = wfGetDB( DB_SLAVE );
		return $db->delete(
			'smw_enrich_entity_selections', // table
			$conditions,
			__METHOD__);
	}
	public function listEntities($conditions, $options = array()) { // TODO pages
		$db = wfGetDB( DB_SLAVE );
		return $db->select(
			'smw_enrich_entity_selections', // table
			array('entity_id', 'selection_id'), // columns
			$conditions,
			__METHOD__,
			$options);
	}
}
class SMWEnrichEntitySelectionManager {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function export(SMWEnrichEntitySelection $dataSource, $path) {
		$title = Title::newFromText(
         //username . '/' .
         'smwenrich/' .
         'exportSelection/' .
         uniqid(),
         NS_USER);
    $job = new SMWEnrichEntitySelectionExportMultipleJob($title, array(
    	// selection_id is primary table key, so it's okay to just refer
    	// to the first element, as there will only be one with this id.
    	'entitySelection' => $results[0],
    	'path' => $path));
    JobQueueGroup::singleton()->push($job);
    return $title;
  }
}
?>
