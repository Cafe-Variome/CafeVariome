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

<?php echo form_open($controllerName.'/Update/' . $ontologyRelationship->getID()); ?>
<?php echo form_hidden(['ontology_relationship_id' => $ontologyRelationship->getID()]); ?>
<?php echo form_hidden(['ontology_id' => $ontologyRelationship->ontology_id]); ?>
<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Ontology Name:', 'ontology_name'); ?>
		<?= $ontologyRelationship->ontology_name ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col-6">
		<?php echo form_label('Name', 'name'); ?>
		<?php echo form_input($name); ?>
	</div>
	<div class="col-6">
	</div>
</div>

<div class="row mb-3">
	<div class="col">
		<button type="submit" name="submit" class="btn btn-primary bg-gradient-primary">
			<i class="fa fa-save"></i> Save Changes
		</button>
		<a href="<?= base_url($controllerName) . '/List/' .  $ontologyRelationship->ontology_id;?>" class="btn btn-secondary bg-gradient-secondary" >
			<i class="fa fa-project-diagram"></i> View Ontology Relationships of <?= $ontologyRelationship->ontology_name ?>
		</a>
	</div>
</div>
<?php echo form_close(); ?>

<?= $this->endSection() ?>
