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
		$title="Title is empty";
	}
	//grab description from multiline comment
	$raw_code_url = $url.'/raw';
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
	$embed_code = $url;

	//get author's username
	$path_exploded = explode("/", parse_url($url, PHP_URL_PATH));
	$author_username = esc_html($path_exploded[1]);
	//get author's GitHub profile
	$github_profile_url = 'https://github.com/'.$author_username;

	//format post content
	$break = " </br> ";
	$post_content = $description.$break."\n".$embed_code."\n".$break.'This code was written by <a href="'.$github_profile_url.'">'.$author_username.'</a>.'.$break.'Original Gist: <a href="'.$url.'">'.$url.'</a>';
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
		'name' => __( 'Gists' ),
		'singular_name' => __( 'Gist' ),
		'add_new_item' => __('Add New Gist'),
		'edit_item' => __( 'Edit Gist' ),
		'new_item' => __( 'New Gist' ),
		'view_item' => __( 'View Gist' ),
		'search_items' => __( 'Search Gists' ),
		'not_found' => __( 'No Gists Found' ),
		'not_found_in_trash' => __( 'No Gists Found In Trash' ),
		'all_items' => __( 'All Gists' ),
		),
		'public' => true,
		'has_archive' => true,
	)
	);
}

//handle search and archives

//widget for related posts