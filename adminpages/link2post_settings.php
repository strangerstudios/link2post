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
			<?php 
			$modules = l2p_get_modules();
			foreach($modules as $key => $value){
			?>
				<tr>
					<th>
						<label for="<?php echo($value['quick_name']); ?>"><?php echo($value['quick_name']); ?></label>
					</th>
					<td>
						<select name="l2p_<?php echo($value['quick_name']);?>_content_enabled">
							<option value="enabled" <?php if(get_option("l2p_".$value['quick_name']."_content_enabled")=="enabled"){echo('selected="selected"');}?> ><?php _e('Yes', 'link2post'); ?></option>
							<option value="disabled" <?php if(get_option("l2p_".$value['quick_name']."_content_enabled"){echo('selected="selected"');}?> ><?php _e('No', 'link2post'); ?></option>
						</select>
					</td>
					<td>
						<select name="l2p_<?php echo($module_name);?>_cpt_enabled">
							<option value="enabled" <?php if(get_option("l2p_".$value['quick_name']."_cpt_enabled")=="enabled"){echo('selected="selected"');}?> ><?php _e('Yes', 'link2post'); ?></option>
							<option value="disabled" <?php if(get_option("l2p_".$value['quick_name']."_cpt_enabled")=="disabled"){echo('selected="selected"');}?> ><?php _e('No', 'link2post'); ?></option>
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