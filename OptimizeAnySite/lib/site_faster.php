<?php

/**
 * @package		Site Faster plugin
 * @author		Andrey Miasoedov (molotow11@gmail.com)
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

class SiteFaster {
	var $site_code;
	
	function __construct($site_code) {
		$this->site_code = $site_code;
	}
	
	function prepareInlineScripts() {
		//can use skipMove attribute for the script tag for skip moving
		preg_match_all("/<script((?:(?!src=|skipMove).)*?)>(.*?)<\/script>/smix", $this->site_code, $matches);
		if(count($matches[0])) {
			$this->site_code = preg_replace("/<script((?:(?!src=|skipMove).)*?)>(.*?)<\/script>/smix", "", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}		
	}
	
	function prepareScriptTags() {
		preg_match_all("#<script (async|src=|type=(\"|')text/javascript(\"|') src=)(.*?)>(.*?)</script>#is", $this->site_code, $matches);
		if(count($matches[0])) {
			$skip_scripts = array(
				0 => "html5.js",
			);
			$append_skipped = Array();
			foreach($matches[0] as $k=>$script) {
				foreach($skip_scripts as $j=>$skip) {
					if(strpos($script, $skip) !== false) {
						$append_skipped[$j] = $matches[0][$k];
						unset($matches[0][$k]);
					}
				}
			}
			ksort($append_skipped);
			
			$this->site_code = preg_replace("#<script (async|src=|type=(\"|')text/javascript(\"|') src=)(.*?)>(.*?)</script>#is", "", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}
	}
	
	function prepareInlineStyles() {
		//can use skipMove attribute for the script tag for skip moving
		preg_match_all("/<style((?:(?!src=|skipMove).)*?)>(.*?)<\/style>/smix", $this->site_code, $matches);
		if(count($matches[0])) {
			$this->site_code = preg_replace("/<style((?:(?!src=|skipMove).)*?)>(.*?)<\/style>/smix", "", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}		
	}
	
	function prepareStyleTags() {
		preg_match_all("#<link ([^>]*rel=(\"|\')stylesheet(\"|\'))[^>]*>#is", $this->site_code, $matches);
		if(count($matches[0])) {
			$skip_styles = array(
				0 => "bootstrap.css",
				1 => "template.css",
				//2 => "style.css",
			);
			$append_skipped = Array();
			foreach($matches[0] as $k=>$style) {
				foreach($skip_styles as $j=>$skip) {
					if(strpos($style, $skip) !== false) {
						$append_skipped[$j] = $matches[0][$k];
						unset($matches[0][$k]);
					}
				}
			}
			ksort($append_skipped);
			$this->site_code = preg_replace("#<link ([^>]*rel=(\"|\')stylesheet(\"|\'))[^>]*>#is", "", $this->site_code);
			$this->site_code = str_replace("</head>", implode("\r\n", $append_skipped) . "\r\n</head>", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}
	}
	
	function OptimizeImages() {
		$SiteRoot = $_SERVER['SCRIPT_URL'];
		$FileRoot = $_SERVER['DOCUMENT_ROOT'];
		$SavePath = $FileRoot . $SiteRoot . "OptimizedImages/";
		
		//create images folder
		if(!file_exists($SavePath)) {
			mkdir($SavePath, 0777, true);
		}		
		preg_match_all("#{$SiteRoot}[^'\");]*(.jpg|.png)#is", $this->site_code, $matches);
		foreach($matches[0] as $link) {
			if(count(explode(" ", $link)) > 1) continue; //disable for srcset images
			$FilePath = $FileRoot . $link;
			$FileName = explode("/", $link);
			$FileName = $FileName[count($FileName) - 1];
			//optimize image
			if(file_exists($FilePath)
				&& !file_exists($SavePath . $FileName)
			) {
				$this->CompressImage($FilePath, $SavePath . $FileName, 70);
			}
			//replace image url
			if(file_exists($SavePath . $FileName)) {
				$ImagePathParts = explode($SiteRoot, $link)[1];
				$ImagePathParts = explode($FileName, $ImagePathParts)[0];
				$newLink = str_replace($ImagePathParts, "OptimizedImages/", $link);
				$this->site_code = str_replace($link, $newLink, $this->site_code);
			}
		}
	}
	
	function clearSpaces() {
		$this->site_code = preg_replace('/(?:(?:\r\n|\r|\n)\s*){3}/s', "\r\n", $this->site_code);
	}
	
	function codeMinify() {
		$this->site_code = preg_replace('/(\r\n|\r|\n)+/', "\r", $this->site_code);
		$this->site_code = preg_replace('/\t/', "", $this->site_code);
	}
	
	function getSiteCode() {
		return $this->site_code;
	}
	
	function CompressImage($source, $destination, $quality) {
		$info = getimagesize($source);
		if ($info['mime'] == 'image/jpeg') 
			$image = imagecreatefromjpeg($source);
		elseif ($info['mime'] == 'image/gif') 
			$image = imagecreatefromgif($source);
		elseif ($info['mime'] == 'image/png') 
			$image = imagecreatefrompng($source);
		imagejpeg($image, $destination, $quality);
		return $destination;
	}
}

?>