<?php 
	// Get/Update Settings
	if(!empty($_REQUEST['l2p_save']))
	{
		update_option("l2p_gist_enabled", $_POST['l2p_gist_enabled']);
	}
?>
<div class="wrap">
	<h2>Link2Post</h2>
	
	<form method="post">
		<h3>Enable/Disable Modules</h3>
		<table>
			<tr>
				<th>
					<label for="gist">Gists:</label>
				</th>
				<td>
					<select name="l2p_gist_enabled">
						<option value="enabled" <?php if(get_option("l2p_gist_enabled")=="enabled"){echo('selected="selected"');}?> >Enabled</option>
						<option value="disabled" <?php if(get_option("l2p_gist_enabled")=="disabled"){echo('selected="selected"');}?> >Disabled</option>
					</select>
				</td>
			</tr>
		</table>
		<input name="l2p_save" type="submit" class="button-primary" value="Save Settings" />
	</form>
</div>