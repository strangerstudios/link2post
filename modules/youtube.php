<?php
//filter ___ to add gist module to link2post
function l2p_add_youtube_module($modules) {
    $modules["www.youtube.com"] = 'l2p_youtube_callback';
    return $modules;
}
add_filter('l2p_modules', 'l2p_add_youtube_module');

//callback to process a URL that is from gist.github.com_address
function l2p_youtube_callback($url){
	//check if we've already processed this URL
	echo("CALLED BACK TO YOUTUBE FOR ".$url);

	//Set up hQuery
	require_once(dirname(__FILE__).'/../lib/hQuery/hquery.php');
	duzun\hQuery::$cache_path = dirname(__FILE__).'/../lib/hQuery/cache/';
	$youtube_page = hQuery::fromUrl($url);

	//grab title from title element
	echo("</br>");
	$title = $youtube_page->find('title'); 
	echo($title);

	//grab description from meta element
	echo("</br>");
	$description = $youtube_page->find('#eow-description');
	echo($description);

	//find author's username
	$author_username = $youtube_page->find('.yt-user-info > .g-hovercard'); 
	echo("</br>");
	echo($author_username);
}