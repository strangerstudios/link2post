<?php
/*
//does post already exist?
	//if not, create, return 1. Set status to 3
	//if so, is the post able to be updated?
		//if not, return -1. set status to 0 with message in span
		//if so, return 2. set status to 2
		
NEEDS TO RETURN OLD POST ID TOO IF NEEDS TO UPDATE
*/
function l2p_can_update($url = NULL) {
	global $current_user, $wpdb;
	
	//no URL, bail
	if(empty($url))
		return;	
		
	//check if we've already processed this URL
	$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_sql($url) . "' LIMIT 1";
	$old_post_id = $wpdb->get_var($sqlQuery);
	
	if(empty($old_post_id)){
		l2p_create_post($url);
		return 1;
	}	
	
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
    		if(empty($arr['callback']) || empty($arr['can_update']) || $arr['can_update']==false){
    			//echo __("Broken callback function.", 'link2post');
    			return -1;
    		}
			else{
				return 2;
			}
    	}
	}
	return 2;
}


function l2p_create_post($url, $old_post_id=NULL){
	global $current_user, $wpdb;
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
	foreach($modules as $module_host => $arr) {
    	if($host == $module_host){
    		//we found one, use the module's parse function now
    		if(empty($arr['callback']) || (!empty($old_post_id) && (empty($arr['can_update']) || $arr['can_update']==false))){
				//can't 
    			return -1;
    		}
			else{
				call_user_func($arr['callback'], $url, $old_post_id);
				return;
			}
    	}
	}
		
	require_once(dirname(__FILE__).'/lib/selector.php');	
	try{
		$html = wp_remote_retrieve_body(wp_remote_get($url));
		
		//scrape the title
		$title = l2p_SelectorDOM::select_element('title', $html);
		if(!empty($title) && !empty($title['text']))
			$title = sanitize_text_field($title['text']);
		else{
			$title = "No title";
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
		
		if(empty($old_post_id)){
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

?>  