<?= $this->extend('layout/dashboard') ?>
<?= $this->section('content') ?>
<h2 class="mt-4">
	<?= $title ?> of '<?= $source_name ?>'
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
<table class="table table-bordered table-striped table-hover" id="attributestable">
	<thead>
		<tr>
			<th>Name</th>
			<th>Display Name</th>
			<th>Type</th>
			<th>Storage Location</th>
			<th>Actions</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($attributes as $attribute): ?>
		<tr>
			<td><?= $attribute->name ?></td>
			<td><?= $attribute->display_name ?></td>
			<td><?= $attribute->type_text ?></td>
			<td><?= $attribute->storage_location ?></td>
			<td>
				<a class="btn btn-sm btn-success bg-gradient-success" href="<?= base_url('Value/List') . '/' . $attribute->getID() ?>">
					<i class="fa fa-list"></i> View Values
				</a>
				<a class="btn btn-sm btn-warning bg-gradient-warning" href="<?= base_url($controllerName . '/Update') . '/' . $attribute->getID() ?>">
					<i class="fa fa-edit"></i> Edit Attribute
				</a>
				<a class="btn btn-sm btn-danger bg-gradient-danger" href="<?= base_url('AttributeMapping/List') . '/' . $attribute->getID() ?>">
					<i class="fa fa-map-signs"></i> View Mappings
				</a>
				<?php if($attribute->type == ATTRIBUTE_TYPE_ONTOLOGY_TERM): ?>
				<a class="btn btn-sm btn-secondary bg-gradient-secondary" href="<?= base_url($controllerName . '/OntologyAssociations') . '/' . $attribute->getID() ?>">
					<i class="fa fa-project-diagram"></i> View Ontology Associations
				</a>
				<?php endif; ?>
				<a class="btn btn-sm btn-info bg-gradient-info" href="<?= base_url($controllerName . '/Details') . '/' . $attribute->getID() ?>">
					<i class="fa fa-eye"></i> View Attribute
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<hr>
<div class="row mb-5">
	<div class="col">
		<a class="btn btn-secondary bg-gradient-secondary" href="<?= base_url('Source') ?>">
			<i class="fa fa-database"></i> View Sources
		</a>
		<a class="btn btn-info bg-gradient-info" href="<?= base_url('Source/Elasticsearch/' . $source_id) ?>">
			<i class="fa fa-search"></i> Elasticsearch Index Status
		</a>
		<a class="btn btn-info bg-gradient-info" href="<?= base_url('Source/Neo4J/' . $source_id) ?>">
			<i class="fa fa-project-diagram"></i> Neo4J Index Status
		</a>
		<a class="btn btn-info bg-gradient-info" href="<?= base_url('Source/UserInterface/' . $source_id) ?>">
			<i class="fa fa-desktop"></i> User Interface Index Status
		</a>
	</div>
</div>
<?= $this->endSection() ?>
