<?php
//filter ___ to add gist module to link2post
function l2p_add_gist_module($modules) {
    $modules["gist.github.com"] = 'l2p_gist_callback';
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_gist_module');

//callback to process a URL that is from gist.github.com_address
function l2p_gist_callback($url){
	//check if we've already processed this URL
	echo("CALLED BACK TO GIST FOR ".$url);

	//Set up hQuery
	require_once(dirname(__FILE__).'/../lib/hQuery/hquery.php');
	duzun\hQuery::$cache_path = dirname(__FILE__).'/../lib/hQuery/cache/';
	$gist_page = hQuery::fromUrl($url);

	//grab title from title element
	echo("</br>");
	$title = $gist_page->find('.gist-header-title > a'); //specific to github, but title tag doesnt point to title doesnt give correct value
	echo($title);

	//grab description from meta element
	echo("</br>");
	$description = $gist_page->find('.repository-meta-content');
	echo($description);

	//try to figure out author (we need to figure out how to cross reference github usernames with WP users)
	//find author's username
	$path_exploded = explode("/", parse_url($url, PHP_URL_PATH));
	$author_username = $path_exploded[1];
	echo("</br>");
	echo($author_username);

	//find author's email
	$github_accnt_url = 'https://github.com/'.$author_username;
	$github_accnt_page = hQuery::fromUrl($github_accnt_url);
	//$author_email = $github_accnt_page->find('u-email'); 
	//can't get email if user is not logged in
	//https://www.eremedia.com/sourcecon/how-to-find-almost-any-github-users-email-address/
	
	//grab the gist ID
	$gist_ID = $path_exploded[2];
	echo("</br>");
	echo($gist_ID);

	//maybe store the code in a field we can search on later
	$github_raw_code_url = $url.'/raw';
	$github_raw_code_page = hQuery::fromUrl($github_raw_code_url);
	echo("</br>");
	echo(htmlspecialchars($github_raw_code_page)); 
	
	
	//grab code and maybe pull description from first comment
	//Single-line Comments
	$single_comments = $gist_page->find('.pl-c');
	echo("</br>");
	echo($single_comments);
	
	//Multiline Comments
	//referencing http://www.justin-cook.com/2006/03/31/php-parse-a-string-between-two-strings/
	$code = " ".htmlspecialchars($github_raw_code_page);
	$start = '/*';
	$end = '*/';
	$ini = strpos($code,$start);
	if ($ini == 0){
		$multiline_comment = "";
	}
	else{
		$ini += strlen($start);   
		$len = strpos($code,$end,$ini) - $ini;
		$multiline_comment = substr($code,$ini,$len);
	}
	echo("</br>");
	echo($multiline_comment);


	//add embed code to post body
	$embed_code = '<script src="'.$url.'.js"></script>';
	echo("</br>");
	echo(htmlspecialchars($embed_code));

	//insert a Gist CPT
}

//do we need embed code?

//add a GitHub Gist CPT
function create_gist_cpt() {  
  register_post_type( 'gist',
    array(
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
add_action( 'init', 'create_gist_cpt' );

//handle search and archives

//widget for related posts