<?php
// TODO: consistency check?

// check required software: cURL, PHP
// required PHP version for this extension is >5.3.10
// min required Virtuoso version is >6.1.3
if ( !defined( 'MEDIAWIKI' ) ) {
	echo 'To install this extension, put the following line in LocalSettings.php: require_once( "\$IP/extensions/SMWEnrich/SMWEnrich.php" );';
	exit( 1 );
}
if ( !defined( 'SMW_VERSION' ) ) {
	echo 'Semantic MediaWiki required';
	exit( 1 );
}
// configure the extension
$wgSMWEnrichDataDir = "/tmp/enrich"; // persistent data
$wgSMWEnrichTmpDir = $wgSMWEnrichDataDir . "/tmp"; // temporary data
// add namespaces
define("NS_SMWENRICH", 3333);
define("NS_ONTOLOGY_ALIGNMENT_FORMAT", 3333);
$wgExtraNamespaces[NS_SMWENRICH] = "SMWEnrich";
$wgExtraNamespaces[NS_ONTOLOGY_ALIGNMENT_FORMAT] = "OntologyAlignmentFormat";
$smwgNamespacesWithSemanticLinks += array( NS_SMWENRICH => true );
// the OntologyAlignmentFormat namespace does not need semantic
// functionality of SMW, see
// http://semantic-mediawiki.org/wiki/Help:$smwgNamespacesWithSemanticLinks
/* MediaWiki has it's own class loading mechanism,
 * so we are nice and are using that instead.
spl_autoload_register(function ($class) {
    include 'includes/' . $class . '.class.php';
});
*/
$wgAutoloadClasses['IOUtil'] = __DIR__ . '/includes/util/IOUtil.php';
$wgAutoloadClasses['NetUtil'] = __DIR__ . '/includes/util/NetUtil.php';
$wgAutoloadClasses['PropertiesUtil'] = __DIR__ . '/includes/util/PropertiesUtil.php';
$wgAutoloadClasses['SpecialSMWEnrich'] = __DIR__ . '/includes/SpecialSMWEnrich.php';
$wgAutoloadClasses['SMWEnrichJob'] = __DIR__ . '/includes/SMWEnrichJobs.php';
$wgAutoloadClasses['SMWEnrichJobDB'] = __DIR__ . '/includes/SMWEnrichJobs.php';
$wgAutoloadClasses['SMWEnrichJobManager'] = __DIR__ . '/includes/SMWEnrichJobs.php';
$wgAutoloadClasses['SMWEnrichJobsAPI'] = __DIR__ . '/includes/api/SMWEnrichAPIJobs.php';
$wgAutoloadClasses['SMWEnrichEntitySelection'] = __DIR__ . '/includes/SMWEnrichEntitySelection.php';
$wgAutoloadClasses['SMWEnrichEntitySelectionDB'] = __DIR__ . '/includes/SMWEnrichEntitySelection.php';
$wgAutoloadClasses['SMWEnrichEntitySelectionManager'] = __DIR__ . '/includes/SMWEnrichEntitySelection.php';
$wgAutoloadClasses['SMWEnrichEntitySelectionAPI'] = __DIR__ . '/includes/api/SMWEnrichAPIEntitySelection.php';
$wgAutoloadClasses['SMWEnrichReferenceLink'] = __DIR__ . '/includes/SMWEnrichReferenceLinks.php';
$wgAutoloadClasses['SMWEnrichReferenceLinkGroup'] = __DIR__ . '/includes/SMWEnrichReferenceLinks.php';
$wgAutoloadClasses['SMWEnrichReferenceLinksDB'] = __DIR__ . '/includes/SMWEnrichReferenceLinks.php';
$wgAutoloadClasses['SMWEnrichReferenceLinksAPI'] = __DIR__ . '/includes/api/SMWEnrichAPIReferenceLinks.php';
$wgAutoloadClasses['SMWEnrichDataSource'] = __DIR__ . '/includes/SMWEnrichDataSources.php';
$wgAutoloadClasses['SMWEnrichDataSourceDB'] = __DIR__ . '/includes/SMWEnrichDataSources.php';
$wgAutoloadClasses['SMWEnrichDataSourceManager'] = __DIR__ . '/includes/SMWEnrichDataSources.php';
$wgAutoloadClasses['SMWEnrichDataSourcesAPI'] = __DIR__ . '/includes/api/SMWEnrichAPIDataSources.php';
$wgAutoloadClasses['SMWEnrichMatchingResult'] = __DIR__ . '/includes/SMWEnrichMatchingResults.php';
$wgAutoloadClasses['SMWEnrichMatchingResultDB'] = __DIR__ . '/includes/SMWEnrichMatchingResults.php';
$wgAutoloadClasses['SMWEnrichMatchingResultManager'] = __DIR__ . '/includes/SMWEnrichMatchingResults.php';
$wgAutoloadClasses['SMWEnrichEntityMatchingResultAPI'] = __DIR__ . '/includes/api/SMWEnrichAPIEntityMatchingResults.php';
$wgAutoloadClasses['SMWExportToFileController'] = __DIR__ . '/includes/util/SMWExportToFileController.php';
$wgAutoloadClasses['SMWEnrichEntitySelectionExportMultipleJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgAutoloadClasses['SMWEnrichLinkSpecificationLearingJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgAutoloadClasses['SMWEnrichEndpointManager'] = __DIR__ . '/includes/io/SMWEnrichEndpointManager.php';
$wgAutoloadClasses['SMWEnrichEndpointManagerRegistry'] = __DIR__ . '/includes/io/SMWEnrichEndpointManager.php';
$wgAutoloadClasses['SMWEnrichVirtuosoEndpointManager'] = __DIR__ . '/includes/io/SMWEnrichEndpointManager.php';
// Ontology Alignment Format
$wgAutoloadClasses['Ontology'] = __DIR__ . '/includes/io/OntologyAlignmentFormat.php';
$wgAutoloadClasses['Cell'] = __DIR__ . '/includes/io/OntologyAlignmentFormat.php';
$wgAutoloadClasses['OntologyAlignment'] = __DIR__ . '/includes/io/OntologyAlignmentFormat.php';
$wgAutoloadClasses['OntologyAlignmentWriter'] = __DIR__ . '/includes/io/OntologyAlignmentFormat.php';
$wgAutoloadClasses['OntologyAlignmentXMLWriter'] = __DIR__ . '/includes/io/OntologyAlignmentFormat.php';
// Frameworks
$wgAutoloadClasses['ERFrameworkConfigBase'] = __DIR__ . '/includes/frameworks/ERFrameworkConfigWriter.php';
$wgAutoloadClasses['ERFrameworkConfigWriter'] = __DIR__ . '/includes/frameworks/ERFrameworkConfigWriter.php';
$wgAutoloadClasses['ERFramework'] = __DIR__ . '/includes/frameworks/ERFrameworks.php';
$wgAutoloadClasses['ERFrameworkBase'] = __DIR__ . '/includes/frameworks/ERFrameworks.php';
$wgAutoloadClasses['ERFrameworkRegistry'] = __DIR__ . '/includes/frameworks/ERFrameworks.php';
// LIMES
$wgAutoloadClasses['LinkFile'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMESConfig.php';
$wgAutoloadClasses['Prefix'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMESConfig.php';
$wgAutoloadClasses['KnowledgeBase'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMESConfig.php';
$wgAutoloadClasses['LIMESConfig'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMESConfig.php';
$wgAutoloadClasses['LIMESConfigWriter'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMESConfigWriter.php';
$wgAutoloadClasses['ERFrameworkLIMES'] = __DIR__ . '/includes/frameworks/limes/ERFrameworkLIMES.php';
// Jobs for the MediaWiki JobQueue, Special Pages, API Modules and Hooks
$wgAutoloadClasses['SMWEnrichMatchingJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgAutoloadClasses['SMWEnrichDownloadJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgAutoloadClasses['SMWEnrichCreateEndpointJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgAutoloadClasses['SMWEnrichEntitySelectionExportMultipleJob'] = __DIR__ . '/includes/queue/SMWEnrichBackgroundJobs.php';
$wgJobClasses['smwenrichdownload'] = 'SMWEnrichDownloadJob';
$wgJobClasses['smwenrichmatchingjob'] = 'SMWEnrichMatchingJob';
$wgJobClasses['smwenrichfetchandloadrdf'] = 'SMWEnrichCreateEndpointJob';
$wgJobClasses['smwenrichentityselectionexport'] = 'SMWEnrichEntitySelectionExportMultipleJob';
$wgSpecialPages['SMWEnrich'] = 'SpecialSMWEnrich';
$wgSpecialPageGroups['SMWEnrich'] = 'pagetools';
$wgAPIModules['smwenrichjobs'] = 'SMWEnrichJobsAPI';
$wgAPIModules['smwenrichentities'] = 'SMWEnrichEntitySelectionAPI';
$wgAPIModules['smwenrichlinks'] = 'SMWEnrichReferenceLinksAPI';
$wgAPIModules['smwenrichdatasources'] = 'SMWEnrichDataSourcesAPI';
$wgAPIModules['smwenrichmatchingresults'] = 'SMWEnrichEntityMatchingResultAPI';
$wgHooks['ParserFirstCallInit'][] = 'SMWEnrichMatchingResultManager::setParserFunctionHook';
$wgHooks['smwInitProperties'][] = 'PropertiesUtil::registerProperties';
$wgExtensionMessagesFiles['SMWEnrich'] = __DIR__ . '/SMWEnrich.i18n.php';
$wgResourceModules['SMWEnrich'] = array(
	'scripts' => array('modules/SMWEnrich.js'),
	'styles' => array('modules/SMWEnrich.css'),
	'messages' => array(),
	'dependencies' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'smwEnrich'
);
// show some information in the credits section
$wgExtensionCredits[defined( 'SEMANTIC_EXTENSION_TYPE' ) ? 'semantic' : 'other'][] = array(
    'path' => __FILE__,
    'name' => 'SMWEnrich',
    'author' => 'Jan Novacek', 
    'url' => 'https://www.mediawiki.org/wiki/Extension:Example', 
    'description' => 'Enrich your data with external sources',
    'version'  => 1.0,
    'license-name' => "GPL"   // Short name of the license, links LICENSE or COPYING file if existing - string, added in 1.23.0
);
?>
