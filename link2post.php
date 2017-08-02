<?php
/*
Plugin Name: Link2Post
Plugin URI: http://www.strangerstudios.com/link2post/
Description: Automatically parse submitted URLs to create posts.
Version: .1
Author: strangerstudios, dlparker1005, andrewza
Author URI: http://www.strangerstudios.com
Text Domain: link2post
*/

/*
	Notes
	
	* Do we want to load a library for scraping HTML? https://github.com/duzun/hQuery.php
*/

/*
	Load modules
*/
define('L2P_DIR', dirname(__FILE__));
if(get_option("l2p_gist_enabled")=="enabled"){
	require_once(L2P_DIR . '/modules/gist.php');
}
//require_once(L2P_DIR . '/modules/youtube.php');

//require_once(L2P_DIR . '/l2p_ajax_functions.php');

/*
	Add Admin Page
*/
function l2p_admin_pages() {
	add_submenu_page( 'tools.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_tools', 'l2p_admin_tool_pages_main' );
	add_submenu_page( 'options-general.php', 'Link2Post', 'Link2Post', 'edit_posts', 'link2post_settings', 'l2p_admin_settings_pages_main' );
}
add_action('admin_menu', 'l2p_admin_pages');

function l2p_admin_tool_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_tools.php');
}

function l2p_admin_settings_pages_main() {
	require_once(dirname(__FILE__) . '/adminpages/link2post_settings.php');
}

function l2p_admin_bar_menu() {
	global $wp_admin_bar;
	
	if(!current_user_can('edit_posts'))
		return;
	
	$wp_admin_bar->add_menu( array(
		'id' => 'link2post',
		'parent' => 'new-content',
		'title' => __( 'Link2Post', 'link2post' ),
		'href' => get_admin_url(NULL, '/tools.php?page=link2post_tools') ) );

	$wp_admin_bar->add_menu( array(
		'id' => 'l2p_input',
		'parent' => 'link2post',
		'title' => '<div id="l2p_vue2">
			<label for="l2purl" v-show="l2p_status==0">URL:</label>
			<input name="l2purl" type=text v-show="l2p_status==0" v-model="l2p_url"/>
			<span>{{l2p_span_text}}</span>
			<input type=button value="submit" v-show="l2p_status==0" v-on:click="l2p_submit"/>
			<input type=button value="update" v-show="l2p_status==1" v-on:click="l2p_update"/>
			<input type=button value="don\'t update" v-show="l2p_status==1" v-on:click="l2p_reset"/>
			<input type=button value="convert another" v-show="l2p_status==3" v-on:click="l2p_reset" />
    	</div>
    	<script src="https://unpkg.com/vue@2.0.3/dist/vue.js"></script>
		<script src="https://unpkg.com/axios@0.12.0/dist/axios.min.js"></script>
		<script src="https://unpkg.com/lodash@4.13.1/lodash.min.js"></script>
		<script>
			var l2p_vue2 = new Vue({
			  el: \'#l2p_vue2\',
			  data: {
				//0: No submission yet
				//1: Submission, asking to update
				//2: Updating
				//3: Updated/Created
		
				l2p_status: 0,
				l2p_url: \'\',
				l2p_span_text: \'\',
				l2p_old_post_id: 0
			  },
			  methods:{
				l2p_submit: function(){
					//add more validation to url
					if(this.l2p_url != \'\'){
						axios.get(\'http://localhost:8888/TestWebsite/wp-content/plugins/link2post/l2p_ajax_functions.php?l2pfunc=l2p_can_update&url=\'+this.l2p_url)
						.then(function (response) {
							data = response.data
							if(data.new_post_created == true){
								l2p_vue2.l2p_span_text = \'Your new post has been created at \'+data.new_post_url
								l2p_vue2.l2p_status = 3
							}
							else if(data.can_update == false){
								l2p_vue2.l2p_span_text = \'An existing post has been found for this url at \'+data.old_post_url+\', but it is not able to be updated.\'
								l2p_vue2.l2p_status = 3
							}
							else{
								l2p_vue2.l2p_span_text = \'An existing post has been found for this url at \'+data.old_post_url+\', update?\'
								l2p_vue2.l2p_old_post_id = data.old_post_id
								l2p_vue2.l2p_status = 1
							}
						})
						.catch(function (error) {
						 console.log("got error "+error.data)
						})
					}
				},
				l2p_update: function(){
					this.l2p_status = 2
					this.l2p_span_text = "Updating..."
					axios.get(\'http://localhost:8888/TestWebsite/wp-content/plugins/link2post/l2p_ajax_functions.php?l2pfunc=l2p_create_post&url=\'+this.l2p_url+\'&old_post_id=\'+this.l2p_old_post_id)
						.then(function (response) {
							data = response.data
							l2p_vue2.l2p_span_text = \'Your new post has been updated at \'+data.url+\'.\'
							l2p_vue2.l2p_status = 3
						})
						.catch(function (error) {
							console.log("got error "+error.data)
						})
				},
				l2p_reset: function(){
					this.l2p_url = \'\'
					this.l2p_span_text = \'\'
					this.l2p_status = 0
				}
			  }
			})
		  </script>') );
}
add_action('admin_bar_menu', 'l2p_admin_bar_menu');
?>  