<?php
class SMWEnrichEntitySelectionExportMultipleJob extends Job {
	public function __construct($title, $params) {
		parent::__construct( 'smwenrichentityselectionexport', $title, $params );
	}
	public function run() {
		$selection = $this->params['entitySelection'];
		$selection->exportTo($this->params['path']);
		return true;
	}
}
class SMWEnrichMatchingJob extends Job {
	public function __construct($title, $params) {
		parent::__construct( 'smwenrichmatchingjob', $title, $params );
	}
	public function run() {
		SMWEnrichJobManager::getInstance()->runJob($this->params['job']);
		return true;
	}
}
class SMWEnrichDownloadJob extends Job {
	public function __construct($title, $params) {
		parent::__construct( 'smwenrichdownload', $title, $params );
	}
	public function run() {
		$download = NetUtil::getInstance()->download(
			$this->params['url'], $this->params['path']);
		$download->start();
		if($download->hasCorrectSize()) {
			return true;
		} else {
			return false;
		}
	}
}
class SMWEnrichCreateEndpointJob extends Job {
	public function __construct($title, $params) {
		parent::__construct( 'smwenrichfetchandloadrdf', $title, $params );
	}
	public function run() {
		SMWEnrichDataSourceManager::getInstance()->createEndpointOf(
			$this->params['dataSource']);
	}
}
?>
