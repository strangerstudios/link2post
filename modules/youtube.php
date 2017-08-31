<?php
function l2p_add_youtube_module($modules) {
    $modules["youtube.php"] = array('host'=>'youtube.com','callback'=>'l2p_youtube_callback', 'create_cpt'=>'l2p_create_youtube_cpt', 'quick_name'=>'youtube','can_update'=>true);
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
		$post_content = $description.$break."\n".esc_url_raw($embed_code)."\n".$break.__('This video was made by', 'link2post'). '<a href="'.esc_url_raw($channel_url).'">'.$channel_name.'</a>'.$break.__('Original Video:', 'link2post'). '<a href="'.esc_attr($url).'">'.esc_url($url).'</a>';
		$post_type = (post_type_exists( "youtube" ) ? 'youtube' : 'post');

		if(empty($old_post_id)){
		//insert a Youtube CPT
		$postarr = array(
				'post_type' => $post_type,
				'post_title' => $title,
				'post_content' => $post_content,
				'post_author' => $current_user->ID,
				'post_status' => 'publish',
				'meta_input' => array(
					'l2p_url' => esc_url_raw($url),
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
			'post_type' => $post_type,
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
function l2p_create_youtube_cpt() {  
  register_post_type( 'youtube',
    array(
      'labels' => array(
        'name' => __( 'YouTube Videos', 'link2post' ),
        'singular_name' => __( 'YouTube Video', 'link2post' ),
        'add_new_item' => __('Add New YouTube Video', 'link2post'),
        'edit_item' => __( 'Edit YouTube Video', 'link2post' ),
        'new_item' => __( 'New YouTube Video', 'link2post' ),
		'view_item' => __( 'View YouTube Videos', 'link2post' ),
		'search_items' => __( 'Search YouTube Videos', 'link2post' ),
		'not_found' => __( 'No YouTube Videos Found', 'link2post' ),
		'not_found_in_trash' => __( 'No YouTube Videos Found In Trash', 'link2post' ),
		'all_items' => __( 'All Videos', 'link2post' ),
      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}

//handle search and archives

//widget for related posts
