<?php namespace WeAreNotMachines\Utilities;

use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
class Autoloader {

	private static $instance = null;

	private $prefixes = ["src", "lib", "classes", "vendor"];
	private $baseFolders = [];

	private function __construct() {}

	public static function register($baseFolder=null) {
		if (empty(self::$instance)) {
			self::$instance = new Autoloader();
			self::$instance->register();
		}
		if (!empty($baseFolder)) {
			self::$instance->addBase($baseFolder);
		} else {
			self::$instance->addBase($_SERVER['DOCUMENT_ROOT']);
		}	
		spl_autoload_register(function($class) {
			foreach (self::$instance->baseFolders AS $b) {
				if (file_exists($b.DIRECTORY_SEPARATOR.str_replace("\\", "/",$class).".php")) {
					require_once $b.DIRECTORY_SEPARATOR.str_replace("\\", "/",$class).".php";
					break;
				}
			}
		});
	}

	public function addPrefix($prefix) {
		$this->prefixes[] = $prefix;
		$this->prefixes = array_unique($this->prefixes);
	}

	public function addBase($pathToBase) {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathToBase, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator AS $filename=>$info) {
			if ($info->isDir() && in_array(substr($filename, strrpos($filename,"/")+1), $this->prefixes)) {
				$this->baseFolders[] = $filename;
			}
		}
		$this->baseFolders = array_unique($this->baseFolders);
	}

	public function getBaseFolders() {
		return $this->baseFolders;
	}

	public static function getAutoloader() {
		if (empty(self::$instance)) {
			self::register();
		}
		return self::$instance;
	}

}