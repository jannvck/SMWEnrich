<?php
class LIMESConfigWriter implements ERFrameworkConfigWriter {
	public function write(ERFrameworkConfigBase $config) {
		$dom = new DOMImplementation();
		$dtd = $dom->createDocumentType("LIMES", "", "limes.dtd"); 
		$doc = $dom->createDocument(null, null, $dtd);
		//$doc->xmlVersion="1.0";
		//$doc->xmlEncoding="UTF-8";
		$elemLIMES = $doc->createElement('LIMES');
		$prefixes = $config->getPrefixes();
		if(is_array($prefixes)) {
			foreach($config->getPrefixes() as $prefix) {
				$this->addPrefix($prefix, $elemLIMES, $doc);
			}
		}
		$this->addSource($config->getSource(), $elemLIMES, $doc);
		$this->addTarget($config->getTarget(), $elemLIMES, $doc);
		$elemMetric = $doc->createElement('METRIC');
		$nodeTextMetric = $doc->createTextNode($config->getMetric());
		$elemMetric->appendChild($nodeTextMetric);
		$elemLIMES->appendChild($elemMetric);
		$this->addAcceptance($config->getAcceptance(), $elemLIMES, $doc);
		$this->addReview($config->getReview(), $elemLIMES, $doc);
		$elemExecution = $doc->createElement('EXECUTION');
		$nodeTextExecution = $doc->createTextNode($config->getExecution());
		$elemLIMES->appendChild($elemExecution);
		$elemOutput = $doc->createElement('OUTPUT');
		$nodeTextOutput = $doc->createTextNode($config->getOutput());
		$elemLIMES->appendChild($elemOutput);
		$doc->appendChild($elemLIMES);
		$doc->formatOutput = true;
		return $doc->saveXML();
	}
	protected function addSource(Knowledgebase $source, DOMElement $elem, DOMDocument $doc) {
		$elemSource = $doc->createElement('SOURCE');    
		$this->addKnowledgeBase($source, $elemSource, $doc);
		$elem->appendChild($elemSource);
	}
	protected function addTarget(Knowledgebase $target, DOMElement $elem, DOMDocument $doc) {
		$elemTarget = $doc->createElement('TARGET');
		$this->addKnowledgeBase($target, $elemTarget, $doc);
		$elem->appendChild($elemTarget);
	}
	private function addKnowledgeBase(Knowledgebase $knowledgeBase, DOMElement $elem, DOMDocument $doc) {
		$elemID = $doc->createElement('ID');
		$nodeTextID = $doc->createTextNode($knowledgeBase->getID());
		$elemEndpoint = $doc->createElement('ENDPOINT');
		$nodeTextEndpoint = $doc->createTextNode($knowledgeBase->getEndpoint());
		$elemVariable = $doc->createElement('VAR');
		$nodeTextVariable = $doc->createTextNode($knowledgeBase->getVariable());
		$elemPagesize = $doc->createElement('PAGESIZE');
		$nodeTextPagesize = $doc->createTextNode($knowledgeBase->getPagesize());
		$elemType = $doc->createElement('TYPE');
		$nodeTextType = $doc->createTextNode($knowledgeBase->getType());
		$elemRestriction = $doc->createElement('RESTRICTION');
		$nodeTextRestriction = $doc->createTextNode($knowledgeBase->getRestriction());
		$elemID->appendChild($nodeTextID);
		$elemEndpoint->appendChild($nodeTextEndpoint);
		$elemVariable->appendChild($nodeTextVariable);
		$elemPagesize->appendChild($nodeTextPagesize);
		$elemType->appendChild($nodeTextType);
		$elemRestriction->appendChild($nodeTextRestriction);
		$elem->appendChild($elemID);
		$elem->appendChild($elemEndpoint);
		$elem->appendChild($elemVariable);
		$elem->appendChild($elemPagesize);
		$elem->appendChild($elemRestriction);
		$properties = $knowledgeBase->getProperties();
		if(isset($properties)) {
			foreach($properties as $property) {
				$elemProperty = $doc->createElement('PROPERTY');
				$nodeTextProperty = $doc->createTextNode($property);
				$elemProperty->appendChild($nodeTextProperty);
				$elem->appendChild($elemProperty);
			}
		}
		$elem->appendChild($elemType);
	}
	protected function addPrefix(Prefix $prefix, DOMElement $elem, DOMDocument $doc) {
		$elemPrefix = $doc->createElement('PREFIX');
		$elemPrefixNamespace = $doc->createElement('NAMESPACE');
		$nodeTextNamespace = $doc->createTextNode($prefix->getNamespace());
		$elemPrefixLabel = $doc->createElement('LABEL');
		$nodeTextLabel = $doc->createTextNode($prefix->getLabel());
		$elemPrefixNamespace->appendChild($nodeTextNamespace);
		$elemPrefixLabel->appendChild($nodeTextLabel);
		$elemPrefix->appendChild($elemPrefixNamespace);
		$elemPrefix->appendChild($elemPrefixLabel);
		$elem->appendChild($elemPrefix);
	}
	protected function addAcceptance(LinkFile $acceptance, DOMElement $elem, DOMDocument $doc) {
		$elemAcceptance = $doc->createElement('ACCEPTANCE');
		$this->addLinkFile($acceptance, $elemAcceptance, $doc);
		$elem->appendChild($elemAcceptance);
	}
	protected function addReview(LinkFile $review, DOMElement $elem, DOMDocument $doc) {
		$elemReview = $doc->createElement('REVIEW');
		$this->addLinkFile($review, $elemReview, $doc);
		$elem->appendChild($elemReview);
	}
	private function addLinkFile(LinkFile $linkFile, DOMElement $elem, DOMDocument $doc) {
		$elemThreshold = $doc->createElement('THRESHOLD');
		$nodeTextThreshold = $doc->createTextNode($linkFile->getThreshold());
		$elemFile = $doc->createElement('FILE');
		$nodeTextFile = $doc->createTextNode($linkFile->getFileName());
		$elemRelation = $doc->createElement('RELATION');
		$nodeTextRelation = $doc->createTextNode($linkFile->getRelation());
		$elemThreshold->appendChild($nodeTextThreshold);
		$elemFile->appendChild($nodeTextFile);
		$elemRelation->appendChild($nodeTextRelation);
		$elem->appendChild($elemThreshold);
		$elem->appendChild($elemFile);
		$elem->appendChild($elemRelation);
	}
}

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
echo "global: " . $target->getProperties() . "\n";
$config = new LIMESConfig($source, $target, new LinkFile(0.98, "accepted.links"), new LinkFile(0.95, "review.links"));
$config->addPrefix("http://www.okkam.org/ontology_person1.owl#", "okkamperson1");
$config->addPrefix("http://www.okkam.org/ontology_person2.owl#", "okkamperson2");
$config->setMetric("levenshtein(x.http://www.okkam.org/ontology_person1.owl#surname, y.http://www.okkam.org/ontology_person2.owl#surname)");
$writer = new LIMESConfigWriter();
echo $writer->write($config);
*/

?>
