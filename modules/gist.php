<?php
//filter ___ to add gist module to link2post
function l2p_add_gist_module($modules) {
    $modules["gist.php"] = array('host'=>'gist.github.com','callback'=>'l2p_gist_callback', 'create_cpt'=>'l2p_create_gist_cpt', 'quick_name'=>'gist','can_update'=>true);
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_gist_module');

//callback to process a URL that is from gist.github.com_address
function l2p_gist_callback($url, $old_post_id=NULL, $return_result=false){
	global $current_user;

	//Set up selector
	require_once(L2P_DIR.'/lib/selector.php');
	$html = wp_remote_retrieve_body(wp_remote_get($url));

	//grab title from title element
	$title = l2p_SelectorDOM::select_element('.repository-meta-content', $html);
	if(!empty($title) && !empty($title['text']))
				$title = sanitize_text_field($title['text']);
	else{
		$title = l2p_SelectorDOM::select_element('.gist-header-title', $html);
		if(!empty($title) && !empty($title['text']))
			$title = sanitize_text_field($title['text']);
		else
			$title="Title is empty";
	}
	//grab description from multiline comment
	$raw_code_url = esc_url_raw($url).'/raw';
	$raw_code_page = wp_remote_retrieve_body(wp_remote_get($raw_code_url));
	$code = " ".htmlspecialchars($raw_code_page); 
	$start = '/*';
	$end = '*/';
	$ini = strpos($code,$start);
	if ($ini == 0){
		$description = "";
	}
	else{
		$before_description = substr($code, 0, $ini);
		$trimmed = trim($before_description);
		if($trimmed == '' or $trimmed == htmlspecialchars('<?php')){
			$ini += strlen($start);   
			$len = strpos($code,$end,$ini) - $ini;
			$description = substr($code,$ini,$len);
			$description = trim(str_replace ( " *" , "" , $description));
			$description = trim(str_replace ( "*" , "" , $description));
		}
		else{
			$description = "";
		}
	}
	//add embed code to post body
	//uses oembed gist plugin
	$embed_code = $url;

	//get author's username
	$path_exploded = explode("/", parse_url($url, PHP_URL_PATH));
	$author_username = esc_html($path_exploded[1]);
	//get author's GitHub profile
	$github_profile_url = 'https://github.com/'.$author_username;

	//format post content
	$break = " </br> ";
	$post_content = $description.$break."\n".$embed_code."\n".$break.__('This code was written by','link2post').' <a href="'.esc_url_raw($github_profile_url).'">'.esc_textarea($author_username).'</a>.'.$break.__('Original Gist','link2post').': <a href="'.esc_url_raw($url).'">'.esc_url_raw($url).'</a>';
	$post_type = (post_type_exists( "gist" ) ? 'gist' : 'post');
		
	if(empty($old_post_id)){
		//insert a Gist CPT
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

//do we need embed code?

//add a GitHub Gist CPT
function l2p_create_gist_cpt() {  
	//add check to make sure we should make cpt
	register_post_type( 'gist',array(
		'labels' => array(
		'name' => __( 'Gists','link2post' ),
		'singular_name' => __( 'Gist','link2post' ),
		'add_new_item' => __('Add New Gist','link2post'),
		'edit_item' => __( 'Edit Gist','link2post' ),
		'new_item' => __( 'New Gist','link2post' ),
		'view_item' => __( 'View Gist','link2post' ),
		'search_items' => __( 'Search Gists','link2post' ),
		'not_found' => __( 'No Gists Found','link2post' ),
		'not_found_in_trash' => __( 'No Gists Found In Trash','link2post' ),
		'all_items' => __( 'All Gists','link2post' ),
		),
		'public' => true,
		'has_archive' => true,
	)
	);
}

//handle search and archives

//widget for related posts