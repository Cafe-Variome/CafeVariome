<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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

<?php echo form_open($controllerName.'/Update/' . $id); ?>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Server', 'server_id', ['class' => 'form-label']); ?>
		<?php echo form_dropdown($server_id); ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Port', 'port', ['class' => 'form-label']); ?>
		<?php echo form_input($port); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Credential', 'credential_id', ['class' => 'form-label']); ?>
		<?php echo form_dropdown($credential_id); ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save" ></i> Save Changes
		</button>
		<a href="<?= base_url($controllerName . '/List'); ?>" class="btn btn-secondary bg-gradient-secondary">
			<i class="fa fa-ethernet"></i> View Proxy Servers
		</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
