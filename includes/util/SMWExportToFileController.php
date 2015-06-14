<?php
/*
 * The implementation of SMWExportController in SMW 1.8.0.5 does
 * not allow to specify an output file when printing only pages,
 * instead of the whole wiki content.
 */
class SMWExportToFileController extends SMWExportController {
	public function __construct(SMWSerializer $serializer, $enable_backlinks = false) {
		parent::__construct($serializer, $enable_backlinks);
	}
	public function printPagesToFile($file, $pages, $recursion = 1, $revisiondate = false  ) {
		$this->outputfile = $file;
		$this->printPages($pages, $recursion, $revisiondate);
	}
}
?>
