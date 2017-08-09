<?php 
	//wrap this in some error handling to detect form submission with an empty or invalid URL
	//use filter_var function to check URL http://php.net/manual/en/function.filter-var.php
	
	$l2p_js_url = plugin_dir_url( __FILE__ ) . "js/l2p_vue.js";
	
	
	remove_action( 'wp_enqueue_scripts', 'l2p_enqueue_scripts');
	remove_action( 'admin_enqueue_scripts', 'l2p_enqueue_scripts' );
	
	wp_enqueue_script("l2p_vue", 'https://unpkg.com/vue@2.0.3/dist/vue.js');
	wp_enqueue_script('l2p_jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);
	wp_enqueue_script("l2p_js_tools", plugins_url('link2post/js/link2post.js', L2P_DIR) , array("l2p_vue", "l2p_jquery"));
	wp_localize_script( "l2p_js_tools", "ajax_target",  admin_url( 'admin-ajax.php' ));
?>
<div class="wrap">
	<h2>Link2Post</h2>
	<div id="l2p_vue">
		<label for="l2purl" v-show="l2p_status==0">URL:</label>
		<input name="l2purl" type=text v-show="l2p_status==0" v-model="l2p_url"/>
		<span v-html="l2p_span_text"></span>
		<input type=button value="submit" v-show="l2p_status==0" v-on:click="l2p_submit"/>
		<input type=button value="update" v-show="l2p_status==1" v-on:click="l2p_update"/>
		<input type=button value="don't update" v-show="l2p_status==1" v-on:click="l2p_reset"/>
		<input type=button value="convert another" v-show="l2p_status==3" v-on:click="l2p_reset" />
		<input type=hidden value="true" id=l2p_on_tools_page />
    </div>
</div>
