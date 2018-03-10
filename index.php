<?php

/**
 * @package		Optimize Any Site
 * @author		Andrey Miasoedov (molotow11@gmail.com)
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */
 
	//Get content
	ob_start();
		require('index-orig.php');
		$content = ob_get_contents();
	ob_end_clean();
	
	//Excluded pages (any words in the url separated by | character)
	$excluded_pages = "tmpl=component|another-page";
	if(preg_match("({$excluded_pages})", $_SERVER['REQUEST_URI']) === 1) { 
		echo $content;
		return; 
	}
	
	//Allowed pages (any words in the url separated by | character), use / for index
	$allowed_pages = "";
	if(preg_match("({$allowed_pages})", $_SERVER['REQUEST_URI']) !== 1) { 
		echo $content;
		return; 
	}
	
	require_once('OptimizeAnySite/lib/site_faster.php');
	$site_faster = new SiteFaster($content);
	
	//Parameters
	
		//Skip move some script tags (order => script name)
		//This will be prepended to </head> tag in ordering by array keys
		$site_faster->skip_scripts = array(
			0 => "test1.js",
			1 => "test2.min.js",
		);
		
		//Move only scripts listed below
		$site_faster->move_scripts_list = array(
		);
		
		//Skip style tags
		$site_faster->skip_styles = array(
			0 => "test1.css",
			1 => "test2.css",
		);
		
		//Move only styles listed below
		$site_faster->move_styles_list = array(
		);
	
		//Attribute text for skip move inline scripts or styles
		//You need to append this text as attribute for your tags, e.g. <script skipMove or <style skipMove ...
		//Works only for Inline scripts and styles
		$site_faster->skipMove = 'skipMove';

		//Replace any text
		$replace_text = array(
			'complete-text' => 'replace-with',
			'another-text' => '',
			
		);
		
		//Replace or remove any scripts/tags that contains a word
		$replace_tags = array(
			'test1.js' => 'test1.min.js',
			'test1.css' => '',
		);
		
		//Add extra text/scripts/styles
		$add_extra = "
		";
	
	//Optimization
		
		//Move style tags to the bottom 
		$site_faster->prepareStyleTags();
		
		//Move inline styles to the bottom 
		//It is needed for prevent conflicts with moved style tags
		$site_faster->prepareInlineStyles();	
		
		//Move script tags to the bottom
		$site_faster->prepareScriptTags();
		
		//Move inline scripts to the bottom
		$site_faster->prepareInlineScripts();
		
		//Optimize Images
		$site_faster->OptimizeImages();
	
	//Misc
	
		//Replace any text
		foreach($replace_text as $replace=>$with) {
			$site_faster->site_code = str_replace($replace, $with, $site_faster->site_code);
		}

		//Replace any scripts/tags that contains a word
		foreach($replace_tags as $replace=>$with) {
			$site_faster->site_code = preg_replace("/<(.*?)( |>).*{$replace}.*((\/>)|(<\/(.*?)>))/", "{$with}", $site_faster->site_code);
		}	
		
		//Add extra text/scripts/styles
		$site_faster->site_code = str_replace("</body>", $add_extra . "\r\n</body>", $site_faster->site_code);
		
		//Clear spaces
		$site_faster->clearSpaces();
		
		//Code minification
		$site_faster->codeMinify();
	
	//Output
	echo $site_faster->site_code;
?>