<?php
class SMWEnrichReferenceLinkGroup {
	protected $id;
	protected $name;
	protected $description;
	public function __construct($id, $name, $description) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
	}
	public function getID() {
		return $this->id;
	}
	public function setName($name) {
		$this->name = $name;
	}
	public function getName() {
		return $this->name;
	}
	public function setDescription($description) {
		$this->description = $description;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getReferenceLinks() {
		$db = SMWEnrichReferenceLinksDB::getInstance();
		return $db->getLinksByGroup($this);
	}
}
class SMWEnrichReferenceLink {
	protected $linkId;
	protected $uri0;
	protected $uri1;
	public function __construct($linkId, $uri0, $uri1) {
		$this->linkId = $linkId;
		$this->uri0 = $uri0;
		$this->uri1 = $uri1;
	}
	public function setLinkId($id) {
		$this->linkId = $id;
	}
	public function getLinkId() {
		return $this->linkId;
	}
	public function setURI0($uri0) {
		$this->uri0 = $uri0;
	}
	public function getURI0() {
		return $this->uri0;
	}
	public function setURI1($uri1) {
		$this->uri1 = $uri1;
	}
	public function getURI1() {
		return $this->uri1;
	}
}
class SMWEnrichReferenceLinksDB {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new SMWEnrichReferenceLinksDB();
		}
		return self::$instance;
	}
	public function addGroup($name, $description) {
		$groupId = mt_rand(); // TODO: check for duplicate
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_reference_links_meta', // table
			array( // data
				'link_group_id' => $groupId,
				'link_group_name' => $name,
				'link_group_description' => $description),
			__METHOD__,
			array());
		return $groupId;
	}
	public function getGroupById($groupId) {
		$groups = $this->getGroups(array('link_group_id = ' . $groupId));
		if(isset($groups)) {
			return $groups[0];
		}
		return false;
	}
	public function getAllGroups() {
		return $this->getGroups(array());
	}
	protected function getGroups($conditions) {
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_reference_links_meta', // table
			array('link_group_id', 'link_group_name', 'link_group_description'), // columns
			$conditions,
			__METHOD__,
			array()); // options
		$groups = array();
		foreach($result as $group) {
			$groups[] = new SMWEnrichReferenceLinkGroup(
				$group->link_group_id,
				$group->link_group_name,
				$group->link_group_description);
		}
		return $groups;
	}
	public function removeGroupById($groupId) {
		return $this->removeGroup($groupId, array('link_group_id = ' . $groupId));
	}
	protected function removeGroup($groupId, $conditions) {
		$db = wfGetDB( DB_SLAVE );
		// remove all links in this group
		$db->delete(
			'smw_enrich_reference_links', // table
			array('link_group_id = ' . $groupId),
			__METHOD__);
		return $db->delete(
			'smw_enrich_reference_links_meta', // table
			$conditions, // conditions
			__METHOD__);
	}
	public function addLink($linkGroupId, $uri0, $uri1) {
		return $this->addLinkByInstance($linkGroupId, 
			new SMWEnrichReferenceLink(null, $uri0, $uri1));
	}
	protected function addLinkByInstance($linkGroupId, SMWEnrichReferenceLink $link) {
		$linkId = mt_rand();
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_reference_links', // table
			array( // data
				'link_id' => $linkId,
				'local_entity_id' => $link->getURI0(),
				'external_entity_id' => $link->getURI1(),
				'link_group_id' => $linkGroupId),
			__METHOD__,
			array());
		$link->setLinkId($linkId);
		return $linkId;
	}
	public function getLinksByGroup(SMWEnrichReferenceLinkGroup $group, $options = array()) {
		return $this->getLinksByGroupID($group->getId(), $options);
	}
	public function getLinksByGroupID($groupId, $options = array()) { // TODO paging
		return $this->getLinks(array('link_group_id = ' . $groupId));
	}
	protected function getLinks($conditions, $options = array()) {
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_reference_links', // table
			array('link_id', 'local_entity_id', 'external_entity_id'), // columns
			$conditions,
			__METHOD__,
			$options); // options
		$links = array();
		foreach($result as $link) {
			$links[] = new SMWEnrichReferenceLink(
				$link->link_id,
				$link->local_entity_id,
				$link->external_entity_id);
		}
		return $links;
	}
	public function removeLinkById($linkId) {
		return $this->removeLinks(array('link_id = ' . $linkId));
	}
	protected function removeLinks($conditions) {
		$db = wfGetDB( DB_SLAVE );
		return $db->delete(
			'smw_enrich_reference_links', // table
			$conditions,
			__METHOD__);
	}
}
?>
