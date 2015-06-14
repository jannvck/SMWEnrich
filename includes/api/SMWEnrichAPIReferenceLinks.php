<?php
class SMWEnrichReferenceLinksAPI extends ApiBase {
	public function execute() {
		// get request parameters
		$apiMain = $this->getMain();
		$paramGroup = $apiMain->getVal('group');
		$paramAdd = $apiMain->getVal('add');
		$paramRemove = $apiMain->getVal('remove');
		$paramList = $apiMain->getVal('list');
		$paramName = $apiMain->getVal('name');
		$paramDescription = $apiMain->getVal('description');
		$paramURI0 = $apiMain->getVal('uri0');
		$paramURI1 = $apiMain->getVal('uri1');
		$paramLink = $apiMain->getVal('link'); // ID of a reference link
		// main logic
		if($paramAdd==true && empty($paramGroup) && isset($paramName)) {
			$this->addGroup($paramName, $paramDescription);
		} else if($paramRemove==true && isset($paramGroup) && empty($paramURI0) && empty($paramURI1)) {
			$this->removeGroup($paramGroup);
		} else if($paramList && empty($paramGroup)) {
			$this->listGroups();
		} else if($paramList && isset($paramGroup)) {
			$this->listLinks($paramGroup);
		} else if($paramAdd==true && isset($paramGroup) && isset($paramURI0) && isset($paramURI1)) {
			$this->addLink($paramGroup, $paramURI0, $paramURI1);
		} else if($paramRemove==true && isset($paramLink)) {
			$this->removeLink($paramLink);
		} else {
			$this->getResult()->addValue(null, 'result', array('operation' => 'FAIL'));
		}
		return true;
	}
	protected function addGroup($name, $description) {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		$groupId = $refDB->addGroup($name, $description);
		$this->getResult()->addValue(null, 'group', array('id' => $groupId)); 
	}
	protected function listGroups() {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		foreach($refDB->getAllGroups() as $i => $group) {
			$data[$i]['id'] = $group->getID();
			$data[$i]['name'] = $group->getName();
			$data[$i]['description'] = $group->getDescription();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "group");
			$this->getResult()->addValue(null, 'groups', $data);
		} else {
			$this->getResult()->addValue(null, 'groups', array('empty' => 'true'));
		}
	}
	protected function removeGroup($id) {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		$refDB->removeGroupById($id);
	}
	protected function addLink($groupId, $uri0, $uri1) {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		$linkId = $refDB->addLink($groupId, $uri0, $uri1);
		$this->getResult()->addValue(null, 'link', array('id' => $linkId));
	}
	protected function removeLink($linkId) {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		$refDB->removeLinkById($linkId);
	}
	protected function listLinks($groupId) {
		$refDB = SMWEnrichReferenceLinksDB::getInstance();
		foreach($refDB->getLinksByGroupID($groupId) as $i => $link) {
			$data[$i]['id'] = $link->getLinkID();
			$data[$i]['uri0'] = $link->getURI0();
			$data[$i]['uri1'] = $link->getURI1();
		}
		if(count($data)>0) {
			$this->getResult()->setIndexedTagName($data, "link");
			$this->getResult()->addValue(null, 'links', $data);
		} else {
			$this->getResult()->addValue(null, 'links', array('empty' => 'true'));
		}
	}
	public function getDescription() {
		return 'SMWEnrich API';
	}
	public function getAllowedParams() {
		return array_merge(parent::getAllowedParams(), array(
			'group' => array (
				ApiBase::PARAM_TYPE => 'int',
				ApiBase::PARAM_REQUIRED => true),
			'add' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'list' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'remove' => array(
				ApiBase::PARAM_TYPE => 'boolean',
				ApiBase::PARAM_REQUIRED => true),
			'uri0' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true),
			'uri1' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true),
			'link' => array(
				ApiBase::PARAM_TYPE => 'int',
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
		// TODO
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
