//Start basic
function l2p_initial_submit() {
	console.log("running");
	console.log(jQuery('#l2p_URL_input'));
	var url = jQuery('#l2p_URL_input').val()
	console.log(url);
	jQuery.ajax({
	url: ajaxurl,type:'GET',timeout:5000,
		dataType: 'html',
		data: "action=echo_url&url="+url,
		error: function(xml){
			//timeout
		},
		success: function(response{
			console.log("success");
		})
	
	})
	
	
	});
}



/*
// initial_submission()
function l2p_initial_submit() {
	//send xml request
	console.log("running");
	var url = document.getElementById("l2p_URL_input").value;
	var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
    	console.log("running 1");
    	if (this.readyState == 4 && this.status == 200) {
    		console.log("running 2");
            document.getElementById("l2p_response").innerHTML = this.responseText;
            
			//if post already exists
			if(this.responseText != ""){
				//set innerHTML of span to "would you like to update"
				document.getElementByID("l2p_response").innerHTML = "Post exists at "+this.responseText+". Would you like to update?";
				//lock text box
				document.getElementByID("l2p_URL_input").hide();
				//hide button
				document.getElementByID("l2p_toolbar_submit").hide();
			} else{
	
				//call l2p process url
				document.getElementByID("l2p_response").innerHTML = "Creating Post";
				//set innerHTML to link to new post
		
			}
		}
    };
	xmlhttp.open("GET", "/TestWebsite/wp-content/plugins/link2post/includes/response.php?q=" + url, true);
    xmlhttp.send();
	
}

function l2p_update_submit(update){
	//update is boolean
	
	//if update
		//call l2p process url
	
		//set innerHTML to link to new post
		
		//not sure what happens if module can't update
	
	//else
		//set innerHTML to nothing
	
	//unhide button
	
	//unlock text box
}
*/