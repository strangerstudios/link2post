<?php 
	//wrap this in some error handling to detect form submission with an empty or invalid URL
	//use filter_var function to check URL http://php.net/manual/en/function.filter-var.php
	l2p_processURL(); 
	$l2p_js_url = plugin_dir_url( __FILE__ ) . "js/l2p_vue.js";
?>
<div class="wrap">
	<h2>Link2Post</h2>
	<div id="l2p_vue">
		<label for="l2purl" v-show="l2p_status==0">URL:</label>
		<input name="l2purl" type=text v-show="l2p_status==0" v-model="l2p_url"/>
		<span>{{l2p_span_text}}</span>
		<input type=button value="submit" v-show="l2p_status==0" v-on:click="l2p_submit"/>
		<input type=button value="update" v-show="l2p_status==1" v-on:click="l2p_update"/>
		<input type=button value="don't update" v-show="l2p_status==1" v-on:click="l2p_reset"/>
		<input type=button value="convert another" v-show="l2p_status==3" v-on:click="l2p_reset" />
    </div>
</div>
<script src="https://unpkg.com/vue@2.0.3/dist/vue.js"></script>
<script>
    var l2p_vue = new Vue({
      el: '#l2p_vue',
      data: {
      	//0: No submission yet
      	//1: Submission, asking to update
      	//2: Updating
      	//3: Updated/Created
      	
        l2p_status: 0,
        l2p_url: '',
        l2p_span_text: ''
      },
      methods:{
      	l2p_submit: function(){
      		//add more validation to url
      		if(this.l2p_url != ''){
      			//php call 1
      				//does post already exist?
      					//if not, create, return 1. Set status to 3
      					//if so, is the post able to be updated?
      						//if not, return -1. set status to 0 with message in span
      						//if so, return 2. set status to 2
      		}
      	},
      	l2p_update: function(){
			this.l2p_status = 2
			this.l2p_span_text = "Updating..."
			//php call 2, update post
			this.l2p_span_text = "Your new post has been updated at (url)."
			this.l2p_status = 3
      	},
      	l2p_reset: function(){
      		this.l2p_url = ''
      		this.l2p_span_text = ''
      		this.l2p_status = 0
      	}
      }
    })
  </script>
