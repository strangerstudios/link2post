<?php

//filter ___ to add gist module to link2post
function l2p_add_youtube_module($modules) {
    $modules["www.youtube.com"] = 'l2p_youtube_callback';
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_youtube_module');

//callback to process a URL that is from gist.github.com_address
function l2p_youtube_callback($url){
	global $current_user, $wpdb;

	//check if we've already processed this URL
	$sqlQuery = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'l2p_url' AND meta_value = '" . esc_sql($url) . "' LIMIT 1";
	$old_post_id = $wpdb->get_var($sqlQuery);
	
	if(!empty($old_post_id)) {
		$post_url = get_permalink($old_post_id);		
		echo __('Found an existing post for that URL here:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>';;
	} else {
		//Set up selector
		require_once(L2P_DIR.'/lib/selector.php');
		$html = wp_remote_retrieve_body(wp_remote_get($url));

		//grab title from title element
		$title = l2p_SelectorDOM::select_element('title', $html);
		if(!empty($title) && !empty($title['text']))
					$title = sanitize_text_field($title['text']);
	
		//grab description video description
		$description = l2p_SelectorDOM::select_element('#eow-description', $html);
		if(!empty($description) && !empty($description['text']))
					$description = sanitize_text_field($title['text']);
	
		//add embed code to post body
		$embed_code = $url;
	
		//get channel name
		$channel_name = l2p_SelectorDOM::select_element('.yt-user-info > .g-hovercard', $html);
		if(!empty($channel_name) && !empty($channel_name['text']))
					$channel_name = sanitize_text_field($title['text']);
	
		//get chanel url
		$channel_url = 'https://youtube.com/'.$channel_name;

		//format post content
		$break = " </br> ";
		$post_content = $description.$break."\n".$embed_code."\n".$break.'This video was made by <a href="'.$channel_url.'">'.$channel_name.'</a>.'.$break.'Original Gist: <a href="'.$url.'">'.$url.'</a>';
		
		//get OG image, CAN'T GET THIS TO WORK
		/*
		$img_link = l2p_SelectorDOM::select_element('meta', $html);
		var_dump($img_link);
		if(!empty($img_link) && !empty($img_link['attributes']) && !empty($img_link['attributes']['content'])){
			$img_link = sanitize_text_field($img_link['attributes']['content']);
			echo("sanitized");
		}
		echo($img_link);
		*/

		//insert a Youtube CPT
		$postarr = array(
				'post_type' => 'youtube',
				'post_title' => $title,
				'post_content' => $post_content,
				'post_author' => $current_user->ID,
				'post_status' => 'publish',
				'meta_input' => array(
					'l2p_url' => $url,
				)
			);
		$post_id = wp_insert_post($postarr);
		$post_url = get_permalink($post_id);
			
		echo '<hr />';
		echo __('New Gist Post:', 'link2post') . ' <a href="' . $post_url . '">' . $post_url . '</a>';
	}
}

//do we need embed code?

//add a YouTube CPT
function create_youtube_cpt() {  
  register_post_type( 'youtube',
    array(
      'labels' => array(
        'name' => __( 'YouTube Videos' ),
        'singular_name' => __( 'YouTube Video' ),
        'add_new_item' => __('Add New YouTube Video'),
        'edit_item' => __( 'Edit YouTube Video' ),
        'new_item' => __( 'New YouTube Video' ),
		'view_item' => __( 'View YouTube Videos' ),
		'search_items' => __( 'Search YouTube Videos' ),
		'not_found' => __( 'No YouTube Videos Found' ),
		'not_found_in_trash' => __( 'No YouTube Videos Found In Trash' ),
		'all_items' => __( 'All Gists' ),
      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}
add_action( 'init', 'create_youtube_cpt' );

//handle search and archives

//widget for related posts
