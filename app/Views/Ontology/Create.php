<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?>
</h2>
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

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name', ['class' => 'form-label']); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Node Type', 'node_type', ['class' => 'form-label']); ?>
		<?php echo form_input($node_type); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Node Key', 'node_key', ['class' => 'form-label']); ?>
		<?php echo form_input($node_key); ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Key Prefix', 'key_prefix', ['class' => 'form-label']); ?>
		<?php echo form_input($key_prefix); ?>
	</div>
	<div class="col-6">
		<?php echo form_label('Term Name', 'term_name', ['class' => 'form-label']); ?>
		<?php echo form_input($term_name); ?>
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<?php echo form_label('Description', 'desc', ['class' => 'form-label']); ?>
		<?php echo form_textarea($desc) ?>
	</div>
</div>

<div class="row mb-3">
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
