<?php
function l2p_add_youtube_module($modules) {
    $modules["youtube.com"] = array('callback'=>'l2p_youtube_callback', 'can_update'=>true);
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_youtube_module');

function l2p_youtube_callback($url, $old_post_id=NULL, $return_result=false){
	global $current_user;
	//Set up selector
	require_once(L2P_DIR.'/lib/selector.php');
	$html = wp_remote_retrieve_body(wp_remote_get($url));

	//grab title from title element
		$title = l2p_SelectorDOM::select_element('title', $html);
		if(!empty($title) && !empty($title['text']))
					$title = sanitize_text_field($title['text']);
		$objToReturn->title = $title;
	
		//grab description video description
		$description = l2p_SelectorDOM::select_element('#eow-description', $html);
		if(!empty($description) && !empty($description['text']))
			$description = sanitize_text_field($description['text']);

		//add embed code to post body
		$embed_code = $url;
	
		//get channel name
		$channel_name = l2p_SelectorDOM::select_element('.yt-user-info', $html);
		if(!empty($channel_name) && !empty($channel_name['text']))
			$channel_name = sanitize_text_field($channel_name['text']);

	
		//get chanel url
		$channel_id = l2p_SelectorDOM::select_element('meta[itemprop=channelId]', $html);
		if(!empty($channel_id) && !empty($channel_id["attributes"]['content']))
			$channel_id = sanitize_text_field($channel_id["attributes"]['content']);
		$channel_url = 'https://youtube.com/channel/'.$channel_id;

		//format post content
		$break = " </br> ";
		$post_content = $description.$break."\n".$embed_code."\n".$break.'This video was made by <a href="'.$channel_url.'">'.$channel_name.'</a>.'.$break.'Original Video: <a href="'.$url.'">'.$url.'</a>';
		//echo $post_content;

		if(empty($old_post_id)){
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
		if($return_result==true){
			return $post_url;
		}
		$objToReturn->url = $post_url;
		$JSONtoReturn = json_encode($objToReturn);
		echo $JSONtoReturn;
		exit;
	}
	else{
		//update existing post
		$postarr = array(
			'ID' => $old_post_id,
			'post_type' => 'youtube',
			'post_title' => $title,
			'post_content' => $post_content,
			'post_author' => $current_user->ID
		);
		wp_update_post($postarr);
		$post_url = get_permalink($old_post_id);
		$objToReturn->url = $post_url;
		$JSONtoReturn = json_encode($objToReturn);
		echo $JSONtoReturn;
		exit;
	}
}

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
		'all_items' => __( 'All Videos' ),
      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}
add_action( 'init', 'create_youtube_cpt' );

//handle search and archives

//widget for related posts
