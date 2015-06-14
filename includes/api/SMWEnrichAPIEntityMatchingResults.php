<?php
class SMWEnrichEntityMatchingResultAPI extends ApiBase {
	public function execute() {
		// get request parameters
		$apiMain = $this->getMain();
		$paramJob = $apiMain->getVal('job');
		$paramList = $apiMain->getVal('list');
		$paramRemove = $apiMain->getVal('remove');
		$paramPublish = $apiMain->getVal('publish');
		// main logic
		if(isset($paramJob) && isset($paramList)) {
			$this->listLinks($paramJob);
		} else if(isset($paramRemove)) {
			$this->removeLink($paramRemove);
		} else if(isset($paramJob) && $paramPublish==true) {
			$this->publish($paramJob);
		} else {
			$this->getResult()->addValue(null, 'result', array('operation' => 'FAIL'));
		}
		return true;
	}
	protected function listLinks($jobId) {
		$resultDB = SMWEnrichMatchingResultDB::getInstance();
		$cells = $resultDB->getResultByJobID($jobId)->getLinks();
		foreach($cells as $i => $cell) {
			$data[$i]['id'] = $cell->getID();
			$data[$i]['entity0'] = $cell->getEntity0();
			$data[$i]['entity1'] = $cell->getEntity1();
			$data[$i]['relation'] = $cell->getRelation();
			$data[$i]['measure'] = $cell->getMeasure();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "link");
			$this->getResult()->addValue(null, 'links', $data);
		} else {
			$this->getResult()->addValue(null, 'links', array('empty' => 'true'));
		}
	}
	protected function removeLink($linkId) { // TODO: should link IDs really be global? 
		$resultDB = SMWEnrichMatchingResultDB::getInstance();
		$resultDB->removeLinkByID($linkId);
		// TODO: proper error handling
		$this->getResult()->addValue(null, 'result', array('operation' => 'OK'));
	}
	protected function publish($jobId) {
		$cells = SMWEnrichMatchingResultDB::getInstance()->getResultByJobID($jobId);
		SMWEnrichMatchingResultManager::getInstance()->addArctileAnnotations($cells);
		// TODO: proper error handling
		$this->getResult()->addValue(null, 'result', array('operation' => 'OK'));
	}
	public function getDescription() {
		return 'SMWEnrich entity matching results API module';
	}
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), array(
			'job' => array (
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'list' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'remove' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'publish' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true)));
	}
	public function getParamDescription() {
		// TODO
		return array_merge(parent::getParamDescription(), array(
			'job' => 'Entity matching job ID'));
	}
	public function getExamples() {
		return array( // TODO
			'api.php?action=smwenrichjobs&face=O_o&format=xml'
			=> 'Get a sideways look (and the usual predictions)');
	}
}
?>
