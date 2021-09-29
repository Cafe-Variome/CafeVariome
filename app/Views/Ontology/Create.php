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

<?php echo form_open($controllerName.'/Create'); ?>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Name', 'name'); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Node Type', 'node_type'); ?>
		<?php echo form_input($node_type); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Node Key', 'node_key'); ?>
		<?php echo form_input($node_key); ?>
	</div>
</div>

<div class="form-group row">
	<div class="col-6">
		<?php echo form_label('Key Prefix', 'key_prefix'); ?>
		<?php echo form_input($key_prefix); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="form-group row">
	<div class="col">
		<?php echo form_label('Description', 'desc'); ?>
		<?php echo form_textarea($desc) ?>
	</div>
</div>

<div class="form-group row">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
			<i class="fa fa-plus"></i> Create Ontology
		</button>
		<a href="<?= base_url($controllerName);?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-project-diagram"></i> View Ontologies
		</a>
	</div>
</div>

<?php echo form_close(); ?>

<?= $this->endSection() ?>
