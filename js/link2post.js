function showL2Presponses(str) {
    if (str.length == 0) { 
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("l2p_response").innerHTML = this.responseText;
            }
        };
        xmlhttp.open("GET", "gethint.php?q=" + str, true);
        xmlhttp.send();
    }
}

// initial_submission(text value)
function l2p_initial_submit(str) {
	//send xml request
	
	//if exists
	
		//set innerHTML of span to "would you like to update"
			
		//lock text box
		
		//hide button
		
	//else
	
		//call l2p process url
	
		//set innerHTML to link to new post
	
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