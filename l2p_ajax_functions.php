<?php

$path = $_SERVER['DOCUMENT_ROOT']."/TestWebsite";
include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';
error_reporting(E_ERROR);

if(!$_GET['l2pfunc']){ die("0");}
elseif($_GET['l2pfunc']=="l2p_can_update"){
	if(!$_GET['url']){ die("0");}
	else{
		l2p_can_update($_GET['url']);
	}
}
elseif($_GET['l2pfunc']=="l2p_create_post"){
	if(!$_GET['url']){ die("0");}
	else{
		l2p_create_post($_GET['url'], $_GET['old_post_id']);
	}
}
elseif($_GET['l2pfunc']=="test"){
echo "It's a good day. ";
}

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
		//echo("making new post");
		$objToReturn->new_post_created = true;
		$objToReturn->new_post_url = l2p_create_post($url, NULL, true);
		$JSONtoReturn = json_encode($objToReturn);
		echo $JSONtoReturn;
		return;
	}	
	//echo("is old post");
	$objToReturn->new_post_created = false;
	$objToReturn->old_post_id = $old_post_id;
	$objToReturn->old_post_url = get_permalink($old_post_id);
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
	$found_match = false;
	foreach($modules as $module_host => $arr) {
    	if($host == $module_host){
    		$found_match = true;
    		//we found one, use the module's parse function now
    		if(empty($arr['callback']) || empty($arr['can_update']) || $arr['can_update']==false){
    			//echo __("Broken callback function.", 'link2post');
    			$objToReturn->can_update = false;
    		}
			else{
				$objToReturn->can_update = true;
			}
    	}
	}
	if($found_match==false){
		$objToReturn->can_update = true;
	}
	$JSONtoReturn = json_encode($objToReturn);
	echo $JSONtoReturn;
}


function l2p_create_post($url, $old_post_id=NULL, $return_result=false){
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
				call_user_func($arr['callback'], $url, $old_post_id, $return_result);
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
			if($return_result==true){
				return $post_url;
			}
			$objToReturn->url = $post_url;
			$JSONtoReturn = json_encode($objToReturn);
			echo $JSONtoReturn;
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
			$post_url = get_permalink($old_post_id);
			$objToReturn->url = $post_url;
			$JSONtoReturn = json_encode($objToReturn);
			echo $JSONtoReturn;
		}		
	}catch (Exception $e) {}
}		

?>  