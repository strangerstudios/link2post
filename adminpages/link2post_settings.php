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
		echo "<meta http-equiv='refresh' content='0'>";
	}
?>
<div class="wrap">
	<h2><?php _e('Link2Post', 'link2post'); ?></h2>
	<form method="post">
		<h3><?php _e('Supported Modules', 'link2post'); ?></h3>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php _e('Module', 'link2post'); ?></th>
					<th><?php _e('Format Post Content', 'link2post'); ?></th>
					<th><?php _e('Create and Use CPT', 'link2post'); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($module_names as $module_name) {
				?>
				<tr>
					<th>
						<label for="<?php echo $module_name; ?>"><?php echo $module_name ; ?></label>
					</th>
					<td>
						<select name="l2p_<?php echo($module_name);?>_content_enabled">
							<option value="enabled" <?php if(get_option("l2p_".$module_name."_content_enabled")=="enabled"){echo('selected="selected"');}?> ><?php _e('Yes', 'link2post'); ?></option>
							<option value="disabled" <?php if(get_option("l2p_".$module_name."_content_enabled")=="disabled"){echo('selected="selected"');}?> ><?php _e('No', 'link2post'); ?></option>
						</select>
					</td>
					<td>
						<select name="l2p_<?php echo($module_name);?>_cpt_enabled">
							<option value="enabled" <?php if(get_option("l2p_".$module_name."_cpt_enabled")=="enabled"){echo('selected="selected"');}?> ><?php _e('Yes', 'link2post'); ?></option>
							<option value="disabled" <?php if(get_option("l2p_".$module_name."_cpt_enabled")=="disabled"){echo('selected="selected"');}?> ><?php _e('No', 'link2post'); ?></option>
						</select>
					</td>
				</tr>
				<?php
				}
			?>
			</tbody>
		</table>
		<p class="submit">
			<input name="l2p_save" type="submit" class="button-primary" value="Save Settings" />
		</p>
	</form>
</div>