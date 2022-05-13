<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<?php if ($statusMessage): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-<?= $statusMessageType ?>">
				<?= $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php echo form_open($controllerName.'/Create'); ?>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Name', 'name'); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Server', 'server_id'); ?>
		<?php echo form_dropdown($server_id); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Port', 'port'); ?>
		<?php echo form_input($port); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Credential', 'credential_id'); ?>
		<?php echo form_dropdown($credential_id); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i> Create Proxy Server
		</button>
		<a href="<?= base_url($controllerName) . '/List';?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-ethernet"></i> View Proxy Servers
		</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
