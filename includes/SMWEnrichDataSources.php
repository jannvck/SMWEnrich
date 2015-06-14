<?php
class SMWEnrichDataSource {
	protected $id;
	protected $name;
	protected $url;
	public function __construct($id, $name, $url) {
		$this->id = $id;
		$this->name = $name;
		$this->url = $url;
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
	public function getURL() {
		return $this->url;
	}
	public function setURL($url) {
		$this->url = $url;
	}
}
class SMWEnrichDataSourceDB {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function addDataSource($name, $url) {
		$dataSourceId = mt_rand(); // TODO check for duplicate
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_data_sources', // table
			array( // data
				'data_source_id' => $dataSourceId,
				'data_source_name	' => $name,
				'data_source_url' => $url),
			__METHOD__,
			array());
		return new SMWEnrichDataSource($dataSourceId, $name, $url);
	}
	public function removeDataSource(SMWEnrichDataSource $dataSource) {
		$this->removeDataSourceById($dataSource-getID());
	}
	public function removeDataSourceById($dataSourceId) {
		$db = wfGetDB( DB_SLAVE );
		return $db->delete(
			'smw_enrich_data_sources', // table
			array('data_source_id = ' . $dataSourceId), // conditions
			__METHOD__);
	}
	public function getDataSourceById($dataSourceId) {
		$dataSources = $this->listDataSources('data_source_id = ' . $dataSourceId);
		if(isset($dataSources)) {
			return $dataSources[0];
		}
		return false;
	}
	public function listDataSources($conditions) {
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_data_sources', // table
			array( // columns
				'data_source_id',
				'data_source_name',
				'data_source_url'),
			$conditions,
			__METHOD__,
			array()); // options
		$dataSources = array();
		foreach($result as $dataSource) {
			$dataSources[] = new SMWEnrichDataSource(
				$dataSource->data_source_id,
				$dataSource->data_source_name,
				$dataSource->data_source_url);
		}
		return $dataSources;
	}
}
?>
