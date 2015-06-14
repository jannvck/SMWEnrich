<?php

/*
 * This file contains classes for representing ontology alignments
 * and for reading/writing in the Ontology Alignment Format.
 *
 * see http://alignapi.gforge.inria.fr/format.html
 *
 */
 
//namespace OntologyAlignmentFormat; // TODO
class Ontology {
	protected $uri;
	protected $url;
	protected $lang;
	public function __construct($ontologyURI, $ontologyURL, $ontologyLanguageURI) {
		$this->uri = $ontologyURI;
		$this->url = $ontologyURL;
		$this->lang = $ontologyLanguageURI;
	}
	public function setURI($ontologyURI) {
		$this->uri = $ontologyURI;
	}
	public function getURI() {
		return $this->uri;
	}
	public function setURL($ontologyURL) {
		$this->url = $ontologyURL;
	}
	public function getURL() {
		return $this->url;
	}
	public function ontology() {
		return $this->url;
	}
	public function setLanguage($ontologyLanguage) {
		$this->lang = $ontologyLanguage;
	}
	public function getLanguage() {
		return $this->lang;
	}
}
class Cell {
	protected $id; // mapping identifier is optional
	protected $entities;
	protected $relation;
	protected $measure;
	public function __construct($id, $entity1, $entity2, $relation, $measure) {
		$this->id = $id;
		$this->entities = array();
		$this->entities[0] = $entity1;
		$this->entities[1] = $entity2;
		$this->relation = $relation;
		$this->measure = $measure;
	}
	public function setID($id) {
		$this->id = $id;
	}
	public function getID() {
		return $this->id;
	}
	public function setEntities(Array $entityURIs) {
		$this->entities = $entityURIs;
	}
	public function getEntities() {
		return $this->entities;
	}
	public function setEntity0($entity) {
		$this->entities[0] = $entity;
	}
	public function getEntity0() {
		return $this->entities[0];
	}
	public function setEntity1($entity) {
		$this->entities[1] = $entity;
	}
	public function getEntity1() {
		return $this->entities[1];
	}
	public function setRelation($relation) {
		$this->relation = $relation;
	}
	public function getRelation() {
		return $this->relation;
	}
	public function setMeasure($measure) {
		$this->measure = $measure;
	}
	public function getMeasure() {
		return $this->measure;
	}
}
class OntologyAlignment {
	protected $ontologies;
	protected $isXML;
	protected $level;
	protected $type;
	protected $mappings;
	public function __construct() {
		$this->ontologies = array();
		$this->mappings = array();
	}
	public function setIsXML($isXML) {
		if(!($isXML=="yes" || $isXML=="no")) {
			//throw new \IllegalArgumentException('the "XML" element can either ' .
			//	'contain "yes" or "no"');
		}
		$this->isXML = $isXML;
	}
	public function getIsXML() {
		return $this->isXML;
	}
	public function setLevel($level) {
		$this->level = $level;
	}
	public function getLevel() {
		return $this->level;
	}
	public function setType($type) {
		$this->type = $type;
	}
	public function getType() {
		return $this->type;
	}
	public function addOntology(Ontology $ontology) {
		if(count($this->ontologies) > 2) {
			throw new \RuntimeException('Only 2 ontologies per alignment allowed');
		}
		$this->ontologies[(string) $ontology->getURI()] = $ontology;
	}
	public function getOntologies() {
		return $this->ontologies;
	}
	public function addMapping(Cell $cell) {
		$id = $cell->getID();
		if(!empty($id) && isset($this->mappings[$cell->getID()])) {
			throw new \RuntimeException(
				'Alignment already contains mapping with ID=' . $cell->getID());
		}
		$this->mappings[] = $cell;
	}
	public function getMappings() {
		return $this->mappings;
	}
}
interface OntologyAlignmentWriter {
	function write(OntologyAlignment $alignment);
}
// simple in-memory based XML writer
class OntologyAlignmentXMLWriter implements OntologyAlignmentWriter {
	public function write(OntologyAlignment $alignment) {
		$doc = new \DOMDocument();
		$elemAlign = $doc->createElement('Alignment');
		$elemXML = $doc->createElement('xml');
		$nodeTextXML = $doc->createTextNode($alignment->getIsXML());
		$elemLevel = $doc->createElement('level');
		$nodeTextLevel = $doc->createTextNode($alignment->getLevel());
		$elemType = $doc->createElement('type');
		$nodeTextType = $doc->createTextNode($alignment->getType());
		$elemXML->appendChild($nodeTextXML);
		$elemLevel->appendChild($nodeTextLevel);
		$elemType->appendChild($nodeTextType);
		$elemAlign->appendChild($elemXML);
		$elemAlign->appendChild($elemLevel);
		$elemAlign->appendChild($elemType);
		$this->addOntologies($doc, $elemAlign, $alignment->getOntologies());
		$this->addMappings($doc, $elemAlign, $alignment->getMappings());
		$this->addRDF($doc, $elemAlign);
		$doc->formatOutput = true;
		return $doc->saveXML();
	}
	protected function addRDF($doc, $content) {
		$elemRDF = $doc->createElement('rdf:RDF');
		$doc->appendChild($elemRDF);
		$attrXMLNS = $doc->createAttribute("xmlns");
		$attrXMLNS->value = "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#";
		$elemRDF->appendChild($attrXMLNS);
		$attrRDF = $doc->createAttribute("xmlns:rdf");
		$attrRDF->value = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
		$elemRDF->appendChild($attrRDF);
		$attrXSD = $doc->createAttribute("xmlns:xsd");
		$attrXSD->value = "http://www.w3.org/2001/XMLSchema#";
		$elemRDF->appendChild($attrXSD);
		$attrAlign = $doc->createAttribute("xmlns:align");
		$attrAlign->value = "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#";
		$elemRDF->appendChild($attrAlign);
		$elemRDF->appendChild($content);
	}
	protected function addOntologies($doc, $elem, $ontologies) {
		foreach($ontologies as $ontology) {
			$elemOntology = $doc->createElement('Ontology');
			$elemOntology->setAttribute('rdf:about', $ontology->getURI());
			$elemLocation = $doc->createElement('location');
			$nodeTextLocation = $doc->createTextNode($ontology->getURL());
			$elemFormalism = $doc->createElement('formalism');
			$elemFormalismNested = $doc->createElement('Formalism');
			$elemFormalismNested->setAttribute('align:uri', $ontology->getLanguage());
			$elemLocation->appendChild($nodeTextLocation);
			$elemFormalism->appendChild($elemFormalismNested);
			$elemOntology->appendChild($elemLocation);
			$elemOntology->appendChild($elemFormalism);
			$elem->appendChild($elemOntology);
		}
	}
	protected function addMappings($doc, $elem, $mappings) {
		foreach($mappings as $mapping) {
			$entities = $mapping-> getEntities();
			$elemMapping = $doc->createElement('map');
			$elemCell = $doc->createElement('Cell');
			$elemEntity1 = $doc->createElement('entity1');
			$elemEntity1->setAttribute('rdf:resource', $entities[0]);
			$nodeTextEntity1 = $doc->createTextNode($entities[0]);
			$elemEntity2 = $doc->createElement('entity2');
			$elemEntity2->setAttribute('rdf:resource', $entities[1]);
			$nodeTextEntity2 = $doc->createTextNode($entities[1]);
			$elemRelation = $doc->createElement('relation');
			$nodeTextRelation = $doc->createTextNode($mapping->getRelation());
			$elemMeasure = $doc->createElement('measure');
			$elemMeasure->setAttribute('rdf:datatype', 'http://www.w3.org/2001/XMLSchema#float');
			$nodeTextMeasure = $doc->createTextNode($mapping->getMeasure());
			$elemMapping->appendChild($elemCell);
			$elemCell->appendChild($elemEntity1);
			$elemCell->appendChild($elemEntity2);
			$elemRelation->appendChild($nodeTextRelation);
			$elemCell->appendChild($elemRelation);
			$elemMeasure->appendChild($nodeTextMeasure);
			$elemCell->appendChild($elemMeasure);
			$elem->appendChild($elemMapping);
		}
	}
	public static function writeReferenceLinks($path, $links) {
		$alignment = new OntologyAlignment();
		$alignment->setIsXML('yes');
		$alignment->setLevel('0');
		$alignment->setType('**');
		$alignment->addOntology(new Ontology('some1','crazy1','lang'));
		$alignment->addOntology(new Ontology('some2','crazy2','lang'));
		foreach($links as $link) {
			$alignment->addMapping(
				new Cell(
					$link->getLinkID(),
					$link->getURI0(),
					$link->getURI1(),
					"=",
					1.0)); // This is a reference link, so similarity measure is 1.0
		}
		$writer = new OntologyAlignmentXMLWriter();
		$ioUtil = IOUtil::getInstance();
		return $ioUtil->fwrite($path, $writer->write($alignment));
	}
}
interface OntologyAlignmentReader {
	function read($xml);
}
class OntologyAlignmentXMLReader implements OntologyAlignmentReader {
	public function readFile($path) {
		$this->read(simplexml_load_file($path));
	}
	public function read($in) {
		if(!($in instanceof SimpleXMLElement)) {
			$xml =  new SimpleXMLElement($in);
		}
		$xml->registerXPathNamespace("def", "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#");
		$xml->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
		$alignment = new OntologyAlignment();
		$alignment->setIsXML($xml->Alignment->xml);
		$alignment->setLevel($xml->Alignment->level);
		$alignment->setType($xml->Alignment->type);
		foreach($xml->xpath('//def:Alignment/def:Ontology') as $xmlOntology) {
			$alignOntology = new Ontology(null, null, null);
			$xmlOntology->registerXPathNamespace("def", "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#");
			$xmlOntology->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			foreach($xmlOntology->xpath('attribute::rdf:about') as $attr) {
				$alignOntology->setURI($attr[0]);
			}
			foreach($xmlOntology->xpath('child::def:location') as $location) {
				$alignOntology->setURL($location);
			}
			foreach($xmlOntology->xpath('child::def:formalism/def:Formalism') as $formalism) {
				$xmlOntology->registerXPathNamespace("align", "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#");
				foreach($formalism->xpath('attribute::align:uri') as $uri) {
					$alignOntology->setLanguage($uri);
				}
			}
			$alignment->addOntology($alignOntology);
		}
		foreach($xml->xpath('//def:Alignment/def:map/def:Cell') as $xmlCell) {
			$alignCell = new Cell(null, null, null, null, null);
			$xmlCell->registerXPathNamespace("def", "http://knowledgeweb.semanticweb.org/heterogeneity/alignment#");
			$xmlCell->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			foreach($xmlCell->xpath('attribute::rdf:about') as $attr) {
				$alignCell->setID($attr[0]);
			}
			foreach($xmlCell->xpath('child::def:entity1') as $entity1) {
				$entity1->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$resource = $entity1->xpath('attribute::rdf:resource');
				$alignCell->setEntity0($resource[0]);
			}
			foreach($xmlCell->xpath('child::def:entity2') as $entity2) {
				$entity2->registerXPathNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
				$resource = $entity2->xpath('attribute::rdf:resource');
				$alignCell->setEntity1($resource[0]);
			}
			foreach($xmlCell->xpath('child::def:relation') as $relation) {
				$alignCell->setRelation($relation);
			}
			foreach($xmlCell->xpath('child::def:measure') as $measure) {
				$alignCell->setMeasure($measure);
			}
			$alignment->addMapping($alignCell);
		}
		return $alignment;
	}
}
/*
$alignment = new OntologyAlignment();
$alignment->setIsXML('yes');
$alignment->setLevel('0');
$alignment->setType('**');
$alignment->addOntology(new Ontology('some','crazy','lang'));
$alignment->addOntology(new Ontology('some2','crazy2','lang'));
$alignment->addMapping(new Cell('somemapping1','entity1URI','entity2URI','=','1.0'));
$alignment->addMapping(new Cell('somemapping2','entity3URI','entity4URI','=','1.0'));
$alignment->addMapping(new Cell('somemapping3','entity5URI','entity6URI','=','1.0'));
$writer = new OntologyAlignmentXMLWriter();
//echo $writer->write($alignment);

$reader = new OntologyAlignmentXMLReader();
//$alignment = $reader->read("/tmp/test/jobs/609319367/alignment.xml");
$alignment = $reader->read($writer->write($alignment));
echo "type=" . $alignment->getType() . "\n";
echo "level=" . $alignment->getLevel() . "\n";
echo "xml=" . $alignment->getIsXML() . "\n";
echo "parsed " . count($alignment->getOntologies()) . " ontologies\n";
foreach($alignment->getOntologies() as $ontology) {
	echo "ontology: " . $ontology->getURI() . ", " . $ontology->getURL() . ", " . $ontology->getLanguage() . "\n";
}
foreach($alignment->getMappings() as $mapping) {
	echo "mapping: " . $mapping->getEntity0() . "->" . $mapping->getEntity1() . "\n";
}
*/
?>
