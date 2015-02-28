<?php namespace WeAreNotMachines\Utilities;

class URLParser {

	public static function parseCurrentURLSegments() {
		$url = preg_replace("/([A-z]+)+(\.)?([A-z]+)?(\?){1}/", "", $_SERVER['REQUEST_URI']);
		//remove a trailing slash
		$url = trim($url, "/");
		$segments = explode("/", $url);
		return $segments;
	}
}