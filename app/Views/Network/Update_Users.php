<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<div class="row">
	<div class="col">
	<?php if($group_type === 'master'): ?>
		<h4>Edit Users for Network: <?php echo $name; ?></h4>
	<?php else: ?>
		<h4>Edit Users for Group: <?php echo $name; ?></h4>
	<?php endif; ?>
    </div>
</div>
<?php echo form_open($controllerName.'/Update_Users/' . $user_id . '/' . $isMaster, ['name' => 'editUser']); ?>
<?php echo form_hidden(['installation_key' => $installation_key]); ?>  
<?php if(isset($users) || isset($group_users)): ?>

<div class="form-group row">
	<div class="col-2">
		<?php echo form_label('Remote User Email', 'remote_user_email'); ?>
	</div>
	<div class="col-8">
		<?php echo form_input($remote_user_email); ?>
	</div>
	<div class="col-2">
		<button type="button" onclick="addRemoteUser()">Add User</button>
	</div>
</div>
<div class="row">
	<div class="col">
		<h4 style="padding-top: 10px;">Users</h4>   
		<p> (R) signifies the user is a Remote user.</p>
	</div>    
</div>
<div class="form-group row">
	<div class="col">
		<select size="10" multiple id="mng_left" style="padding-top: 10px;" class="form-control">
		</select>
	</div>
	<div class="col">
		<input type="button" onclick="moveItem(event);" class="form-control btn btn-success btn-lg btn-block" value="Add &gt;&gt;"/><br>
		<input type="button" onclick="removeItem(event);" class="form-control btn btn-danger btn-lg btn-block" value="&lt;&lt; Remove"/>
	</div>
	<div class="col">
		<select size="10" multiple id="mng_right" name="groups[]" class="groupsSelected form-control" style="padding-top: 10px;">
		</select>
	</div>
</div>
	<?php if(isset($users)) : foreach ($users as $user): ?>
			<?php if ($user->remote) : ?>
			<script type="text/javascript">
				$("#mng_left").append($("<option></option>")
				.attr("value",'<?php echo $user->id; ?>')
				.text('<?php echo $user->username . ' (R)'; ?>')); 
			</script>
		<?php else : ?>
			<script type="text/javascript">
				$("#mng_left").append($("<option></option>")
				.attr("value",'<?php echo $user->id; ?>')
				.text('<?php echo $user->username; ?>')); 
			</script>
		<?php endif; ?>
	<?php endforeach;
endif; ?>

	<?php if(isset($group_users)) : foreach ($group_users as $group_user): ?>

			<?php if ($group_user['remote']) : ?>
			<script type="text/javascript">
				$("#mng_right").append($("<option></option>")
				.attr("value",'<?php echo $group_user['id']; ?>')
				.text('<?php echo $group_user['username'] . ' (R)'; ?>')); 
			</script>
		<?php else : ?>
			<script type="text/javascript">
				$("#mng_right").append($("<option></option>")
				.attr("value",'<?php echo $group_user['id']; ?>')
				.text('<?php echo $group_user['username']; ?>')); 
			</script>
		<?php endif; ?>
	<?php endforeach;
endif;?>
<?php else: ?>
   <p><span class="label label-important">There are no users present in this group.</span></p>
<?php endif; ?>
<?php if(isset($sources_left) || isset($sources_right)): ?>
<div class="form-group row">
	<div class="col">
		<select size="10" multiple id="sources_left" style="padding-top: 10px;" class="form-control"></select>
	</div>
	<div class="col">
		<input type="button" onclick="moveItem(event);" class="form-control btn btn-success btn-lg btn-block" value="Add &gt;&gt;"/>
		<input type="button" onclick="removeItem(event);" class="form-control btn btn-danger btn-lg btn-block" value="&lt;&lt; Remove"/>    
	</div>
	<div class="col">
		<select size="10" multiple id="sources_right" name="sources[]" class="sourcesSelected form-control" style="padding-top: 10px;"></select>    
	</div>
</div>
	<?php if(isset($sources_left)) : foreach ($sources_left as $key => $value): ?>
		<script type="text/javascript">
			$("#sources_left").append($("<option></option>")
			.attr("value",'<?php echo $key ?>')
			.text('<?php echo $value ?>')); 
		</script>
	<?php endforeach;
endif; ?>

	<?php if(isset($sources_right)) : foreach ($sources_right as $key => $value): ?>
		<script type="text/javascript">
			$("#sources_right").append($("<option></option>")
			.attr("value",'<?php echo $key ?>')
			.text('<?php echo $value ?>')); 
		</script>
	<?php endforeach;
endif;?>
<?php else: ?>
	<?php if($group_type !== 'master'): ?>
		<p><span class="label label-important">There are no sources present in this installation.</span></p>
	<?php endif; ?>
<?php endif; ?>

<?php echo form_hidden('isMaster', $isMaster); ?>                
<?php echo form_hidden('id', $user_id); ?>
<?php echo form_hidden($csrf); ?>
<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary" onclick="edit_user_network_groups_sources();">
			<i class="fa fa-save"></i>  Save
		</button>
		<?php if($group_type === 'master'): ?>
			<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary" >
				<i class="fa fa-backward"></i> Go back
			</a>
		<?php else: ?>
			<a href="<?php echo base_url('NetworkGroup'); ?>" class="btn btn-secondary" >
			<i class="fa fa-backward"></i> Go back
			</a>
		<?php endif; ?>        
	</div>
</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>