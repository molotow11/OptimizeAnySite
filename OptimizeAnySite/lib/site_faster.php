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
		$pattern = "/<script((?:(?!src=|{$this->skipMove}).)*?)>(.*?)<\/script>/smix";
		preg_match_all($pattern, $this->site_code, $matches);
		if(count($matches[0])) {
			$this->site_code = preg_replace($pattern, "", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}		
	}
	
	function prepareScriptTags() {
			if(count($this->move_scripts_list)) {
				$list = implode("|", $this->move_scripts_list);
				$pattern = "/<script[^<]*({$list})[^<]*>[^<]*<\/script>/";
				preg_match_all($pattern, $this->site_code, $matches);
				if(count($matches[0])) {
					$sorted = Array();
					foreach($matches[0] as $k=>$script) {
						foreach($this->move_scripts_list as $j=>$moved) {
							if(strpos($script, $moved) !== false) {
								$sorted[$j] = $matches[0][$k];
							}
						}
					}
					ksort($sorted);	
					$this->site_code = preg_replace($pattern, "", $this->site_code);
					$this->site_code = str_replace("</body>", implode("\r\n", $sorted) . "\r\n</body>", $this->site_code);
				}
			}
			else {
				$pattern = "#<script (async|src=|type=(\"|')text/javascript(\"|') src=)(.*?)>(.*?)</script>#is";
				preg_match_all($pattern, $this->site_code, $matches);
				if(count($matches[0])) {
					$append_skipped = Array();
					foreach($matches[0] as $k=>$script) {
						foreach($this->skip_scripts as $j=>$skip) {
							if(strpos($script, $skip) !== false) {
								$append_skipped[$j] = $matches[0][$k];
								unset($matches[0][$k]);
							}
						}
					}
					ksort($append_skipped);					
					$this->site_code = preg_replace($pattern, "", $this->site_code);
					$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
					$this->site_code = str_replace("</head>", implode("\r\n", $append_skipped) . "\r\n</head>", $this->site_code);
				}
			}
	}
	
	function prepareInlineStyles() {
		$pattern = "/<style((?:(?!src=|{$this->skipMove}).)*?)>(.*?)<\/style>/smix";
		preg_match_all($pattern, $this->site_code, $matches);
		if(count($matches[0])) {
			$this->site_code = preg_replace($pattern, "", $this->site_code);
			$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
		}		
	}
	
	function prepareStyleTags() {
		if(count($this->move_styles_list)) {
			$list = implode("|", $this->move_styles_list);
			$pattern = "/<link [^<]*({$list})[^<]* \/>/";
			preg_match_all($pattern, $this->site_code, $matches);
			if(count($matches[0])) {
				$sorted = Array();
				foreach($matches[0] as $k=>$script) {
					foreach($this->move_styles_list as $j=>$moved) {
						if(strpos($script, $moved) !== false) {
							$sorted[$j] = $matches[0][$k];
						}
					}
				}
				ksort($sorted);	
				$this->site_code = preg_replace($pattern, "", $this->site_code);
				$this->site_code = str_replace("</body>", implode("\r\n", $sorted) . "\r\n</body>", $this->site_code);
			}
		}
		else {		
			$pattern = "#<link ([^>]*rel=(\"|\')stylesheet(\"|\'))[^>]*>#is";
			preg_match_all($pattern, $this->site_code, $matches);
			if(count($matches[0])) {
				$append_skipped = Array();
				foreach($matches[0] as $k=>$style) {
					foreach($this->skip_styles as $j=>$skip) {
						if(strpos($style, $skip) !== false) {
							$append_skipped[$j] = $matches[0][$k];
							unset($matches[0][$k]);
						}
					}
				}
				ksort($append_skipped);
				$this->site_code = preg_replace($pattern, "", $this->site_code);
				$this->site_code = str_replace("</body>", implode("\r\n", $matches[0]) . "\r\n</body>", $this->site_code);
				$this->site_code = str_replace("</head>", implode("\r\n", $append_skipped) . "\r\n</head>", $this->site_code);
			}
		}
	}
	
	function OptimizeImages() {
		$FileRoot = $_SERVER['DOCUMENT_ROOT'];
		$parts = explode("/", $_SERVER['SCRIPT_NAME']);
		$SiteRoot = count($parts) > 2 ? $parts[count($parts) - 2] : $parts[0];
		$SavePath = $FileRoot . '/' . $SiteRoot . '/OptimizedImages/';
		
		//create images folder
		if(!file_exists($SavePath)) {
			mkdir($SavePath, 0777, true);
		}	

		preg_match_all("#{$SiteRoot}[^'\");]*(.jpg|.jpeg|.png)#is", $this->site_code, $matches);
		foreach($matches[0] as $link) {
			if(count(explode(" ", $link)) > 1) continue; //disable for srcset images
			$FilePath = $FileRoot . '/' . $link;
			$parts = explode("/", $link);
			$FileName = $parts[count($parts) - 1];
			//optimize image
			if(file_exists($FilePath)
				&& !file_exists($SavePath . $FileName)
			) {
				$this->imageCompress($FilePath, $SavePath . $FileName);
			}
			//replace original image url
			if(file_exists($SavePath . $FileName)) {
				$newLink = $SiteRoot . '/OptimizedImages/' . $FileName;
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
	
	function imageCompress($src, $dst) {
		if($this->isAnimatedPng($src)) {
			copy($src, $dst);
			return;
		}
		list($width, $height) = getimagesize($src);
		$type = strtolower(substr(strrchr($src,"."),1));
		if($type == 'jpeg') $type = 'jpg';
		switch($type) {
			case 'jpg' : 
				$img = imagecreatefromjpeg($src); 
			break;
			case 'png' : 
				$img = imagecreatefrompng($src);
			break;
		}
		$new = imagecreatetruecolor($width, $height);
		// preserve transparency
		if($type == "gif" or $type == "png") {
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
		}
		imagecopyresampled($new, $img, 0, 0, 0, 0, $width, $height, $width, $height);
		switch($type) {
			case 'jpg': imagejpeg($new, $dst, 70); break;
			case 'png': imagepng($new, $dst, 9); break;
		}
		return true;
	}
	
	function isAnimatedPng($src) {
		$img_bytes = file_get_contents($src);
		if($img_bytes) {
			if(strpos(substr($img_bytes, 0, strpos($img_bytes, 'IDAT')), 'acTL') !== false
				|| strpos($img_bytes, 'GIF89') !== false
			) {
				return true;
			}
		}
		return false;
    }
}

?>