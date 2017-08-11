<?php 
	// Get/Update Settings
	$module_names = array();
	foreach (scandir(L2P_DIR.'/modules') as $filename) {
		if('.php'==substr($filename, -4)) {
			$module_name = substr($filename, 0, -4);
			$module_names[] = $module_name;
		}
	}
	if(!empty($_REQUEST['l2p_save']))
	{
		foreach($module_names as $module_name){
			if($_POST['l2p_'.$module_name.'_content_enabled']=='enabled'){
				update_option('l2p_'.$module_name.'_content_enabled', 'enabled');
				update_option('l2p_'.$module_name.'_cpt_enabled', $_POST['l2p_'.$module_name.'_cpt_enabled']);
			}else{
				update_option('l2p_'.$module_name.'_content_enabled', 'disabled');
			}
		}
	}
?>
<div class="wrap">
	<h2>Link2Post</h2>
	
	<form method="post">
		<h3>Enable/Disable Modules</h3>
		<table>
			<tr>
			<th>Module Name</th>
			<th>Format Post Content</th>
			<th>Use CPT</th>
			</tr>
			<?php 
			foreach($module_names as $module_name){
			?>
				<tr><th>
					<label for="<?php echo($module_name);?>"><?php echo($module_name);?>:</label>
				</th>
				<td>
					<select name="l2p_<?php echo($module_name);?>_content_enabled">
						<option value="enabled" <?php if(get_option("l2p_".$module_name."_content_enabled")=="enabled"){echo('selected="selected"');}?> >Enabled</option>
						<option value="disabled" <?php if(get_option("l2p_".$module_name."_content_enabled")=="disabled"){echo('selected="selected"');}?> >Disabled</option>
					</select>
				</td>
				<td>
					<select name="l2p_<?php echo($module_name);?>_cpt_enabled">
						<option value="enabled" <?php if(get_option("l2p_".$module_name."_cpt_enabled")=="enabled"){echo('selected="selected"');}?> >Enabled</option>
						<option value="disabled" <?php if(get_option("l2p_".$module_name."_cpt_enabled")=="disabled"){echo('selected="selected"');}?> >Disabled</option>
					</select>
				</td></tr>
			<?php	
			}
			?>
		</table>
		<input name="l2p_save" type="submit" class="button-primary" value="Save Settings" />
	</form>
</div>