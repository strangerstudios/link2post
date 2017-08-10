<?php
function l2p_add_codepen_module($modules) {
    $modules["codepen.io"] = array('callback'=>'l2p_codepen_callback', 'can_update'=>true);
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_codepen_module');

function l2p_codepen_callback($url, $old_post_id=NULL, $return_result=false){
	global $current_user;
	//Set up selector
	require_once(L2P_DIR.'/lib/selector.php');
	$html = wp_remote_retrieve_body(wp_remote_get($url));

	//grab title from title element
		$title = l2p_SelectorDOM::select_element('title', $html);
		if(!empty($title) && !empty($title['text']))
					$title = sanitize_text_field($title['text']);
		$objToReturn->title = $title;
	
		//scrape the description
		$description = l2p_SelectorDOM::select_element('meta[property=og:description]', $html);
		if(!empty($description) && !empty($description['attributes']) && !empty($description['attributes']['content']))
			$description = sanitize_text_field($description['attributes']['content']);
		else{
			$description = "";
		}
			
		//get author name
		$author_name = l2p_SelectorDOM::select_element('.pen-owner-link', $html);
		if(!empty($author_name) && !empty($author_name['text']))
			$author_name = sanitize_text_field($author_name['text']);

	
		//get aurthor url
		$author_page = l2p_SelectorDOM::select_element('.pen-owner-link', $html);
		if(!empty($author_page) && !empty($author_page["attributes"]['href']))
			$author_page = sanitize_text_field($author_page["attributes"]['href']);
		$author_url = 'https://codepen.io/'.$author_page;
		
		//add embed code to post body
		$embed_code = '<p data-height="265" data-theme-id="0" data-slug-hash="'.substr($url, -6).'" data-default-tab="css,result" data-user="'.$author_page.'" data-embed-version="2" data-pen-title="'.$title.'" class="codepen">See the Pen <a href="'.$url.'">'.$title.'</a> by '.$author_name.' (<a href="'.$author_url.'">@'.$author_page.'</a>) on <a href="https://codepen.io">CodePen</a>.</p>
<script async src="https://production-assets.codepen.io/assets/embed/ei.js"></script>';
		
		//format post content
		$break = " </br> ";
		$post_content = $description.$break."\n".$embed_code."\n".$break.'This pen was made by <a href="'.$author_url.'">'.$author_name.'</a>.'.$break.'Original Pen: <a href="'.$url.'">'.$url.'</a>';
		//echo $post_content;

		if(empty($old_post_id)){
		//insert a Codepen CPT
		$postarr = array(
				'post_type' => 'codepen',
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
			'post_type' => 'codepen',
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

//add a Codepen CPT
function create_codepen_cpt() {  
  register_post_type( 'codepen',
    array(
      'labels' => array(
        'name' => __( 'Pens' ),
        'singular_name' => __( 'Pen' ),
        'add_new_item' => __('Add New Pen'),
        'edit_item' => __( 'Edit Pen' ),
        'new_item' => __( 'New Pen' ),
		'view_item' => __( 'View Pen' ),
		'search_items' => __( 'Search Pens' ),
		'not_found' => __( 'No Pens Found' ),
		'not_found_in_trash' => __( 'No Pens Found In Trash' ),
		'all_items' => __( 'All Pens' ),
      ),
      'public' => true,
      'has_archive' => true,
    )
  );
}
add_action( 'init', 'create_codepen_cpt' );

//handle search and archives

//widget for related posts
