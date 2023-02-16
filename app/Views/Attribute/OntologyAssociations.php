<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $attribute->name ?>'
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

<?= form_open($controllerName.'/OntologyAssociations/' . $attribute->getID()) ?>
<input type="hidden" name="attribute_id" id="attribute_id" value="<?= $attribute->getID() ?>" />
<input type="hidden" id="csrf_token" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />

<div class="row mb-3">
	<div class="col-3">
		<?= form_label('Ontology', 'ontology'); ?>
		<?= form_dropdown($ontology); ?>
	</div>
	<div class="col-3">
		<?= form_label('Prefix', 'prefix'); ?>
		<?= form_dropdown($prefix); ?>
	</div>
	<div class="col-3">
		<?= form_label('Relationship', 'relationship'); ?>
		<?= form_dropdown($relationship); ?>
	</div>

	<div class="col-3">
		<br>
		<div class="spinner-border text-warning spinner-border-sm" role="status" id="spinner" style="display:none;">
			<span class="sr-only">Loading...</span>
		</div>
		<button type="submit" name="submit" class="btn btn-success bg-gradient-success" id="ontologyassociation_btn">
			<i class="fa fa-plus"></i> Create Attribute Ontology Association
		</button>
	</div>
</div>
<?= form_close() ?>
<hr>

<table class="table table-bordered table-striped table-hover" id="attributeontologiestable">
	<thead>
	<tr>
		<th>Ontology Name</th>
		<th>Prefix</th>
		<th>Relationship</th>
		<th>Actions</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($attributeOntologyAssociations as $association): ?>
		<tr>
			<td><?= $association->ontology_name ?></td>
			<td><?= $association->prefix_name ?></td>
			<td><?= $association->relationship_name ?></td>
			<td>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url($controllerName. '/DeleteAssociation'). "/" . $association->id ; ?>">
					<i class="fa fa-trash"></i> Delete Association
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url($controllerName . '/List/' . $attribute->source_id) ?>">
			<i class="fa fa-arrow-left"></i> Go Back to List of Attributes for <?= $attribute->source_name ?>
		</a>
		<a class="btn btn-primary bg-gradient-primary" target="_blank" href="<?= base_url('Ontology') ?>">
			<i class="fa fa-project-diagram"></i> View Ontologies
		</a>
		<a class="btn btn-success bg-gradient-success" target="_blank" href="<?= base_url('Value/List/' . $attribute->getID()) ?>">
			<i class="fa fa-list"></i> View Values of <?= $attribute->name ?>
		</a>
	</div>
</div>
<?= $this->endSection() ?>
