<?php
class SMWEnrichDataSourcesAPI extends ApiBase {
	public function execute() {
		// get request parameters
		$apiMain = $this->getMain();
		$paramId = $apiMain->getVal('id');
		$paramURL = $apiMain->getVal('url');
		$paramAdd = $apiMain->getVal('add');
		$paramRemove = $apiMain->getVal('remove');
		$paramList = $apiMain->getVal('list');
		$paramName = $apiMain->getVal('name');
		// main logic
		if($paramAdd==true && isset($paramURL) && isset($paramName)) {
			$this->addSource($paramName, $paramURL);
		} else if($paramRemove==true && isset($paramId)) {
			$this->removeDataSource($paramId);
		} else if($paramList==true) {
			$this->listDataSources();
		} else {
			// TODO set response: bad request
			$this->getResult()->addValue(null, 'result', array('operation' => 'FAIL'));
		}
		return true;
	}
	protected function addSource($name, $url) {
		$dataSourceDB = SMWEnrichDataSourceDB::getInstance();
		// MediaWiki takes care of proper escaping, so it should be ok to
		// pass these directly to the database
		$dataSourceId = $dataSourceDB->addDataSource($name, $url);
		$this->getResult()->addValue(null, 'dataSource', array('id' => $dataSourceId));
		return $dataSourceId;
	}
	protected function removeDataSource($dataSourceId) {
		$dataSourceDB = SMWEnrichDataSourceDB::getInstance();
		$dataSourceDB->removeDataSourceById($dataSourceId);
	}
	protected function listDataSources() {
		$dataSourceDB = SMWEnrichDataSourceDB::getInstance();
		foreach($dataSourceDB->listDataSources('') as $i => $dataSource) {
			$data[$i]['id'] = $dataSource->getID();
			$data[$i]['name'] = $dataSource->getName();
			$data[$i]['url'] = $dataSource->getURL();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "dataSource");
			$this->getResult()->addValue(null, 'dataSources', $data);
		} else {
			$this->getResult()->addValue(null, 'dataSources', array('empty'=>'true'));
		}
	}
	public function getDescription() {
		return 'SMWEnrich Data Source API';
	}
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), array(
			'id' => array (
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'url' => array (
				ApiBase::PARAM_TYPE => 'string',
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
			'name' => array(
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
