<?php

/*
Plugin Name: CG-Redirect
Plugin URI: http://www.chait.net/index.php?p=310
Description: Simple plugin for instant-redirects to another URL when a single post is displayed.  Very basic, very simple.  Please <a href="http://www.chait.net/index.php?p=48" title="Support CHAITGEAR">find a way to support CHAITGEAR!</a>
Author: David Chait
Author URI: http://www.chait.net
Version: 0.7
*/ 


$REQUESTED = $_SERVER['REQUEST_URI'];
if ($truncoff = strpos($REQUESTED, '&'))
	$REQUESTED = substr($REQUESTED, 0, $truncoff);

if ( strstr($REQUESTED, 'plugins.php')  // under admin interface?
||	 is_plugin_page() ) return;

$pluginBasedir = dirname(__FILE__).'/';

// if you have any problems with the redirect, you can try turning this off.
// but it's here to attempt to force search engines through to the linked page,
// and better redirect browsers.  I've defaulted to a 302 redirect code.
$use_3xx_redirect = 302;

// we don't want redirect processing ever in wp-admin section...
if ( !strstr($REQUESTED, 'wp-admin') )
{
	function cg_redirect_pageload()
	{
		global $posts;
		global $use_302_redirect;

		if (!is_single() && !is_page()) return; // only hook when we're on a single page.
		
		$content = &$posts[0]->post_content;
		$startloc = strpos($content, '<redirect '); // 10 chars
		if ($startloc===FALSE) return; //no tag.
		$startloc += 9; // skips past the tag opening
		$endloc = strpos($content, '/>', $startloc);
		if ($endloc===FALSE) return;
		
		$tourl = rtrim(substr($content, $startloc, $endloc-$startloc));
//		die($tourl);
		if ($use_3xx_redirect)
		{
			@header("HTTP/1.1 $use_3xx_redirect");
			@header("Status: $use_3xx_redirect");
		}
		@header("Location: ".$tourl);
		exit();
	}
	
	function cg_redirect_init()
	{
		if (function_exists('cg_redirect_pageload'))
			add_action('template_redirect', 'cg_redirect_pageload');
	}
	
	if (function_exists('cg_redirect_init'))
		add_action('plugins_loaded', 'cg_redirect_init');
}
//	do_action('template_redirect');
//		$this->posts = apply_filters('the_posts', $this->posts);

?>