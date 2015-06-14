<?php
class LIMESConfig extends ERFrameworkConfigBase {
	protected $prefixes = array();
	protected $source;
	protected $target;
	protected $metric;
	protected $acceptance;
	protected $review;
	protected $execution = "SIMPLE";
	protected $output = "N3";
	public function __construct(Knowledgebase $source, Knowledgebase $target,
			LinkFile $acceptance, LinkFile $review) {
		$this->source = $source;
		$this->target = $target;
		$this->acceptance = $acceptance;
		$this->review = $review;
		$this->addDefaultPrefixes();
	}
	public function getPrefixes() {
		return $this->prefixes;
	}
	public function setPrefixes($prefixes) {
		$this->prefixes = $prefixes;
	}
	public function addPrefix($namespace, $label) {
		$this->prefixes[] = new Prefix($namespace, $label);
	}
	protected function addDefaultPrefixes() {
		$this->addPrefix("http://www.w3.org/1999/02/22-rdf-syntax-ns#", "rdf");
		$this->addPrefix("http://www.w3.org/2000/01/rdf-schema#", "rdfs");
		$this->addPrefix("http://www.w3.org/2002/07/owl#", "owl");
		// TODO more prefixes
	}
	public function getSource() {
		return $this->source;
	}
	public function setSource(Knowledgebase $source) {
		$this->source = $source;
	}
	public function getTarget() {
		return $this->target;
	}
	public function setTarget($target) {
		$this->target = $target;
	}
	public function getMetric() {
		return $this->metric;
	}
	public function setMetric($metric) {
		$this->metric = $metric;
	}
	public function getAcceptance() {
		return $this->acceptance;
	}
	public function setAcceptance(LinkFile $acceptance) {
		$this->acceptance = $acceptance;
	}
	public function getReview() {
		return $this->review;
	}
	public function setReview(LinkFile $review) {
		$this->review = $review;
	}
	public function getExecution() {
		return $this->execution;
	}
	public function setExecution($execution) {
		$this->execution = $execution;
	}
	public function getOutput() {
		return $this->output;
	}
	public function setOutput($output) {
		$this->output;
	}
}
class Prefix {
	protected $nspace = null;
	protected $label = null;
	public function __construct($namespace, $label) {
		$this->nspace = $namespace;
		$this->label = $label;
	}
	public function getNamespace() {
		return $this->nspace;
	}
	public function setNamespace($namespace) {
		$this->nspace = $namespace;
	}
	public function getLabel() {
		return $this->label;
	}
	public function setLabel($label) {
		$this->label = $label;
	}
}
class Knowledgebase {
	protected $id;
	protected $endpoint;
	protected $type = "SPARQL"; // as of LIMES 0.6: SPARQL or N3
	protected $variable; // used in conjunction with restriction
	protected $pagesize;
	protected $restriction;
	protected $properties;
	public function __construct($id, $endpoint) {
		$this->id = $id;
		$this->endpoint = $endpoint;
		$this->properties = array();
	}
	public function getID() {
		return $this->id;
	}
	public function setID($id) {
		$this->id = $id;
	}
	public function getEndpoint() {
		return $this->endpoint;
	}
	public function setEndpoint($url) {
		$this->url = $url;
	}
	public function getType() {
		return $this->type;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getVariable() {
		return $this->variable;
	}
	public function setVariable($variable) {
		$this->variable = $variable;
	}
	public function getPagesize() {
		return $this->pagesize;
	}
	public function setPagesize($pagesize) {
		$this->pagesize = $pagesize;
	}
	public function getRestriction() {
		return $this->restriction;
	}
	public function setRestriction($restriction) {
		$this->restriction = $restriction;
	}
	public function setProperties($properties) {
		$this->properties = $properties;
	}
	public function getProperties() {
		return $this->properties;
	}
	public function addProperty($property) {
		$this->properties[] = $property;
	}
}
class LinkFile {
	protected $threshold = null;
	protected $fileName = null;
	protected $relation = "owl:sameAs";
	public function __construct($threshold, $fileName) {
		$this->threshold = $threshold;
		$this->fileName = $fileName;
	}
	public function getThreshold() {
		return $this->threshold;
	}
	public function setThreshold($threshold) {
		$this->threshold = $threshold;
	}
	public function getFileName() {
		return $this->fileName;
	}
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}
	public function getRelation() {
		return $this->relation;
	}
}
?>
