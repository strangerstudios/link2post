<?php 
	// Get/Update Settings
	$modules = l2p_get_modules();
	if(!empty($_POST['l2p_save']))
	{
		foreach($modules as $key => $value){
			if($_POST['l2p_'.$value['quick_name'].'_content_enabled']=='enabled'){
				update_option('l2p_'.$value['quick_name'].'_content_enabled', 'enabled');
				update_option('l2p_'.$value['quick_name'].'_cpt_enabled', $_POST['l2p_'.$value['quick_name'].'_cpt_enabled']);
			}else{
				update_option('l2p_'.$value['quick_name'].'_content_enabled', 'disabled');
				update_option('l2p_'.$value['quick_name'].'_cpt_enabled', 'disabled');
			}
		}
		echo "<meta http-equiv='refresh' content='0'>";
	}
	add_action('shutdown','l2p_flush');
	function l2p_flush(){
		flush_rewrite_rules(true);
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
			$modules = l2p_get_modules();
			foreach($modules as $key => $value){
			?>
				<tr><th>
					<label for="<?php echo($value['quick_name']);?>"><?php echo($value['quick_name']);?>:</label>
				</th>
				<td>
					<select name="l2p_<?php echo($value['quick_name']);?>_content_enabled">
						<option value="enabled" <?php if(get_option("l2p_".$value['quick_name']."_content_enabled")=="enabled"){echo('selected="selected"');}?> >Enabled</option>
						<option value="disabled" <?php if(get_option("l2p_".$value['quick_name']."_content_enabled")=="disabled"){echo('selected="selected"');}?> >Disabled</option>
					</select>
				</td>
				<td>
					<select name="l2p_<?php echo($value['quick_name']);?>_cpt_enabled">
						<option value="enabled" <?php if(get_option("l2p_".$value['quick_name']."_cpt_enabled")=="enabled"){echo('selected="selected"');}?> >Enabled</option>
						<option value="disabled" <?php if(get_option("l2p_".$value['quick_name']."_cpt_enabled")=="disabled"){echo('selected="selected"');}?> >Disabled</option>
					</select>
				</td></tr>
			<?php	
			}
			?>
		</table>
		<input name="l2p_save" type="submit" class="button-primary" value="Save Settings" />
	</form>
</div>