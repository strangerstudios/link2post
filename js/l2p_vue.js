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