<?php
class NetUtil {
	static private $instance = null;
	protected $active = array();
	private function __construct() {}
	static public function getInstance() {
		if(self::$instance == null) {
			self::$instance = new NetUtil();
		}
		return self::$instance;
	}
	/*
	 * cURL will be used to download files. Data will be
	 * written into a temporary file, which will be renamed
	 * to the correct download filename afterwards.
	 *
	 * Interrupted downloads will be resumed automatically
	 * but beware that the server may or may not support
	 * resuming downloads, so always verify the file size
	 * with the hasCorrectSize() function after a download
	 * has finished.
	 * If a download was completed and start() is called
	 * again anyway, then the existing file will be corrupted,
	 * as the download will try to resume, but will fail.
	 */
	public function download($url, $directory) {
		if(isset($this->active[$url])) {
				return $this->active[$url];
		} else {
			$download = new Download($url);
			$download->setBaseDirectory($directory);
			return $download;
		}
	}
	protected function getActiveDownloads() {
		return $this->active;
	}
}
class Download {
	private $url = null;
	private $progress = null;
	private $baseDirectory = null;
	private $canceled = false;
	public function __construct($url) {
		$this->url = $url;
	}
	public function getURL() {
		return $this->url;
	}
	public function setURL($url) {
		$this->url = $url;
	}
	public function getDirectory() {
		return $this->getDownloadDirectoryIn($this->getBaseDirectory());
	}
	public function getBaseDirectory() {
		return $this->baseDirectory;
	}
	public function setBaseDirectory($baseDirectory) {
		$this->baseDirectory = $baseDirectory;
	}
	public function getProgress() {
		return $this->progress;
	}
	protected function setProgress($downloadSize, $downloaded, $uploadSize, $uploaded) {
		$this->progress = array(
			"size" => $downloadSize,
			"downloaded" => $downloaded);
		return $this->canceled;
	}
	public function cancel() {
		$this->canceled = true;
	}
	protected function resumeFrom() {
		$fileNames = $this->getFileNames();
		if(!empty($fileNames)) {
			// this class creates only a single file per directory
			$downloadedFileName = $fileNames[0];
			if(file_exists($downloadedFileName) && !is_dir($downloadedFileName)) {
				return filesize($downloadedFileName);
			}
			return false;
		}
	}
	public function hasCorrectSize() {
		$progress = $this->getProgress();
		$fileNames = $this->getFileNames();
		if(empty($fileNames) || empty($progress)) {
			return false;
		} else {
			return filesize($fileNames[0]) == $progress['size'];
		}
	}
	public function start() {
		// are we resuming an unfinished download?
		$resumeFromBytes = $this->resumeFrom();
		if($resumeFromBytes == false) { // create new temporary file
			$this->fileHandle = $this->tmpFileIn($this->getBaseDirectory(), "xb");
			if($this->fileHandle == false) {
				throw new RuntimeException("Could not create temporary download file");
			}
		} else { // reuse old temporary file
			// beware: server may not support resuming
			// use hasCorrectSize() to check a finished download after resuming
			$this->fileHandle = $this->tmpFileIn($this->getBaseDirectory(), "ab");
			if($this->fileHandle == false) {
				throw new RuntimeException("Could not open temporary download file for writing");
			}
		}
		// set up and call cURL
		$curl = curl_init($this->getURL());
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_FILE, $this->fileHandle);
		if($resumeFromBytes != false) {
			curl_setopt($curl, CURLOPT_RANGE, $resumeFromBytes . "-");
		}
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_NOPROGRESS, false);
		curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, "Download::setProgress");
		$response = curl_exec($curl);
		/*
		 * Would be nice to have that, but the current cURL API in PHP does not
		 * allow this to be implemented in a proper way due to the fact that
		 * the HTTP request header cannot be retrieved seperatly from the data.
		 * Stripping the header from the data while writing if using
		 * CURLOPT_WRITEFUNCTION also isn't possible, as this would require the
		 * header size to be available when writing, but curl_getinfo does not
		 * report the corret value when the download has not finished yet.
		 *
		 * So just use the URL path basename as the filename.
		 *
		// try to extract the filename from the HTTP header 
		$this->requestHeaderSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$this->requestHeader = substr($response, 0, $this->requestHeaderSize);
		echo "header: " . $this->requestHeader . "\n";
		$filename = null;
		if(preg_match("/^Content-Disposition: attachment; filename=(.+)$/", $this->requestHeader, $matches)) {
			$filename = $matches[0];
		} else { // fall back to the URL path basename
			$filename = basename($this->getURL());
		}
		*/
		$filename = basename($this->getURL());
		curl_close($curl);
		// rename the temporary file
		$metadata = stream_get_meta_data($this->fileHandle);
		fclose($this->fileHandle);
		rename($metadata['uri'], $this->getDownloadDirectoryIn($this->getBaseDirectory()) . "/" . $filename);
	}
	protected function getDownloadDirectoryIn($baseDirectory) {
		$path = $baseDirectory . "/" . sha1($this->getURL());
		if(!is_dir($path)) {
			IOUtil::getInstance()->mkDir($path);
		}
		return $path;
	}
	protected function tmpFileIn($baseDirectory, $fopenMode) {
		// minor TODO: consider using PHP's built-in tmpfile()
		// pro: system takes care of clean-up
		// con: no resume across reboots possible
		$tmpFileName = $this->getDownloadDirectoryIn($baseDirectory) . "/tmp.download";
		return fopen($tmpFileName, $fopenMode);
	}
	public function getFileNames() {
		return glob($this->getDirectory() . "/*");
	}
}
//$url = "http://img2.wikia.nocookie.net/__cb20080413090826/lotr/de/images/1/1a/Baumbart.jpg";
//$url = "http://pngimg.com/upload/tree_PNG2517.png";
//$url = "http://releases.ubuntu.com/14.04/ubuntu-14.04-desktop-amd64.iso";
//$url = "http://www.mysaleshop.de/media/pdf/testpdf.pdf";
//$url = "https://archive.org/download/test-mpeg/test-mpeg.mpg";
/*
$url = "http://distro.ibiblio.org/damnsmall/release_candidate/dsl-4.11.rc1.iso";
$download = NetUtil::getInstance()->download($url, "/tmp/test");
$download->start();
if($download->hasCorrectSize()) {
	echo "correct size!";
} else {
	echo "incorrect size!";
}
*/
//$download->cancel();
?>
