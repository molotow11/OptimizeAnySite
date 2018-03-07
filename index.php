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

	//Optimization
	require_once('OptimizeAnySite/lib/site_faster.php');
	$site_faster = new SiteFaster($content);
		
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
		
		//Clear spaces
		$site_faster->clearSpaces();
		
		//Code minification
		$site_faster->codeMinify();
	
	//Output
	echo $site_faster->getSiteCode();
?>