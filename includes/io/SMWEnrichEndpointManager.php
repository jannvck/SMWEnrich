<?php
interface SMWEnrichEndpointManager {
	function getID();
	function createEndpointOf($dataURL, $name);
	function getProgressOf($name);
	function cancelEndpointCreation();
	function removeEndpoint($name);
	function getEndpoints();
}
class SMWEnrichEndpointManagerRegistry {
	protected static $instance;
	protected $managers;
	private function __construct() {
		$managers = array();
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	} 
	public function addEndpointManager(SMWEnrichEndpointManager $manager) {
		$managers[] = array(
			'id' => $manager->getID(),
			'instance' => $manager);
	}
	public function getEndpointManagers() {
		return $this->managers;
	}
	public function getDefaultEndpointManager() {
		return new SMWEnrichVirtuosoEndpointManager();
	}
}
class SMWEnrichVirtuosoEndpointManager {
	protected $config = null;
	protected $endpoints = array();
	public function getID() {
		return "VirtuosoEndpointManager";
	}
	public function setConfig(Array $config) {
		$this->checkConfig($config);
		$this->config = $config;
	}
	public function createEndpointOf($dataDirectoryPath, $graphName) {
		$this->bulkLoad($dataDirectoryPath, "*.rdf", $graphName);
		$this->endpoints[$graphName] = array('dataDirectory' => $dataDirectoryPath);
		// TODO set endpoint URL properly
		return "http://localhost:3030/". $graphName . "/query";
	}
	public function getProgressOf($name) {
		// TODO
		return false;
	}
	public function cancelEndpointCreation() {
		$this->stopBulkLoad();
	}
	public function removeEndpoint($name) {
		$isqlScriptPath = $this->endpoints[$name]['dataDirectory'] .
			"/dropGraph.isql.script";
		$isqlScriptContent = "SPARQL DROP GRAPH <" . $name . ">;";
		file_put_contents($isqlScriptPath, $isqlScriptContent);
		return $this->isqlCmd($isqlScriptPath);
	}
	public function getEndpoints() {
		return $this->endpoints;
	}
	protected function mkDataDir($name) {
		/*
		 * http://virtuoso.openlinksw.com/dataspace/doc/dav/wiki/Main/VirtBulkRDFLoader
		 *
		 * myfile.n3          ;; RDF data
		 * myfile.n3.graph    ;; Contains Graph IRI name into which RDF data from myfile.n3 will be loaded
		 * global.graph       ;; Contains Graph IRI name into which RDF data from any files that do not have a specific graph name file will be loaded
		 */
		 $tmpDataDir = $$this->tmpDir . "/" . $name; // TODO win/unix paths, escaping, chroot
		 $ioUtil = new IOUtil();
		 try {
		 	 $ioUtil->mkdir($tmpDataDir);
		 	 $ioUtil->fwrite($tmpDataDir . "/" . $name . ".graph", "http://somegraph.org/"); // TODO store graph mapping in db: $name <-> graph
		 	 $ioUtil->fwrite($tmpDataDir . "/global.graph", "http://someothergraph.org");
		 	 return true;
		 } catch (Exception $e) {
		 	 // TODO
		 	 return false;
		 }
	}
	public function bulkLoad($dataPath, $filePattern, $defaultGraphIRI) {
		$isqlScriptPath = $dataPath . "/bulkLoad.isql.script";
		// It may have some consequences, if the graph is not explicitly created,
		// this isn't mandatory though.
		$isqlScriptContent = "SPARQL CREATE GRAPH <" . $defaultGraphIRI . ">;\n";
		$isqlScriptContent = $isqlScriptContent . 'ld_dir (\'' . $dataPath .'\', \'' . $filePattern .
			'\', \'' . $defaultGraphIRI . '\');';
		$isqlScriptContent = $isqlScriptContent . "\nrdf_loader_run();";
		$isqlScriptContent = $isqlScriptContent . "\ncheckpoint;";
		// Remove the bulk load job, otherwise running another bulk load job
		// on the same file will have no effect, without warning.
		$isqlScriptContent = $isqlScriptContent . "\nDELETE FROM DB.DBA.load_list WHERE ll_state=2;";
		//$ioUtil = IOUtil::getInstance();
		//$ioUtil->fwrite($isqlScriptPath, $isqlScriptContent);
		file_put_contents($isqlScriptPath, $isqlScriptContent);
		return $this->isqlCmd($isqlScriptPath);
	}
	protected function stopBulkLoad() {
		$this->isqlCmd('exec=\'rdf_load_stop();\'');
	}
	private function isqlCmd($args) {
		$this->checkConfig($this->config);
		$cmd = $this->config['isql-path'] . " " .
		$this->config['host'] . " " .
		$this->config['port'] . " " .
		$this->config['user'] . " " .
		$this->config['password'] . " " . $args;
		echo escapeshellcmd($cmd);
		shell_exec(escapeshellcmd($cmd));
	}
	private function checkConfig($config) {
		if(!isset($config['isql-path']) ||
			!isset($config['host']) || 
			//!isset($config['port']) || // optional
			!isset($config['user']) ||
			!isset($config['password'])) {
				throw new RuntimeException("Missing configuration values");
		}
	}
}
/* usage example:
require("/home/jan/Dokumente/diplomarbeit/dev/workspace/smwEnrich/includes/util/IOUtil.php");
$man = new VirtuosoEndpointManager();
$man->setConfig(array(
	'isql-path' => 'isql-vt',
	'host' => 'localhost',
	'user' => 'dba',
	'password' => 'somepassword'));
$man->bulkLoad("/tmp/test/josef", "*.rdf", "http://smw-cora.org/FAILED");
*/
?>
