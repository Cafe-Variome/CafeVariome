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
			<div class="alert alert-<?= $statusMessageType ?>">
			<?php echo $statusMessage ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php echo form_open($controllerName. '/Update/' . $discovery_group_id); ?>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Name', 'name'); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Network', 'network'); ?>
		<?= form_dropdown($network) ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Policy', 'policy'); ?>
		<?= form_dropdown($policy) ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Users', 'users'); ?>
		<?= form_multiselect($users) ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Sources', 'sources'); ?>
		<?= form_multiselect($sources) ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-12">
		<?php echo form_label('Description', 'description'); ?>
		<?php echo form_textarea($description); ?>
	</div>

</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save" ></i> Save Changes
		</button>
		<a href="<?php echo base_url($controllerName); ?>" class="btn btn-secondary bg-gradient-secondary"><i class="fas fa-fw fa-user-friends"></i> View Discovery Groups</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
