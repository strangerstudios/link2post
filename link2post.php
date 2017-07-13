<?php
/*
Plugin Name: Link2Post
Plugin URI: http://www.strangerstudios.com/link2post/
Description: Automatically parse submitted URLs to create posts.
Version: .1
Author: strangerstudios, dlparker1005, andrewza
Author URI: http://www.strangerstudios.com
Text Domain: link2post
*/

/*
	Notes
	
	* Do we want to load a library for scraping HTML? https://github.com/duzun/hQuery.php
*/

/*
	Add Admin Page
*/
function l2p_admin_pages() {
	add_submenu_page( 'tools.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post', 'l2p_admin_pages_main' );
}
add_action('admin_menu', 'l2p_admin_pages');

function l2p_admin_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post.php');
}


/*
	Add Form to Admin Bar
*/
function l2p_admin_bar_menu() {
	global $wp_admin_bar;
	
	if(!current_user_can('edit_posts'))
		return;
	
	$wp_admin_bar->add_menu( array(
		'id' => 'link2post',
		'parent' => 'new-content',
		'title' => __( 'Link2Post', 'link2post' ),
		'href' => get_admin_url(NULL, '/tools.php?page=link2post') ) );
}
add_action('admin_bar_menu', 'l2p_admin_bar_menu');

/*
	Process a Form Submission
*/
function l2p_processURL($url = NULL) {
	if(empty($url) && !empty($_REQUEST['l2purl'])) {
		$url = esc_url_raw($_REQUEST['l2purl']);		
	}
	//echo($url);
	
	//no  URL, bail
	if(empty($url))
		return;
		
	//Load modules
	require_once(dirname(__FILE__) . '/modules/gist.php');
	
	//each element in array should be [host=>callback_function]
	$modules = apply_filters('l2p_modules', array());
	
	//Check the domain of the URL to see if it matches a module
	$host = parse_url($url, PHP_URL_HOST);
	foreach($modules as $module_host => $callback_function) {
    	if($host == $module_host){
    		call_user_func($callback_function, $url);
    		return;
    	}
	}
	//else default stuff below
	
	//check if we've already processed this URL
	
	//use HTTP API to access the URL
	require_once(dirname(__FILE__).'/lib/hQuery/hquery.php');
	duzun\hQuery::$cache_path = dirname(__FILE__).'/lib/hQuery/cache/';
    $doc = hQuery::fromUrl($url);
    
	//scrape the title
	$title = $doc->find('title');
	echo($title);
	
	//scrape the description
	//$description = $doc->find('');
	//echo($description);
	
	//create a link post and insert it
}

function l2p_processURLWithModule($url, $module) {
	
}