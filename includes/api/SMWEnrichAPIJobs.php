<?php
class SMWEnrichJobsAPI extends ApiBase {
	public function execute() {
		// get request parameters
		$apiMain = $this->getMain();
		$paramId = $apiMain->getVal('id');
		$paramAdd = $apiMain->getVal('add');
		$paramRemove = $apiMain->getVal('remove');
		$paramUpdate = $apiMain->getVal('update');
		$paramList = $apiMain->getVal('list');
		$paramStart = $apiMain->getVal('start');
		$paramName = $apiMain->getVal('name');
		$paramDescription = $apiMain->getVal('description');
		$paramSelection = $apiMain->getVal('selection');
		$paramLinks = $apiMain->getVal('links');
		$paramDataSource = $apiMain->getVal('dataSource');
		// main logic
		if($paramAdd==true) {
			$this->addJob($paramName, $paramDescription);
		} else if($paramRemove==true) {
			$this->removeJob($paramId);
		} else if($paramUpdate==true) {
			$this->updateJob(
				$paramId,
				$paramName,
				$paramDescription,
				$paramSelection,
				$paramLinks,
				$paramDataSource);
		} else if($paramList==true) {
			$this->listJobs();
		} else if($paramStart=true && isset($paramId)) {
			$this->startJob($paramId);
		} else {
			$this->getResult()->addValue(null, 'result', array('request' => 'FAIL'));
		}
		return true;
	}
	protected function addJob(
			$name,
			$description,
			$selectionId,
			$linkGroupId,
			$dataSourceId) {
		$db = SMWEnrichJobDB::getInstance();
		// MediaWiki takes care of proper escaping, so it should be ok to
		// pass these directly to the database
		$jobId = $db->addJob(
			new SMWEnrichJob(
				null, // job ID is generated server-side and returned to the client 
				$name,
				$description,
				$selectionId,
				$linkGroupId,
				$dataSourceId));
		$this->getResult()->addValue(null, 'job', array('id' => $jobId));
	}
	protected function removeJob($jobId) {
		// Don't call the database functions directly, because
		// a job may also have some other files related to it
		// which need to be removed as well.
		SMWEnrichJobManager::getInstance()->removeJobByID($jobId);
	}
	protected function updateJob(
			$jobId,
			$name,
			$description,
			$selectionId,
			$linkGroupId,
			$dataSourceId) {
		$db = SMWEnrichJobDB::getInstance();
		$job = $db->getJobById($jobId);
		$job->setName($name);
		$job->setDescription($description);
		$job->setSelectionId($selectionId);
		$job->setLinkGroupId($linkGroupId);
		$job->setDataSourceId($dataSourceId);
		$db->updateJob($job);
	}
	protected function listJobs() {
		$jobDB = SMWEnrichJobDB::getInstance();
		foreach($jobDB->getJobs(null) as $i => $job) {
			$data[$i]['id'] = $job->getID();
			$data[$i]['name'] = $job->getName();
			$data[$i]['description'] = $job->getDescription();
			$data[$i]['selectionId'] = $job->getSelectionId();
			$data[$i]['linkGroupId'] = $job->getLinkGroupId();
			$data[$i]['dataSourceId'] = $job->getDataSourceId();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "job");
			$this->getResult()->addValue(null, 'jobs', $data);
		} else {
			$this->getResult()->addValue(null, 'jobs', array('empty'=>'true'));
		}
	}
	protected function startJob($jobId) {
		SMWEnrichJobManager::getInstance()->startJobByID($jobId);
		$this->getResult()->addValue(null, 'result', array('request' => 'OK'));
	}
	public function getDescription() {
		return 'SMWEnrich API';
	}
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), array(
			'id' => array (
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'add' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'remove' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'start' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'update' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'name' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true),
			'description' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true),
			'selection' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'links' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'dataSource' => array(
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true))
			);
	}
	public function getParamDescription() {
		return array_merge(parent::getParamDescription(), array(
			'jobId' => 'Entity matching job ID'
			) );
	}
	public function getExamples() {
		return array(
			'api.php?action=smwenrichjobs&add=true&name=test&description=test'
			=> 'Add a job named "test" with description "test"');
	}
}
?>
