<?php
class IOUtil {
	private static $instance = null;
	private function __construct() {}
	public function getInstance() {
		if(self::$instance == null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public static function mkdir($path) {
		if(file_exists($path) && !is_dir($path)) {
			throw new RuntimeException("Cannot create directory: path points to a file");
		}
		if(!is_dir($path)) {
			if(!\mkdir($path, 0777, true)) {
				throw new RuntimeException("Could not create directory " . $path);
			}
		} // else: directory exists, ignore
	}
	public static function rmdir($path) {
		array_map("unlink", glob($path ."/*"));
		if(!\rmdir($path)) {
			throw new RuntimeException("Could not remove directory " . $path);
		}
	}
	public function fwrite($filename, $content) {
		$file = \fopen($filename, "wb");
		if($file == false) {
			throw new RuntimeException("Could not open file " . $filename .
				" for writing");
		}
		$bytes = \fwrite($file, $content);
		if($bytes == false) {
			$this->flcose($file);
			throw new RuntimeException("Could not write to file " . $filename);
		}
		$this->flcose($file);
		return $bytes;
	}
	public static function fread($filename) {
		$file = \fopen($filename, "rb");
		if($file == false) {
			throw new RuntimeException("Could not open " . $filename .
				" for reading");
		}
		$content = \fread($file, \filesize($filename));
		if($content == false) {
			$this->flcose($file);
			throw new RuntimeException("Could not read file " . $filename);
		}
		return $content;
	}
	protected function tmpDir() {
		$path = $wgSMWEnrichTmpDir . '/' . mt_rand();
		$this->mkdir($path);
		return $path;
	}	
	protected function flcose($handle) {
		if(!\fclose($handle)) {
			throw new RuntimeException("Could not close file " . $handle);
		}
	}
}
?>
