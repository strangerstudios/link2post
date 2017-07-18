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
require_once(L2P_DIR . '/modules/gist.php');
//require_once(L2P_DIR . '/modules/youtube.php');

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
	global $current_user, $wpdb;
	
	if(empty($url) && !empty($_REQUEST['l2purl'])) {
		$url = esc_url_raw($_REQUEST['l2purl']);		
	}
	//echo($url);
	
	
	//no URL, bail
	if(empty($url))
		return;			
		
	/**
	 * Filter to add Link2Post modules. Modules are used to handle parsing
	 * for URLs from specific sites.
	 *
	 * @since .1
	 *
	 * @param array $modules Array of modules. Each element in array should be [host=>callback_function].	 
	 */
	$modules = apply_filters('l2p_modules', array());
		
	//check the domain of the URL to see if it matches a module
	$host = parse_url($url, PHP_URL_HOST);
	foreach($modules as $module_host => $callback_function) {
    	if($host == $module_host){
    		//we found one, use the module's parse function now
			call_user_func($callback_function, $url);
    		return;
    	}
	}
	
	//No modules were found for this host, so we'll do the default behavior.
	
	//check if we've already processed this URL			TODO: Should we update if it's already found?
	$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_sql($url) . "' LIMIT 1";
	$old_post_id = $wpdb->get_var($sqlQuery);
	
	if(!empty($old_post_id)) {
		$post_url = get_permalink($old_post_id);		
		echo __('Found an existing post for that URL here:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>';;
	} else {
		//load the URL and parse
		require_once(dirname(__FILE__).'/lib/selector.php');	
		try{
			$html = wp_remote_retrieve_body(wp_remote_get($url));
					
			//scrape the title
			$title = l2p_SelectorDOM::select_element('title', $html);
			if(!empty($title) && !empty($title['text']))
				$title = sanitize_text_field($title['text']);
			
			//scrape the description
			$description = l2p_SelectorDOM::select_element('meta[name=description]', $html);
			if(!empty($description) && !empty($description['attributes']) && !empty($description['attributes']['content']))
				$description = sanitize_text_field($description['attributes']['content']);
			
			//add link back to the URL to the description:
			$description .= "\n\n" . sprintf(__('Originally post at %s.', 'link2post'), '<a href="' . esc_url($url) . '">' . $host . '</a>');
			
			//create a link post and insert it
			$postarr = array(
				'post_type' => 'post',
				'post_title' => $title,
				'post_content' => $description,
				'post_author' => $current_user->ID,
				'post_status' => 'publish',
				'meta_input' => array(
					'l2p_url' => $url,
				)
			);
			
			$post_id = wp_insert_post($postarr);
			$post_url = get_permalink($post_id);
			
			echo '<hr />';
			echo __('New Post:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>';				
		}catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}		
}