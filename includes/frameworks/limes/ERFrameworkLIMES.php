<?php
class ERFrameworkLIMES extends ERFrameworkBase {
	public function getID() {
		return "limes-0.6.RC3";
	}
	public function getName() {           
		return "LIMES";
	}
	public function getDescription() {
		return "LIMES is a ..."; // TODO
	}
	public function createResultObject(SMWEnrichJob $job) {
		return new MatchingResultLIMES($job, $this->getOptions($job));
	}
	public function match(SMWEnrichJob $job) {
		$options = $this->getOptions($job);
		$this->writeReferenceLinks($job);
		// To create a configuration file for a concrete matching, we need a
		// link specification to be used by LIMES.
		// Starting EAGLERunner in "learning" mode will create a link specification.
		$options['mode'] = "learning";
		if($this->writeConfig($job, $options)) {
			$this->callBinary($options);
		}
		// Now we have to write a new LIMES configuration file containing
		// a link specification ("metric"), so the matching algorithm can use it.
		//if(unlink($options['configFile'])) {
		$options['mode'] = "matching";
		if($this->writeConfig($job, $options)) {
			$this->callBinary($options);
		}
		return new MatchingResultLIMES($job, $options);
	}
	public function getProgress() {
		return false; //  TODO
	}
	function cancel() {
		return false; // TODO
	}
	protected function writeReferenceLinks(SMWEnrichJob $job) {
		$referenceLinkGroup = $job->getReferenceLinkGroup();
		$path = $job->getDataDirectory() . "/reference-links.xml";
		$bytes = OntologyAlignmentXMLWriter::writeReferenceLinks(
			$path, $referenceLinkGroup->getReferenceLinks());
		if($bytes == false) {
			throw new RuntimeException("Could not write ontology alignment file");
		}
		return $path;
	}
	protected function getOptions(SMWEnrichJob $job) {
		$dataDir = $job->getDataDirectory();
		$options['baseFolder'] = $dataDir;
		$options['dataFolder'] = $dataDir . "/data/";
		$options['configFile'] = $dataDir . "/limes-config.xml";
		$options['referenceLinks'] = $dataDir . "/reference-links.xml";
		$options['resultFolder'] = $dataDir . "results/";
		$options['resultFileName'] = "pseudo_eval.xml";
		$options['metricFileName'] = "learned.metric";
		$options['name'] = $job->getName();
		$options['acceptedThreshold'] = 0.98; 
		$options['acceptedFileName'] = "accepted.links";
		$options['reviewThreshold'] = 0.95;
		$options['reviewFileName'] = "review.links";
		return $options;
	}
	protected function writeConfig(SMWEnrichJob $job, $options) {
		/*
		$source = new KnowledgeBase("testID1", "http://sparql.org/sparql1");
		$source->setVariable("?x");
		$source->setRestriction("?x rdf:type http://www.okkam.org/ontology_person1.owl#Person");
		$source->setPagesize(1000);
		$source->addProperty("http://www.okkam.org/ontology_person1.owl#surname AS lowercase");
		$target = new KnowledgeBase("testID2", "http://sparql.org/sparql2");
		$target->setVariable("?x");
		$target->setRestriction("?y rdf:type okkamperson2:Person");
		$target->setPagesize(1000);
		$target->addProperty("http://www.okkam.org/ontology_person1.owl#surname AS lowercase");
		$config = new LIMESConfig($source, $target, new LinkFile(0.98, "accepted.links"), new LinkFile(0.95, "review.links"));
		$config->addPrefix("http://www.okkam.org/ontology_person1.owl#", "okkamperson1");
		$config->addPrefix("http://www.okkam.org/ontology_person2.owl#", "okkamperson2");
		$config->setMetric("levenshtein(x.http://www.okkam.org/ontology_person1.owl#surname, y.http://www.okkam.org/ontology_person2.owl#surname)");
		$writer = new LIMESConfigWriter();
		echo $writer->write($config);
		*/
		/*
		 * This is a bit confusing: the LIMES configuration file currently only
		 * supports exactly one "source" and one "target".
		 * In contrast to that, this is extension is designed to be easily extended
		 * to support multiple data sources when the underlying ER framework supports
		 * this as well, thus a "data sources" in SMWEnrich can either refer to a
		 * "source" or a "target" in LIMES.
		 *
		 * In a nutshell: source refers to MediaWiki, target to external data source
		 */
		// Entities of the virtual research environment will be imported into
		// a graph named after the job ID. So we refer to that as the graph name.
		//$configSource = new KnowledgeBase(0, "http://localhost:3030/smw/query");
		// TODO: set the endpoint URL properly
		$configSource = new KnowledgeBase(0, "http://localhost:8890/sparql");
		$configSource->setVariable("?x");
		$configSource->setRestriction("?x rdf:type smw:Kategorie-3APerson");
		$configSource->setPagesize(1000);
		$configSource->addProperty("rdfs:label AS lowercase");
		$jobDataSource = $job->getDataSource();
		// TODO: take the endpoint URL from the job data source as in the line below
		//$configTarget = new KnowledgeBase($jobDataSource->getID(), $jobDataSource->getURL());
		// This is a workaround, to avoid downloading a large dataset each time during development.
		$configTarget = new KnowledgeBase($jobDataSource->getID(), "http://localhost:8890/sparql");
		$configTarget->setVariable("?x");
		$configTarget->setRestriction("?x rdf:type dnb:DifferentiatedPerson");
		$configTarget->setPagesize(1000);
		$configTarget->addProperty("dnb:preferredNameForThePerson AS lowercase");
		$config = new LIMESConfig(
			$configSource,
			$configTarget,
			new LinkFile(
				$options['acceptedThreshold'],
				$options['acceptedFileName']),
			new LinkFile(
				$options['reviewThreshold'],
				$options['reviewFileName']));
		//$config->addPrefix("http://testwiki.smw-cora.org/index.php/Spezial:URI-Aufl%C3%B6ser/", "smw");
		$config->addPrefix("http://localhost/smw-cora/index.php/Spezial:URI-Aufl%C3%B6ser/", "smw");
		$config->addPrefix("http://d-nb.info/gnd/", "gnd");
		$config->addPrefix("http://d-nb.info/standards/elementset/gnd#", "dnb");
		if($options['mode']=="matching") {
			$baseFolderPathInfo = pathinfo($options['baseFolder']);
			$metric = file_get_contents(
				$baseFolderPathInfo['dirname'] . "/" .
				$baseFolderPathInfo['basename'] . "/" .
				$options['metricFileName']);
			if($metric==false) {
				throw new RuntimeException("Could not read metric file "+
					$options['metricFileName']);
			}
			$config->setMetric($metric);
		}
		$writer = new LIMESConfigWriter();
		return file_put_contents($options['configFile'], $writer->write($config));		
	}
	/*
	 * EAGLERunner supports running LIMES in two modes:
	 * (1) "learning" mode: learning a link specification with EAGLE
	 * (2) "matching" mode: actually matching entities of two datasets 
	 */
	protected function callBinary($args) {
		/* EAGLERunner CLI
		 --mode learning
		 --baseFolder "resources/" 
		 --dataFolder "smwdnb/"
		 --cacheFolder "/tmp/cache"
		 --configFile "smwdnb.xml" 
		 --referenceLinks "smwdnb-goldstandard.xml.csv" 
		 --resultFolder "resources/results/" 
		 --resultFileName "Pseudo_eval_Persons1.csv.xml" 
		 --name smwdnb
		 */
		 $erunBin = "erun_2.10-0.1-SNAPSHOT-one-jar.jar"; // TODO use patched LIMES
		 $erunPath = "/home/jan/Dokumente/diplomarbeit/dev/workspace/erun/target/scala-2.10/" . $erunBin;
		 $baseFolderPathInfo = pathinfo($args['baseFolder']);
		 $cmd = "/usr/bin/java -jar \"" . $erunPath . "\" " .
		 				"--mode \"" . $args['mode'] . "\" " .
		 				"--baseFolder \"" . $baseFolderPathInfo['dirname'] . "/" .
		 														$baseFolderPathInfo['basename'] . "/\" " .
		 				"--dataFolder \"" . basename($args['dataFolder']) . "/\" " .
		 				"--cacheFolder \"" . $args['cacheFolder'] . "/\" " .
		 				"--configFile \"" . basename($args['configFile']) . "\" " .
		 				"--referenceLinks \"" . basename($args['referenceLinks']) . "\" " .
		 				"--resultFolder \"" . basename($args['resultFolder']) . "/\" " .
		 				"--resultFileName \"" . $args['resultFileName'] . "\" " .
		 				"--metricFileName \"" . $args['metricFileName'] . "\" " .
		 				"--name \"" . $args['name'] . "\" " .
		 				"2>&1"; // additional options
		 // TODO: error handling
		 $out = shell_exec($cmd);
		 // debugging
		 file_put_contents("/tmp/smwenrich.out", array($cmd, $out));
	}
}
class MatchingResultLIMES extends SMWEnrichMatchingResult {
	protected $options;
	public function __construct(SMWEnrichJob $job, $options) {
		parent::__construct($job);
		$this->options = $options;
	}
	public function getAlignment() {
		$job = $this->getJob();
		$dataDir = $job->getDataDirectory();
		$path = $dataDir . "/" . $this->options['acceptedFileName'];
		if(!file_exists($path)) {
			throw new RuntimeException("Link file " . $path . " does not exist");
		}
		$alignment = new OntologyAlignment();
		$alignment->setIsXML('yes');
		$alignment->setLevel('0');
		$alignment->setType('**');
		$alignment->addOntology(new Ontology('some1','crazy1','lang'));
		$alignment->addOntology(new Ontology('some2','crazy2','lang'));
		foreach(file($path) as $line) {
			$chunks = explode(" ", $line, 4);
			$cell = new Cell(
					0, // no ID assigned by LIMES as of 0.6.RC3
					substr($chunks[0], 1, -1),
					substr($chunks[2], 1, -1),
					"http://www.w3.org/2002/07/owl#sameAs",
					$this->options['acceptedThreshold']);
			$alignment->addMapping($cell);
		}
		return $alignment;
	}
}
?>
