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
		'href' => get_admin_url(NULL, '/tools.php?page=link2post_tools') ) );
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
		
	//check if we've already processed this URL			TODO: Should we update if it's already found?
	$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_sql($url) . "' LIMIT 1";
	$old_post_id = $wpdb->get_var($sqlQuery);
	
	//if either of these are true, the page will be scraped
	$new_post = empty($old_post_id);
	$update_post = false;		
		
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
	foreach($modules as $module_host => $arr) {
    	if($host == $module_host){
    		//we found one, use the module's parse function now
    		
    		if(empty($arr['callback'])){
    			echo __("Broken callback function.", 'link2post');
    			return;
    		}
    	
    		if($new_post==true){
    			//new post, so not udating
				call_user_func($arr['callback'], $url);
				return;
			}
			
			//updating
			$post_url = get_permalink($old_post_id);
			if(empty($arr['can_update']) || $arr['can_update']==false){
				//module can't update
				echo __('Found an existing post for that URL here:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>.<br>' . __('The module for this post type is not capable of updating.', 'link2post');
				return;
			}
			
			//module can update
			if(empty($_REQUEST['l2poverwrite'])) {
				$post_url = get_permalink($old_post_id);		
				echo __('Found an existing post for that URL here:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>.';
				$current_url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				echo __('<br>Would you like to overwrite it? ').'<a href="'.$current_url.'&l2poverwrite=true">'.__('Yes   ').'</a><a href="'.$current_url.'&l2poverwrite=false">'.__('No').'</a>';
				return;
			}
			elseif($_REQUEST['l2poverwrite']=="true"){
				call_user_func($arr['callback'], $url, $old_post_id);
				return;
			}
			elseif($_REQUEST['l2poverwrite']=="false"){
				echo("Post not updated.");
				return;
			}
    		return;
    	}
	}
	
	//no modules were found for this host, so we'll do the default behavior.
	
	//if post already exists
	if(!$new_post) {
		if(empty($_REQUEST['l2poverwrite'])) {
			$post_url = get_permalink($old_post_id);		
			echo __('Found an existing post for that URL here:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>.';
			$current_url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			echo __('<br><br>Would you like to overwrite it? ').'<a href="'.$current_url.'&l2poverwrite=true">'.__('Yes   ').'</a><a href="'.$current_url.'&l2poverwrite=false">'.__('No').'</a>';
			return;
		}
		elseif($_REQUEST['l2poverwrite']=="true"){
			$update_post = true;
		}
		elseif($_REQUEST['l2poverwrite']=="false"){
			echo("Post not updated.");
			return;
		}
	} 
	
	//scraping
	if($new_post==true || $update_post==true){
		//load the URL and parse
		require_once(dirname(__FILE__).'/lib/selector.php');	
		try{
			$html = wp_remote_retrieve_body(wp_remote_get($url));
			
			//scrape the title
			$title = l2p_SelectorDOM::select_element('title', $html);
			if(!empty($title) && !empty($title['text']))
				$title = sanitize_text_field($title['text']);
			else{
				$title = "";
			}
			
			//scrape the description
			$description = l2p_SelectorDOM::select_element('meta[name=description]', $html);
			if(!empty($description) && !empty($description['attributes']) && !empty($description['attributes']['content']))
				$description = sanitize_text_field($description['attributes']['content']);
			else{
				$description = "";
			}
			
			//add link back to the URL to the description:
			$description .= "\n\n" . sprintf(__('Originally posted at %s.', 'link2post'), '<a href="' . esc_url($url) . '">' . $host . '</a>');
			
			if($new_post==true){
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
			}	
			else{
				//update existing post
				$postarr = array(
					'ID' => $old_post_id,
					'post_type' => 'post',
					'post_title' => $title,
					'post_content' => $description,
					'post_author' => $current_user->ID
				);
				wp_update_post($postarr);
				echo '<hr />';
				$post_url = get_permalink($old_post_id);
				echo __('Updated post at ', 'link2post') . '<a href="' . $post_url . '">' . $post_url . '</a>.';
			}		
		}catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}		
}