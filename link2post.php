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
	Load modules
*/
define('L2P_DIR', dirname(__FILE__));
if(get_option("l2p_gist_enabled")=="enabled"){
	require_once(L2P_DIR . '/modules/gist.php');
}
//require_once(L2P_DIR . '/modules/youtube.php');

/*
	Add Admin Page
*/
function l2p_admin_pages() {
	add_submenu_page( 'tools.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_tools', 'l2p_admin_tool_pages_main' );
	add_submenu_page( 'options-general.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_settings', 'l2p_admin_settings_pages_main' );
}
add_action('admin_menu', 'l2p_admin_pages');

function l2p_admin_tool_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_tools.php');
}

function l2p_admin_settings_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_settings.php');
}

function l2p_admin_bar_menu() {
	global $wp_admin_bar;
	
	if(!current_user_can('edit_posts'))
		return;
	
	$wp_admin_bar->add_menu( array(
		'id' => 'link2post',
		'parent' => 'new-content',
		'title' => __( 'Link2Post', 'link2post' ),
		'href' => get_admin_url(NULL, '/tools.php?page=link2post_tools') ) );

	$wp_admin_bar->add_menu( array(
		'id' => 'l2p_input',
		'parent' => 'link2post',
		'title' => '<form><input type="text" id="l2p_URL_input" style="height:22px;"><button type="button" id="l2p_toolbar_submit" onclick="l2p_initial_submit()">Create Post</button></form><span id="l2p_response"></span>') );
}
add_action('admin_bar_menu', 'l2p_admin_bar_menu');
?>  