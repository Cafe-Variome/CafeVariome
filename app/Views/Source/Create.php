<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>

<div class="row">
	<div class="col">
		<h2><?= $title ?></h2>
	</div>
</div>
<hr>
<div class="row">
	<div class="col">
		<p>Please enter the source information below.</p>
	</div>
</div>
<?php if($message): ?>
	<div class="row">
		<div class="col">
			<div class="alert alert-info">
			<?php echo $message ?>
			</div>
		</div>
	</div>
<?php endif; ?>
<?php echo form_open($controllerName . '/Create'); ?>

<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Source Name', 'name'); ?>
		<?php echo form_input($name); ?>
		<small class="form-text text-muted">
			The source name can only contain alphanumeric characters and spaces.
		</small>
	</div>
	<div class=col-6>
		<?php echo form_label('Display Name', 'display_name'); ?>
		<?php echo form_input($display_name); ?>
	</div>
</div>
<div class="form-group row">
	<div class=col-6>
		<?php echo form_label('Owner Name', 'owner_name'); ?>
		<?php echo form_input($owner_name); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Owner Email', 'owner_email'); ?>
		<?php echo form_input($owner_email); ?>
	</div>

</div>
<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Source URI', 'uri'); ?>
		<?php echo form_input($uri); ?>
	</div>
	<div class=col-6>
		<?php echo form_label('Status', 'status'); ?>
		<?php echo form_dropdown($status); ?>
	</div>
</div>

<div class="form-group row">
	<div class=col-12>
		<?php echo form_label('Source Description', 'description'); ?>
		<?php echo form_textarea($description); ?>
	</div>
</div>
<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i>  Create Source
		</button>
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
	</div>
</div>

<?php echo form_close(); ?>
<?= $this->endSection() ?>
