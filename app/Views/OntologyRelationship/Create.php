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

<?php echo form_open($controllerName.'/Create/' . $ontology_id); ?>
<?php echo form_hidden(['ontology_id' => $ontology_id]); ?>

	<div class="form-group row">
		<div class="col-6">
			<?php echo form_label('Ontology Name:', 'ontology_name'); ?>
			<?= $ontology_name ?>
		</div>
		<div class="col-6">
		</div>
	</div>

	<div class="form-group row">
		<div class="col-6">
			<?php echo form_label('Name', 'name'); ?>
			<?php echo form_input($name); ?>
		</div>
		<div class="col-6">
		</div>
	</div>

	<div class="form-group row">
		<div class="col">
			<button type="submit" name="submit" class="btn btn-success bg-gradient-success">
				<i class="fa fa-plus"></i> Create Ontology Relationship
			</button>
			<a href="<?= base_url($controllerName) . '/List/' . $ontology_id;?>" class="btn btn-secondary bg-gradient-secondary" >
				<i class="fa fa-project-diagram"></i> View Ontology Prefixes of <?= $ontology_name ?>
			</a>
		</div>
	</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
