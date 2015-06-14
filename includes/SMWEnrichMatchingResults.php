<?php
/*
 * Concrete MatchingResult classes should be declared along with their
 * corresponding ERFramework implementations. See includes/frameworks/
 *
 * This class is intended to be used as a future.
 */
abstract class SMWEnrichMatchingResult {
	protected $job;
	public function __construct(SMWEnrichJob $job) {
		$this->job = $job;
	}
	public function getJob() {
		return $this->job;
	}
	public abstract function getAlignment(); // Ontology Alignment Format object
	/*
	 * Convenience functions below
	 */
	public function addLink(Cell $link) {
		SMWEnrichMatchingResultDB::getInstance()->addLink($link);
	}
	public function getLinks($options = array()) {
		return SMWEnrichMatchingResultDB::getInstance()->getLinksByResult($this, $options);
	}
}
class SMWEnrichMatchingResultDB {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new SMWEnrichMatchingResultDB();
		}
		return self::$instance;
	}
	public function addResult(SMWEnrichMatchingResult $result) {
		$alignment = $result->getAlignment();
		foreach($alignment->getMappings() as $cell) {
			$this->addLink($result->getJob(), $cell);
		}
	}
	public function getResultByJobName($jobName) {
		return $this->getResult(SMWEnrichJobDB::getInstance()->getJobByName($jobName));
	}
	public function getResultByJobID($jobId) {
		return $this->getResult(SMWEnrichJobDB::getInstance()->getJobById($jobId));
	}
	public function getResult(SMWEnrichJob $job) {
		// prepared extensibilty of this extension for other frameworks
		$framework = ERFrameworkRegistry::getInstance()->getDefaultFramework();
		return $framework->createResultObject($job);
	}
	public function addLink(SMWEnrichJob $job, Cell $link, $options = array()) {
		$this->addLinkByJobID($job->getID(), $link, $options);
	}
	public function addLinkByJobID($jobId, Cell $link, $options = array()) {
		// If link has an ID defined use that,
		// otherwise create a new random ID
		$linkId = $link->getID();
		if(!isset($linkId) || $linkId == 0) {
			$linkId = mt_rand();
		}
		$db = wfGetDB( DB_SLAVE );
		$db->insert(
		'smw_enrich_results', // table
		array( // data
			'link_id' => $linkId,
			'local_entity_id' => $link->getEntity0(),
			'external_entity_id' => $link->getEntity1(),
			'link_relation'=> $link->getRelation(),
			'link_measure' => (float) $link->getMeasure(),
			'job_id' => $jobId),
		__METHOD__,
		$options);
		return $linkId;
	}
	public function removeLink(Cell $link) {
		$this->removeLinkByID($link->getID());
	}
	public function removeLinkByID($linkId) {
		$db = wfGetDB( DB_SLAVE );
		$db->delete(
			'smw_enrich_results', // table
			array('link_id = ' . $linkId), // conditions
			__METHOD__);
	}
	public function getLinkById($linkId, $options = array()) {
		$links = $this->getLinks('link_id = ' . $linkId, $options);
		if(isset($links)) {
			return $links[0];
		}
		return false;
	}
	public function getLinksByResult(SMWEnrichMatchingResult $result, $options = array()) {
		return $this->getLinks("job_id = " . $result->getJob()->getID());
	}
	public function getLinks($conditions, $options = array()) {
		$db = wfGetDB( DB_SLAVE );
		$result = $db->select(
			'smw_enrich_results', // table
			array( // columns
				'link_id',
				'local_entity_id',
				'external_entity_id',
				'link_relation',
				'link_measure',
				'job_id'),
			$conditions,
			__METHOD__,
			$options);
		$links = array();
		foreach($result as $link) {
			$links[] = new Cell(
				$link->link_id,
				$link->local_entity_id,
				$link->external_entity_id,
				$link->link_relation,
				$link->link_measure);
		}
		return $links;
	}
}
class SMWEnrichMatchingResultManager {
	private static $instance;
	private function __construct() {
	}
	public static function getInstance() {
		if(self::$instance == null) {
			self::$instance = new SMWEnrichMatchingResultManager();
		}
		return self::$instance;
	}
	public function addResultArticle($result) {
		$title = Title::newFromText($result->getJob()->getID());
		$article = WikiPage::factory($title);
		// TODO
		$article->doEdit($content, 'SMWEnrich: entity matching result');
	}
	private function storeSemanticData(SMWEnrichMatchingResult $result) {
		/* I don't know how to realise this properly in SMW, and could not
		 * find out in 2 days.
		 * The simple task to just add some data is merely documented.
		 * Starting with 1.9.x, there are some test cases in the public repository,
		 * but they are almost identical to what I did here.
		 */
		$title = Title::makeTitle(
			"testNS", // namespace
			"testTitle", // title
			"info", // fragment
			""); // interwiki prefix
		$article = new Article($title);
		$article->doEdit("test", "summary");
		$subject = WikiPage::newFromTitle($title);
		//$data = new SMWSemanticData($subject);
		//SMWSubobject::addPropertyValueToSemanticData('myProperty', 'myValue', $data);
		//$propertyHasID = new SMWDIProperty('_hasID');
		
		/*
		$diJobId = new SMWDINumber((int) $result->getJob()->getID());
		$diJobName = new SMWDIString((string) $result->getJob()->getName());
		$diJobDescription = new SMWDIString((string) $result->getJob()->getDescription());
		$diJobSelection = new SMWDIString((string) $result->getJob()->getSelection()->getName());
		$diJobLinkGroup = new SMWDIString((string) $result->getJob()->getReferenceLinkGroup()->getName());
		$diJobDataSource = new SMWDIString((string) $result->getJob()->getDataSource()->getName());
		$diJobProgress = new SMWDINumber((float) $result->getJob()->getProgress());
		$diJobStartDate = SMWDITime::newFromTimestamp((int) $result->getJob()->getDateStarted());
		$diJobEndDate = SMWDITime::newFromTimestamp((int) $result->getJob()->getDateFinished());
		$propertyHasID = SMWDIProperty::newFromUserLabel("_hasID");
		$subData = new SMWSemanticData($subject);
		$subData->addPropertyObjectValue($propertyHasID, $diJobId);
		*/
		//$data->addSubSemanticData($subData);
		
		//$data->addPropertyValue("hasName", $diJobName);
		//$data->addPropertyObjectValue($propertyHasID, $diJobId);
		/*
		$data->addPropertyValue("hasDescription", $diJobDescription);
		$data->addPropertyValue("hasEntitySelection", $diJobSelection);
		$data->addPropertyValue("hasLinkGroup", $diJobLinkGroup);
		$data->addPropertyValue("hasDataSource", $diJobDataSource);
		$data->addPropertyValue("hasProgress", $diJobProgress);
		$data->addPropertyValue("hasStartDate", $diJobStartDate);
		$data->addPropertyValue("hasEndDate", $diJobEndDate);
		*/
		
		/*
		$property = SMWDIProperty::newFromUserLabel($propName);
		$dataValue = SMWDataValueFactory::newPropertyObjectValue($property, $value);
		*/
		
		//$data->addPropertyValue("_txt", new SMWDIString("some name"));
		
		//$page     = SMWWikiPageValue::makePage("Johnny", NS_MAIN);
		//$writer   = new SMWWriter( $page->getTitle() );
		$data      = new SMWSemanticData($subject);
		//$remove   = new SMWSemanticData($subject);
		//$property = SMWPropertyValue::makeUserProperty("hasID");
		$property = SMWDIProperty::newFromUserLabel("hasID");
		//$value    = SMWDataValueFactory::newPropertyObjectValue($property, "123431");
		$blob = new SMWDIBlob("1231413");
		$data->addPropertyObjectValue($property, $blob);
		//$writer->update( $remove, $add, "Adding Johnny's new wish" );
		
		//$db = new SMWSQLStore3();
		$db = smwfGetStore();
		$db->updateData($data);
		
		
		$retrieved = $db->getSemanticData($subject);
		$properties = $retrieved->getProperties();
		$out = "properties: " . count($properties) . "<br/>";
		$out = $out . "data:<br/>";
		foreach($retrieved->getPropertyValues($property) as $dataItem) {
			$out = "ajnsdkjns" . $out . $dataItem; // SMWDIBlob
		}
		$out = $out . "<br/>data2:<br/>";
		foreach($properties as $property) {
			$out = "getKey():" . $out . $property->getKey() . "\n"; // SMWDIProperty
			$out = "getSortKey():" . $out . $property->getSortKey() . "\n"; // SMWDIProperty
			$out = "getLabel():" . $out . $property->getLabel() . "\n"; // SMWDIProperty
		}
		$out = $out . "<br/><br/>subData:";
		$val = $retrieved->getSubSemanticData();
		if(isset($val)) {
			$out = $out . " (isset, count=" . count($val) . ")<br/>";
		} else {
			$out = $out . " (undefined)<br/>";
		}
		foreach($val as $subdata) {
			foreach($subdata-getPropertyValues($propertyURI) as $dataItem) {
				$out = $out . $dataItem->getHierpart();
			}
		}
		return $out;
	}
	public static function setParserFunctionHook($parser) {
		$parser->setFunctionHook( 'smwenrich', 'SMWEnrichMatchingResultManager::parserFunction' );
		return true;
	}
	public function parserFunction($parser) {
		/*
			{{#smwenrich: job=1072633873
			| print=info
			| id=ID
			| name=Name
			| description=Beschreibung
			| selection=Entitäten
			| linkGroup=Referenzlinks
			| dataSource=Datenquelle
			| progress=Fortschritt
			| startDate=Angefangen
			| endDate=Abgeschlossen
			| format=table
			}}
			
			{{#smwenrich: job=1072633873
			| print=result
			| format=table
			}}
		 */
		$namedArgs = array();
		foreach(func_get_args() as $i => $arg) {
			if($i > 0) { // skip $parser argument
				$mapping = explode('=', $arg, 2);
				if(count($mapping) == 2) {
					$namedArgs[trim($mapping[0])] = trim($mapping[1]);
				}
			}
		}
		// A job object is required for all tasks
		$job = false;
		if(is_numeric($namedArgs['job'])) {
			$job = SMWEnrichJobDB::getInstance()->getJobById($namedArgs['job']);
		} else {
			$job = SMWEnrichJobDB::getInstance()->getJobByName($namedArgs['job']);
		}
		if($job == false) {
			return "No job '" . $namedArgs['job'] . "' found\n";
		}
		// We have parsed user supplied arguments for this function,
		// have retrieved a job object from the database, so at this
		// point we are ready to do perform any action depending on
		// the arguments supplied.
		if($namedArgs['print']=='info') {
			return SMWEnrichMatchingResultManager::printJobHTML($job, $namedArgs);
		} else if($namedArgs['print']=='result') {
			return SMWEnrichMatchingResultManager::printResultHTML($job, $namedArgs);
		}
	}
	protected function printJobHTML(SMWEnrichJob $job, $args) {
		return "<table>".
			"<tr><td>" . $args['id'] . "</td><td>" . $job->getID() . "</td></tr>" .
			"<tr><td>" . $args['name'] . "</td><td>" . $job->getName() . "</td></tr>" .
			"<tr><td>" . $args['description'] . "</td><td>" . $job->getDescription() . "</td></tr>" .
			"<tr><td>" . $args['selection'] . "</td><td>" . $job->getSelectionId() . "</td></tr>" .
			"<tr><td>" . $args['linkGroup'] . "</td><td>" . $job->getLinkGroupId() . "</td></tr>" .
			"<tr><td>" . $args['dataSource'] . "</td><td>" . $job->getDataSourceId() . "</td></tr>" .
			"<tr><td>" . $args['progress'] . "</td><td>" . $job->getProgress() . "</td></tr>" .
			"<tr><td>" . $args['startDate'] . "</td><td>" . date(DATE_RSS, $job->getDateStarted()) . "</td></tr>" .
			"<tr><td>" . $args['endDate'] . "</td><td>" . date(DATE_RSS, $job->getDateFinished()) . "</td></tr>" .
			"</table>";
	}
	protected function printResultHTML(SMWEnrichJob $job, $args) {
		if(!$job->isFinished()) {
			return "Job '" . $job->getName() . "' has not finished, yet\n";
		}
		$result = SMWEnrichMatchingResultDB::getInstance()->getResult($job);
		$out = "<table><tr><th>local entity</th><th>similarity</th><th>external entity</th></tr>";
		foreach($result->getLinks() as $link) {
			$out = $out . "<tr><td>" .  $link->getEntity0() . "</td>" .
				"<td>" . $link->getMeasure() . "</td>" .
				"<td>" . $link->getEntity1() . "</td></tr>";
		}
		$out = $out . "</table>";
		return $out;
	}
	protected function printJobAnnotations(SMWEnrichJob $job) {
		// TODO add more information
		return '[[SMWEnrichJobID::' . $job->getID() .']]' . "\n" .
		 	'[[SMWEnrichJobName::' . $job->getName() . ']]' . "\n";
  }
  /*
   * The purpose of this function is to add semantic annotations
   * to each article that represents an entity which was part of the
   * matching process.
   */
  public function addArctileAnnotations(SMWEnrichMatchingResult $result) {
		foreach($result->getLinks() as $link) {
			// TODO: append information to an article which represents an entity
			$entity = parse_url($link->getEntity0());
			$title = Title::makeTitle(
				"SMWEnrich", // namespace
				basename($entity['path']), // title
				$entity['fragment'], // fragment
				""); // interwiki prefix
			$page = WikiPage::factory($title);
			$out = $page->getRawText();
			//$out = $out . $this->printJobAnnotations($result->getJob());
			$out = $out . "[[SMWEnrichMatch::" . $link->getEntity1() . "]]\n";
			$page->doEdit($out, "summary");
		}
		return true;
	}
}
?>
