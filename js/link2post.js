jQuery( document ).ready(function() {
	var l2p_vue = new Vue({
	  el: '#l2p_vue',
	  data: {
		//0: No submission yet
		//1: Submission, asking to update
		//2: Updating
		//3: Updated/Created
		l2p_on_tools_page: jQuery("#l2p_on_tools_page").val(),
		l2p_status: 0,
		l2p_url: '',
		l2p_span_text: '',
		l2p_old_post_id: 0,
		l2p_showAdminbar:false
	  },
	  methods:{
		l2p_submit: function(){
			//add more validation to url
			if(this.l2p_url != ''){
				jQuery.post(
				 ajax_target, 
				 {
					'action': 'l2p_submit',
					'l2p_url':  this.l2p_url
				}, 
				function(response){
					response = JSON.parse(response)
					if(response.new_post_created == true){
						if(l2p_vue.l2p_on_tools_page=="true"){
							l2p_vue.l2p_span_text = 'Your new post has been created at <a href="'+response.new_post_url+'">'+response.new_post_url+'</a>.'
						}
						else{
							l2p_vue.l2p_span_text = 'Post created <a href="'+response.new_post_url+'" style="display:inline-block">here</a>.'
						}
						l2p_vue.l2p_status = 3
					}
					else if(response.can_update == false){
						if(l2p_vue.l2p_on_tools_page=="true"){
							l2p_vue.l2p_span_text = 'An existing post has been found for this url at <a href="'+response.old_post_url+'">'+response.old_post_url+'</a>, but it is not able to be updated.'
						}
						else{
							l2p_vue.l2p_span_text = 'Existing post found <a href="'+response.old_post_url+'" style="display:inline-block">here</a>, but cannot be updated.'
						}
						l2p_vue.l2p_status = 3
					}
					else{
						if(l2p_vue.l2p_on_tools_page=="true"){
							l2p_vue.l2p_span_text = 'An existing post has been found for this url at <a href="'+response.old_post_url+'">'+response.old_post_url+'</a>, update?'
						}
						else{
							l2p_vue.l2p_span_text = 'Existing post found <a href="'+response.old_post_url+'" style="display:inline-block">here</a>, update?'
						}
						l2p_vue.l2p_old_post_id = response.old_post_id
						l2p_vue.l2p_status = 1
					}
				})

			}
		},
		l2p_update: function(){
			this.l2p_status = 2
			this.l2p_span_text = "Updating..."
			jQuery.post(
				 ajax_target, 
				 {
					'action': 'l2p_update',
					'l2p_url':  l2p_vue.l2p_url,
					'l2p_old_post_id': l2p_vue.l2p_old_post_id
				}, 
				function(response){
					response = JSON.parse(response)
					if(l2p_vue.l2p_on_tools_page=="true"){
						l2p_vue.l2p_span_text = 'Your post has been updated at <a href="'+response.url+'">'+response.url+'</a>.'
					}
					else{
						l2p_vue.l2p_span_text = 'Post updated <a href="'+response.url+'" style="display:inline-block">here</a>.'
					}					
					l2p_vue.l2p_status = 3
				})
		},
		l2p_reset: function(){
			this.l2p_url = ''
			this.l2p_span_text = ''
			this.l2p_status = 0
		}
	  }
	})
});