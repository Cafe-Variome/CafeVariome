<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $statusMessage ?>
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

<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Users Granted Access', 'users'); ?>
		<?php echo form_multiselect('users[]', $users , $selectedUsers, ['id'=> 'users', 'class' => 'form-control']); ?>
	</div>
	<div class=col-6>
	</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Sources in Network', 'sources'); ?>
		<?php echo form_multiselect('sources[]', $sources , $selectedSources, ['id'=> 'sources', 'class' => 'form-control']); ?>
	</div>
	<div class=col-6>
	</div>
</div>

<?php echo form_hidden('isMaster', $isMaster); ?>                
<?php echo form_hidden('name', $name); ?>                
<?php echo form_hidden('id', $user_id); ?>
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