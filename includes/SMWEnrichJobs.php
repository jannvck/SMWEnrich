<?php
class SMWEnrichJob {
	protected $id;
	protected $name;
	protected $description;
	protected $selectionId; // entity selection
	protected $linkGroupId; // reference links group
	protected $dataSourceId;
	protected $dateStarted;
	protected $dateFinished;
	protected $progress;
	public function __construct($id, $name, $description,
			$selectionId, $linkGroupId, $dataSourceId, $dateStarted, $dateFinished,
			$progress) {
		$this->id = $id;
		$this->name = $name;
		$this->description = $description;
		$this->selectionId = $selectionId;
		$this->linkGroupId = $linkGroupId;
		$this->dataSourceId = $dataSourceId;
		$this->dateStarted = $dateStarted;
		$this->dateFinished = $dateFinished;
		$this->progress = $progress;
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
	public function getSelectionId() {
		return $this->selectionId;
	}
	public function setSelectionId($selectionId) {
		$this->selectionId = $selectionId;
	}
	public function getLinkGroupId() {
		return $this->linkGroupId;
	}
	public function setLinkGroupId($linkGroupId) {
		$this->linkGroupId = $linkGroupId;
	}
	public function getDataSourceId() {
		return $this->dataSourceId;
	}
	public function setDataSourceId($dataSourceId) {
		$this->dataSourceId = $dataSourceId;
	}
	public function getDataDirectory() {
		$ioUtil = IOUtil::getInstance();
		$wgSMWEnrichDataDir = "/tmp/enrich"; // FIXME: set config variable
		$path = $wgSMWEnrichDataDir . "/jobs/" . $this->getID();
		$ioUtil->mkdir($path);
		return $path;
	}
	public function setDateStarted($date) {
		$this->dateStarted = $date;
	}
	public function getDateStarted() {
		return $this->dateStarted;
	}
	public function setDateFinished($date) {
		$this->dateFinished = $date;
	}
	public function getDateFinished() {
		return $this->dateFinished;
	}
	public function setProgress($progress) {
		$this->progress = $progress;
	}
	public function getProgress() {
		return $this->progress;
	}
	/*
	 * convenience functions
	 */
	public function getSelection() {
		$db = SMWEnrichEntitySelectionDB::getInstance();
		return $db->getSelectionById($this->getSelectionId());
	}
	public function getReferenceLinkGroup() {
		$db = SMWEnrichReferenceLinksDB::getInstance();
		return $db->getGroupById($this->getLinkGroupId());
	}
	public function getDataSource() {
		$db = SMWEnrichDataSourceDB::getInstance();
		return $db->getDataSourceById($this->getDataSourceId());
	}
	public function getMatchingResult() {
		$db = SMWEnrichMatchingResultDB::getInstance();
		return $db->getResult($this);
	}
	public function isFinished() {
		// A job can be considered complete, when there is a
		// finish date set, beware: finished with or without errors
		$dateFinished = $this->getDateFinished();
		return isset($dateFinished);
	}
}
class SMWEnrichJobDB {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new SMWEnrichJobDB();
		}
		return self::$instance;
	}
	public function updateJob(SMWEnrichJob $job, $options = array()) {
		$db = wfGetDB( DB_SLAVE );
		return $db->update(
			'smw_enrich_jobs', // table
			array( // data
				'job_id' => $job->getID(),
				'job_name' => $job->getName(),
				'job_description' => $job->getDescription(),
				'job_start_date' => $job->getDateStarted(),
				'job_finish_date' => $job->getDateFinished(),
				'job_progress' => $job->getProgress(),
				'selection_id' => $job->getSelectionId(),
				'link_group_id' => $job->getLinkGroupId(),
				'data_source_id' => $job->getDataSourceId()),
			array('job_id = ' . $job->getID()), // conditions
			__METHOD__,
			$options);
	}
	public function addJob(SMWEnrichJob $job) {
		$jobId = mt_rand(); // TODO check for duplicate
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
			'smw_enrich_jobs', // table
			array( // data
				'job_id' => $jobId,
				'job_name' => $job->getName(),
				'job_description' => $job->getDescription(),
				'job_start_date'=> $job->getDateStarted(),
				'job_finish_date' => $job->getDateFinished(),
				'job_progress' => $job->getProgress(),
				'selection_id' => $job->getSelectionId(),
				'link_group_id' => $job->getLinkGroupId(),
				'data_source_id' => $job->getDataSourceId()),
			__METHOD__,
			array());
		return $jobId;
	}
	public function removeJob(SMWEnrichJob $job) {
		$this->removeJobById($job->getId());
	}
	public function removeJobById($jobId) {
		$db = wfGetDB( DB_SLAVE );
		return $db->delete(
			'smw_enrich_jobs', // table
			array('job_id = ' . $jobId), // conditions
			__METHOD__);
	}
	public function getJobByName($jobName) {
		$jobs = $this->getJobs('job_name = ' . $jobName);
		if(isset($jobs)) {
			return $jobs[0];
		}
		return false;
	}
	public function getJobById($jobId) {
		$jobs = $this->getJobs("job_id = " . $jobId);
		if(isset($jobs)) {
			return $jobs[0];
		}
		return false;
	}
	public function getJobs($conditions, $options = array()) {
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_jobs', // table
			array( // columns
				'job_id',
				'job_name',
				'job_description',
				'job_start_date',
				'job_finish_date',
				'job_progress',
				'selection_id',
				'link_group_id',
				'data_source_id'),
			$conditions,
			__METHOD__,
			$options);
		$jobs = array();
		foreach($result as $job) {
			$jobs[] = new SMWEnrichJob(
				$job->job_id,
				$job->job_name,
				$job->job_description,
				$job->selection_id,
				$job->link_group_id,
				$job->data_source_id,
				$job->job_start_date,
				$job->job_finish_date,
				$job->job_progress);
		}
		return $jobs;
	}
}
class SMWEnrichJobManager {
	protected static $instance;
	protected $framework;
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new SMWEnrichJobManager();
		}
		return self::$instance;
	}
	private function __construct() {
		$this->framework = ERFrameworkRegistry::getInstance()->getDefaultFramework();
		$listeners = array();
	}
	/*
	 * Remove a job from the database and all files related to this job as well.
	 * Be careful, this call cannot be undone.
	 */
	public function removeJobByID($jobId) {
		$jobDB = SMWEnrichJobDB::getInstance();
		$jobDB->removeJobById($jobId);
	}
	public function startJobByID($jobId) {
		$this->startJob(SMWEnrichJobDB::getInstance()->getJobById($jobId));
	}
	/*
	 * This function only dispatches a job to run in background.
	 * See the comment of the runJob function below also.
	 */
	public function startJob(SMWEnrichJob $job) {
		$title = Title::newFromText(
         //username . '/' .
         'smwenrich/' .
         'exportSelection/' .
         uniqid(),
         NS_USER);
		$job = new SMWEnrichMatchingJob($title, array('job' => $job));
		JobQueueGroup::singleton()->push($job);
	}
	/*
	 * This function ecapsulates the main logic of this extension on
	 * a high level. There is still a need for a proper scheduling
	 * component which allows dispatching jobs for running them in
	 * background because using the MediaWiki JobQueue is inapropriate
	 * for long running tasks and this function is a blocking call.
	 * Until this is issue is solved, it is up to the caller to run
	 * this function in background.
	 */
	public function runJob(SMWEnrichJob $job) {
		$job->setDateStarted(time());
		SMWEnrichJobDB::getInstance()->updateJob($job);
		// This dispatches a job to run in the MediaWiki JobQueue:
		//SMWEnrichEntitySelectionManager::getInstance()->export(...);
		// But we use a blocking call here, until the issue described
		// in the comment above is solved:
		$wgSMWEnrichDataDir = "/tmp/enrich"; // FIXME: config variable not set
		$wgSMWEnrichTmpDir = $wgSMWEnrichDataDir . "/tmp";
		$job->getSelection()->exportTo($wgSMWEnrichTmpDir . "/export");
		$endpointManager = $this->createEndpoints($job);
		$result = $this->framework->match($job);
		/*
		foreach($endpointManager->getEndpoints() as $name => $endpoint) {
			$endpointManager->removeEndpoint($name);
		}
		*/
		SMWEnrichMatchingResultDB::getInstance()->addResult($result);
		$job->setDateFinished(time());
		$job->setProgress(1.0);
		SMWEnrichJobDB::getInstance()->updateJob($job);
	}
	/*
	 * This function exists to make data accessible via an SPARQL endpoint.
	 * The exported entities of the virtual research environment are always
	 * loaded in an endpoint, while the data source may or may not be loaded
	 * depending on the data source URL.
	 */
	private function createEndpoints(SMWEnrichJob $job) {
		$wgSMWEnrichDataDir = "/tmp/enrich"; // FIXME: config variable not set
		$wgSMWEnrichTmpDir = $wgSMWEnrichDataDir . "/tmp";
		
		$endpointManager = SMWEnrichEndpointManagerRegistry::getInstance()->getDefaultEndpointManager();
		$endpointManager->setConfig(array( // TODO: create proper config variables
					'isql-path' => 'isql-vt',
					'host' => 'localhost',
					'user' => 'dba',
					'password' => 'somepassword'));
		// Create an endpoint for the exported entities of the virtual research
		// environment. The entities will be imported into a graph named after
		// the job ID.
		if($job->getSelection()->hasExportIn($wgSMWEnrichTmpDir . "/export")) {
			$exportFilePath = $wgSMWEnrichTmpDir . "/export";
			$endpointManager->createEndpointOf($exportFilePath, $job->getID());
			// TODO: pass the endpoint URL to the matching configuration or the
			// endpoint manager config
		} else {
			// TODO: proper error handling - entity export failed
			throw new RuntimeException("Could not create SPARQL endpoint for local entities.");
		}
		// TODO implement the condition to recognize a SPARQL endpoint correctly
		// intention: don't create an endpoint for an existing SPARQL endpoint
		// data source.
		$dataSource = $job->getDataSource();
		if(!strpos($dataSource->getURL(), "query")) {
			$download = NetUtil::getInstance()->download($dataSource->getURL(),
				$wgSMWEnrichTmpDir . "/download");
			// Remove any existing files, to not corrupt the download
			// when trying to resume a successful download.
			foreach($download->getFileNames() as $downloaded) {
				unlink($downloaded);
			}
			$download->start();
			if($download->hasCorrectSize()) {
				$endpointURL = $endpointManager->createEndpointOf(
					$download->getDirectory(), $dataSource->getURL());
				// Use this SPARQL endpoint URL but do not overwrite the original
				// URL of the data source - do not update the database
				$job->getDataSource()->setURL($endpointURL);
			} else {
				// TODO: proper error handling - incorrect download
				throw new RuntimeException("Data set download failed");
			}
		} else {
			throw new RuntimeException("NO DOWNLOAD SHIT - URL: ". $job->getDataSource()->getURL() .
				"dataSourceID: " . $job->getDataSource()->getID());
		}
		return $endpointManager;
	}
}
?>
