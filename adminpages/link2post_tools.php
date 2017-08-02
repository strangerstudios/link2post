<?php 
	//wrap this in some error handling to detect form submission with an empty or invalid URL
	//use filter_var function to check URL http://php.net/manual/en/function.filter-var.php
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
<script src="https://unpkg.com/axios@0.12.0/dist/axios.min.js"></script>
<script src="https://unpkg.com/lodash@4.13.1/lodash.min.js"></script>
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
        l2p_span_text: '',
        l2p_old_post_id: 0
      },
      methods:{
      	l2p_submit: function(){
      		//add more validation to url
      		if(this.l2p_url != ''){
      			axios.get('http://localhost:8888/TestWebsite/wp-content/plugins/link2post/l2p_ajax_functions.php?l2pfunc=l2p_can_update&url='+this.l2p_url)
                .then(function (response) {
                	data = response.data
					if(data.new_post_created == true){
						l2p_vue.l2p_span_text = 'Your new post has been created at '+data.new_post_url
						l2p_vue.l2p_status = 3
					}
					else if(data.can_update == false){
						l2p_vue.l2p_span_text = 'An existing post has been found for this url at '+data.old_post_url+', but it is not able to be updated.'
						l2p_vue.l2p_status = 3
					}
					else{
						l2p_vue.l2p_span_text = 'An existing post has been found for this url at '+data.old_post_url+', update?'
						l2p_vue.l2p_old_post_id = data.old_post_id
						l2p_vue.l2p_status = 1
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
			axios.get('http://localhost:8888/TestWebsite/wp-content/plugins/link2post/l2p_ajax_functions.php?l2pfunc=l2p_create_post&url='+this.l2p_url+'&old_post_id='+this.l2p_old_post_id)
                .then(function (response) {
                	data = response.data
					l2p_vue.l2p_span_text = 'Your new post has been updated at '+data.url+'.'
					l2p_vue.l2p_status = 3
                })
                .catch(function (error) {
					console.log("got error "+error.data)
                })
      	},
      	l2p_reset: function(){
      		this.l2p_url = ''
      		this.l2p_span_text = ''
      		this.l2p_status = 0
      	}
      }
    })
  </script>
