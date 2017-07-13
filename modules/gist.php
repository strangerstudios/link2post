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
	echo("CALLED BACK FOR ".$url);

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

	//grab the gist ID


	//try to figure out author (we need to figure out how to cross reference github usernames with WP users)
	//find author's username
	$author_username = $gist_page->find('.author > a'); 
	echo("</br>");
	echo($author_username);

	//find author's email
	$github_accnt_url = 'https://github.com/'.$author_username;
	$github_accnt_page = hQuery::fromUrl($github_accnt_url);
	//$author_email = $github_accnt_page->find('u-email'); 
	//can't get email if user is not logged in
	//https://www.eremedia.com/sourcecon/how-to-find-almost-any-github-users-email-address/


	//grab code and maybe pull description from first comment
	//NOT WORKING
	$first_comment = $gist_page->find('.blob-code > .pl-s1 > .pl-c > .pl-c');
	echo("</br>");
	echo($first_comment);

	//maybe store the code in a field we can search on later

	//add embed code to post body

	//insert a Gist CPT

//do we need embed code?

//add a GitHub Gist CPT

//handle search and archives

//widget for related posts
}