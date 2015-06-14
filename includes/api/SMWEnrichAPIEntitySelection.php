<?php
class SMWEnrichEntitySelectionAPI extends ApiBase {
	public function execute() {
		// get request parameters
		$apiMain = $this->getMain();
		$paramSelection = $apiMain->getVal('selection');
		$paramAdd = $apiMain->getVal('add');
		$paramRemove = $apiMain->getVal('remove');
		$paramList = $apiMain->getVal('list');
		$paramExport = $apiMain->getVal('export');
		$paramName = $apiMain->getVal('name'); // entity URI
		$paramDescription = $apiMain->getVal('description');
		// main logic
		if($paramAdd==true && empty($paramSelection) && isset($paramName) && isset($paramDescription)) {
			$this->addSelection($paramName, $paramDescription);
		} else if($paramRemove==true && isset($paramSelection) && empty($paramName)) {
			$this->removeSelection($paramSelection);
		} else if($paramAdd==true && isset($paramSelection) && isset($paramName)) {
			$this->addEntity($paramSelection, $paramName);
		} else if($paramRemove==true && isset($paramSelection) && isset($paramName)) {
			$this->removeEntity($paramSelection, $paramName);
		} else if($paramList==true && isset($paramSelection)) { // list entities of a certain selection
			$this->listEntities($paramSelection);
		} else if($paramList==true) {
			$this->listSelections();
		} else if($paramExport==true && isset($paramSelection)) {
			$this->startExport($paramSelection);
		} else {
			$this->getResult()->addValue(null, 'result', array('operation' => 'FAIL'));
		}
		return true;
	}
	protected function addSelection($name, $description) {
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		// MediaWiki takes care of proper escaping, so it should be ok to
		// pass these directly to the database
		$selectionId = $selectionDB->addSelection($name, $description);
		$this->getResult()->addValue(null, 'selection', array('id' => $selectionId));
		return $selectionId;
	}
	protected function removeSelection($selectionId) {
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		$selectionDB->removeSelection($selectionId);
	}
	protected function listSelections() {
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		foreach($selectionDB->listSelections('') as $i => $selection) {
			$data[$i]['id'] = $selection->getID();
			$data[$i]['name'] = $selection->getName();
			$data[$i]['description'] = $selection->getDescription();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "selection");
			$this->getResult()->addValue(null, 'selections', $data);
		} else {
			$this->getResult()->addValue(null, 'selections', array('empty'=>'true'));
		}
	}
	protected function addEntity($selectionId, $entityId) {
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		$selectionDB->addEntity($selectionId, $entityId);
	}
	protected function removeEntity($selectionId, $entityId) {
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		if($entityId=="all") {
			$selectionDB->removeAllEntities($selectionId);
		} else {
			$selectionDB->removeEntity($selectionId, $entityId);
		}
	}
	protected function listEntities($selectionId) { // TODO pages
		$selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		foreach($selectionDB->listEntities('selection_id = ' . $selectionId) as $i => $entity) {
			$data[$i]['id'] = $entity->entity_id;
		}
		if(count($data) > 0) {
			$this->getResult()->setIndexedTagName($data, "entity");
			$this->getResult()->addValue(null, 'entities', $data);
		} else {
			$this->getResult()->addValue(null, 'entities', array('empty' => 'true'));
		}
	}
	protected function startExport($selectionId) {
    $selectionDB = SMWEnrichEntitySelectionDB::getInstance();
		$selection = $selectionDB->getSelectionById($selectionId);
    if(!empty($selection)) {
    	SMWEnrichEntitySelectionManager::getInstance()->export(
    		$selection, $wgSMWEnrichTmpDir . "/export");
    	$this->getResult()->addValue(null, 'result', array('operation' => 'OK'));
    } else {
    	$this->getResult()->addValue(null, 'result', array('operation' => 'FAIL'));
    }
	}
	public function getDescription() {
		return 'SMWEnrich Entity Selection API';
	}
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), array(
			'selection' => array (
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'add' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'remove' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'list' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'export' => array (
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'name' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true),
			'description' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true))
			);
	}
	public function getParamDescription() {
		return array_merge(parent::getParamDescription(), array(
			'jobId' => 'Entity matching job ID'
			) );
	}
	public function getExamples() {
		return array( // TODO
			'api.php?action=smwenrichjobs&face=O_o&format=xml'
			=> 'Get a sideways look (and the usual predictions)'
			);
	}
}
?>
