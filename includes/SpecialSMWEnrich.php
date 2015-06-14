<?php
	class SpecialSMWEnrich extends SpecialPage {
		function __construct() {
			parent::__construct( 'SMWEnrich' );
		}
		function execute( $par ) {
			$request = $this->getRequest();
			$output = $this->getOutput();
			$output->addModules( 'SMWEnrich' );
			$this->setHeaders();
			$query = $request->getQueryValues();
			
			# Get request data from, e.g.
			#$param = $request->getText( 'procId' );
			
			# View or modify an existing matching job
			/*
			if (isset($query['post'])) {
				$wikitext = 'woohooo';
			} else if (isset($query['job'])) {
				$jobId = $query['job'];
				$db = wfGetDB( DB_SLAVE );
				$res = $db->select(
					'smw_enrich_jobs', // table
					array('job_id'), // columns
					'', // conditions
					__METHOD__,
					array() // options
				);
				foreach($res as $row) {
					$wikitext = 'shit';
				}
				$wikitext = 'this job is not running';
			} else { # display running matching jobs or start new matching job
				$wikitext = '<h2>Current running jobs:</h2>';
				$jobControl = SMWEnrichJobDB::getInstance();
				foreach($jobControl->getJobs('') as $job) {
					$wikitext = $wikitext . $job->getName() . ', ' . $job->getDescription() . '<br/>';
				}
			}
			*/
			
			if (isset($query['browse'])) {
				//$smwb = new SWBSpecialBrowseSW();
				//$smwb->displayBrowse();
			} else {
			/*
			$wikitext = $wikitext . '<iframe src="/smw-cora/index.php/Special:BrowseSW?article=Barbara">' .
				'content here, when iframe cannot be displayed' .
				'</iframe>';
				*/
			$wikitext = $wikitext . file_get_contents(__DIR__ . '/SMWEnrichContent.html');
			
			}
			
			# use §wgOut for output, not plain echo
			# http://www.mediawiki.org/wiki/Manual:Special_pages#General_Information
			$output->addHTML($wikitext);
			//$this->querySMW();
		}
		protected function querySMW() {
			$provider[] = array(
                         array( 'query' => array(
                                 '[[Modification date::+]]',
                                 '?Modification date',
                                 'limit=10'
                                 )
                         ),
                         array(
                                 array(
                                         'label'=> '',
                                         'typeid' => '_wpg',
                                         'mode' => 2,
                                         'format' => false
                                 ),
                                 array(
                                         'label'=> 'Modification date',
                                         'typeid' => '_dat',
                                         'mode' => 1,
                                         'format' => ''
                                 )
                         )
                 );
			$rawParams = "";
			list( $queryString, $parameters, $printouts ) = SMWQueryProcessor::getComponentsFromFunctionParams( $provider[0], false );
			SMWQueryProcessor::addThisPrintout( $printouts, $parameters );
			$parameters = SMWQueryProcessor::getProcessedParams( $parameters, $printouts );
			$query = SMWQueryProcessor::createQuery(
				$queryString,
				$parameters,
				SMWQueryProcessor::SPECIAL_PAGE,
				'',
				$printouts);
			$result = StoreFactory::getStore()->getQueryResult($query);
			$output->addHTML("here it is: " . $result->toArray());
		}
	}
?>
